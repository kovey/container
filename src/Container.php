<?php
/**
 * @description container
 *
 * @package Container
 *
 * @author kovey
 *
 * @time 2019-10-16 10:36:36
 *
 */
namespace Kovey\Container;

use Kovey\Container\Exception\ContainerException;
use Kovey\Container\Event;
use Kovey\Event\EventManager;
use Kovey\Event\EventInterface;
use Kovey\Validator\RuleInterface;
use Kovey\Container\Keyword\Fields;
use Kovey\Container\Keyword\EventName;
use Kovey\Container\Module;
use Kovey\Library\Trace\TraceInterface;

class Container implements ContainerInterface
{
    const CLASS_METHOD_CONSTRUCT = '__construct';

    /**
     * @description cache
     *
     * @var Array
     */
    private Array $instances;

    /**
     * @description links
     *
     * @var Array
     */
    private Array $links;

    /**
     * @description methods cache
     *
     * @var Array
     */
    private Array $methods;

    /**
     * @description keywords
     *
     * @var Array
     */
    private Array $keywords;

    /**
     * @description event manager
     *
     * @var EventManager
     */
    private EventManager $eventManager;

    /**
     * @description check multi instance off
     *
     * @var bool
     */
    private bool $isCheckMultiInstance = false;

    /**
     * @description construct
     *
     * @return Container
     */
    public function __construct()
    {
        $this->instances = array();
        $this->methods = array();
        $this->links = array();
        $this->keywords = array(
            Event\ShardingDatabase::class => Fields::KEYWORD_DATABASE, 
            Event\ShardingRedis::class => Fields::KEYWORD_REDIS, 
            Event\Transaction::class => Fields::KEYWORD_BOOL_TRUE, 
            Event\Database::class => Fields::KEYWORD_DATABASE, 
            Event\Redis::class => Fields::KEYWORD_REDIS, 
            Event\GlobalId::class => Fields::KEYWORD_GLOBAL_ID,
            Event\Router::class => Fields::KEYWORD_BOOL_TRUE,
            Event\Protocol::class => Fields::KEYWORD_BOOL_TRUE,
            Event\Clickhouse::class => Fields::KEYWORD_DATABASE
        );
        $this->eventManager = new EventManager(array(
            EventName::EVENT_SHARDING_DATABASE => Event\ShardingDatabase::class,
            EventName::EVENT_SHARDING_REDIS => Event\ShardingRedis::class,
            EventName::EVENT_DATABASE => Event\Database::class,
            EventName::EVENT_REDIS => Event\Redis::class,
            EventName::EVENT_GLOBAL_ID => Event\GlobalId::class,
            EventName::EVENT_ROUTER => Event\Router::class,
            EventName::EVENT_PROTOCOL => Event\Protocol::class,
            EventName::EVENT_CLICKHOUSE => Event\Clickhouse::class
        ));
    }

    /**
     * @description get object
     *
     * @param string $class
     *
     * @param string $traceId
     *
     * @param Array $ext
     *
     * @param ... $args
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function get(string $class, string $traceId, string $spanId, Array $ext = array(), ...$args) : mixed
    {
        if (!isset($this->instances[$class])) {
            $this->resolve($class);
            $this->checkCircularReference();
            if ($this->isCheckMultiInstance) {
                $this->checkMultiInstance($class);
            }
        }

        if (empty($this->instances[$class]['class'])) {
            throw new \RuntimeException(sprintf('Class "%s" in not exists', $class));
        }

        $class = $this->instances[$class]['class'];

        if (count($args) < 1) {
            if ($class instanceof \ReflectionClass) {
                if ($class->hasMethod(self::CLASS_METHOD_CONSTRUCT)) {
                    $args = $this->getMethodArguments($class->getName(), self::CLASS_METHOD_CONSTRUCT, $traceId, $spanId);
                }
            }
        }

        array_walk($ext, function (&$val) use ($traceId, $spanId) {
            if (!is_object($val)) {
                return;
            }

            if (!empty($traceId)) {
                if ($val instanceof TraceInterface) {
                    $val->setTraceId($traceId);
                }
            }
            if (!empty($spanId)) {
                if ($val instanceof TraceInterface) {
                    $val->setSpanId($spanId);
                }
            }
        });

        return $this->bind($class, $traceId, $spanId, $this->instances[$class->getName()]['dependencies'] ?? array(), $ext, $args);
    }

    /**
     * @description bind
     *
     * @param ReflectionClass | ReflectionAttribute $class
     *
     * @param string $traceId
     *
     * @param Array $dependencies
     *
     * @param Array $ext
     *
     * @param Array $args
     *
     * @return mixed
     */
    private function bind(\ReflectionClass | \ReflectionAttribute $class, string $traceId, string $spanId, Array $dependencies, Array $ext = array(), Array $args = array()) : mixed
    {
        $obj = null;
        if (count($args) > 0) {
            $obj = $class->newInstanceArgs($args);
        } else {
            $obj = $class->newInstance();
        }

        if (!empty($traceId)) {
            if ($obj instanceof TraceInterface) {
                $obj->setTraceId($traceId);
            }
        }

        if (!empty($spanId)) {
            if ($obj instanceof TraceInterface) {
                $obj->setSpanId($spanId);
            }
        }

        if ($obj instanceof Module\HasDbInterface) {
            if (isset($ext[Fields::KEYWORD_DATABASE])) {
                $obj->setDatabase($ext[Fields::KEYWORD_DATABASE]);
            }
        }

        if ($obj instanceof Module\HasRedisInterface) {
            if (isset($ext[Fields::KEYWORD_REDIS])) {
                $obj->setRedis($ext[Fields::KEYWORD_REDIS]);
            }
        }

        if ($obj instanceof Module\HasGlobalIdInterface) {
            if (isset($ext[Fields::KEYWORD_GLOBAL_ID])) {
                $obj->setGlobalId($ext[Fields::KEYWORD_GLOBAL_ID]);
            }
        }

        if (count($dependencies) < 1) {
            return $obj;
        }

        foreach ($dependencies as $className => $property) {
            $dep = $this->bind($this->instances[$className]['class'], $traceId, $spanId, $this->instances[$className]['dependencies'] ?? array(), $ext);
            $property->setValue($obj, $dep);
        }

        return $obj;
    }

    /**
     * @description cache
     *
     * @param string | ReflectionMethod $classMethod
     *
     * @return Array
     */
    private function resolveMethod(string | \ReflectionMethod $method) : Array
    {
        if (!$method instanceof \ReflectionMethod) {
            $method = new \ReflectionMethod($method);
        }

        $attrs = array(
            'keywords' => array(),
            'arguments' => array()
        );

        $validRules = array();
        $ruleKeywords = array();

        if ($method->getName() !== self::CLASS_METHOD_CONSTRUCT) {
            foreach ($method->getAttributes() as $attr) {
                if (isset($this->keywords[$attr->getName()])) {
                    continue;
                }

                $validRule = $attr->newInstance();
                if (!$validRule instanceof RuleInterface) {
                    continue;
                }

                $validRules[] = $validRule;
                $ruleKeywords[$attr->getName()] = 1;
            }
        }

        foreach ($method->getAttributes() as $attr) {
            if (isset($ruleKeywords[$attr->getName()])) {
                continue;
            }

            if (!isset($this->keywords[$attr->getName()])) {
                $attrs['arguments'][] = $attr;
                continue;
            }

            if ($method->getName() === self::CLASS_METHOD_CONSTRUCT) {
                continue;
            }

            if ($this->processKeywords($method, $attr, $validRules)) {
                continue;
            }

            $attrs['keywords'][$attr->getName()] = $attr;
        }

        return $attrs;
    }

    private function processRouter(\ReflectionMethod $method, \ReflectionAttribute $attr, Array $validRules) : void
    {
        $suffix = substr($method->class, 0 - strlen(Event\Router::ROUTER_CONTROLLER));
        if ($suffix !== Event\Router::ROUTER_CONTROLLER) {
            return;
        }

        $suffix = substr($method->name, 0 - strlen(Event\Router::ROUTER_ACTION));
        if ($suffix !== Event\Router::ROUTER_ACTION) {
            return;
        }

        $router = $attr->newInstance();
        $router->setController(substr($method->class, 0, 0 - strlen(Event\Router::ROUTER_CONTROLLER)))
               ->setAction(substr($method->name, 0, 0 - strlen(Event\Router::ROUTER_ACTION)))
               ->setRules($validRules);

        $this->eventManager->dispatch($router);
    }

    private function processProcotol(\ReflectionMethod $method, \ReflectionAttribute $attr) : void
    {
        $protocol = $attr->newInstance();
        $protocol->setHandler($method->class)
            ->setMethod($method->name);

        $this->eventManager->dispatch($protocol);
    }

    private function processKeywords(\ReflectionMethod $method, \ReflectionAttribute $attr, Array $validRules) : bool
    {
        if ($attr->getName() == Event\Router::class) {
            $this->processRouter($method, $attr, $validRules);
            return true;
        }

        if ($attr->getName() == Event\Protocol::class) {
            $this->processProcotol($method, $attr);
            return true;
        }

        return false;
    }

    /**
     * @description cache
     *
     * @param string | ReflectionAttribute $class
     *
     * @return null
     */
    private function resolve(string | \ReflectionAttribute $class, string $pclass = '') : void
    {
        if (!$class instanceof \ReflectionAttribute) {
            if (isset($this->instances[$class])) {
                return;
            }
            $class = new \ReflectionClass($class);
        }

        if (isset($this->instances[$class->getName()])) {
            return;
        }

        $this->instances[$class->getName()] = array(
            'class' => $class,
            'dependencies' => array()
        );

        if (!empty($pclass)) {
            $pclass = $pclass . ' -> ' . $class->getName();
        } else {
            $pclass = $class->getName();
        }

        try {
            $dependencies = $this->getAts($class);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('%s in class[%s]', $e->getMessag(), $class->getName()), 1002);
        }

        if (empty($dependencies)) {
            $this->links[] = $pclass;
            return;
        }

        foreach ($dependencies as $dependency) {
            $this->instances[$class->getName()]['dependencies'][$dependency['class']->getName()] = $dependency['property'];

            if (isset($this->instances[$dependency['class']->getName()])) {
                $this->links[] = $pclass . ' -> ' . $dependency['class']->getName();
                continue;
            }

            $this->resolve($dependency['class'], $pclass);
        }
    }

    /**
     * @description check circular reference
     *
     * @return void
     *
     * @throws ContainerException
     */
    private function checkCircularReference() : void
    {
        foreach ($this->links as $link) {
            $info = explode(' -> ', $link);
            $info = array_count_values($info);
            foreach ($info as $key => $val) {
                if ($val < 2) {
                    continue;
                }

                throw new ContainerException(sprintf('"%s" circular reference in dependency link: "%s"', $key, $link));
            }
        }
    }

    /**
     * @description check instance more than one
     *
     * @param string $class
     *
     * @return void
     *
     * @throws ContainerException
     */
    private function checkMultiInstance($class) : void
    {
        $queue = new \SplQueue();
        $queue->enqueue($class);
        $set = array();
        while (!$queue->isEmpty()) {
            $pid = $queue->dequeue();
            $info = $this->instances[$pid];

            if (isset($set[$pid])) {
                throw new ContainerException(sprintf('"%s" more than one instance in dependency links: "%s"', $pid, implode(' -> ', array_keys($set))));
            }

            $set[$pid] = 1;

            foreach ($info['dependencies'] as $ds => $dInfo) {
                $queue->enqueue($ds);
            }
        }

        var_dump($set);
    }

    /**
     * @description get all reject
     *
     * @param ReflectionClass | ReflectionAttribute $ref
     *
     * @return Array
     */
    private function getAts(\ReflectionClass | \ReflectionAttribute $ref) : Array
    {
        if (!$ref instanceof \ReflectionClass) {
            $ref = new \ReflectionClass($ref->getName());
        }

        $properties = $ref->getProperties();
        $ats = array();
        foreach ($properties as $property) {
            $attrs = $property->getAttributes();
            if (empty($attrs)) {
                continue;
            }

            foreach ($attrs as $attr) {
                if ($property->isPrivate()
                    || $property->isProtected()
                ) {
                    $property->setAccessible(true);
                }

                $ats[$property->getName()] = array(
                    'class' => $attr,
                    'property' => $property
                );

                break;
            }
        }

        return $ats;
    }

    /**
     * @description method arguments
     *
     * @param string $class
     *
     * @param string $method
     *
     * @param string $traceId
     *
     * @param Array $ext
     *
     * @return Array
     */
    public function getMethodArguments(string $class, string $method, string $traceId, string $spanId, Array $ext = array()) : Array
    {
        $classMethod = $class . '::' . $method;
        $this->methods[$classMethod] ??= $this->resolveMethod($classMethod);
        $attrs = $this->methods[$classMethod]['arguments'];
        array_walk($attrs, function(&$attr) use ($traceId, $ext, $spanId) {
            $obj = $this->get($attr->getName(), $traceId, $spanId, $ext, ...$attr->getArguments());
            $attr = $obj;
        });

        return $attrs;
    }

    /**
     * @description 获取关键字
     *
     * @param string $class
     *
     * @param string $methods
     * 
     * @return Array
     */
    public function getKeywords(string $class, string $method) : Array
    {
        $classMethod = $class . '::' . $method;
        $this->methods[$classMethod] ??= $this->resolveMethod($classMethod);
        $keywords = array(
            'ext' => array()
        );

        $hasTransaction = false;
        $hasDatabase = false;
        foreach ($this->methods[$classMethod]['keywords'] as $keyword => $event) {
            if ($keyword === Event\Transaction::class) {
                $hasTransaction = true;
                continue;
            }

            if (!$this->eventManager->listenedByClass($keyword)) {
                continue;
            }

            if ($keyword === Event\Database::class) {
                $hasDatabase = true;
            }

            $pool = $this->eventManager->dispatchWithReturn($event->newInstance());
            $keywords[$this->keywords[$keyword]] = $pool;
            $keywords['ext'][$this->keywords[$keyword]] = $pool;
        }

        $keywords[Fields::KEYWORD_OPEN_TRANSACTION] = $hasTransaction && $hasDatabase;
        return $keywords;
    }

    /**
     * @description events
     *
     * @param string $events
     * 
     * @param callable | Array $fun
     *
     * @return $this
     */
    public function on(string $event, callable | Array $fun) : ContainerInterface
    {
        $this->eventManager->addEvent($event, $fun);
        return $this;
    }

    /**
     * @description parse
     *
     * @param string $dir
     *
     * @param string $namespace
     *
     * @param string $suffix = ''
     * 
     * @return $this
     */
    public function parse(string $dir, string $namespace, string $suffix = '') : ContainerInterface
    {
        if (!is_dir($dir)) {
            throw new ContainerException("$dir is not found");
        }

        $files = scandir($dir);
        foreach ($files as $file) {
            if (substr($file, -4) !== '.php') {
                continue;
            }

            $class = trim($namespace . '\\' . substr($file, 0, -4) . $suffix, '\\');
            $this->resolve($class);
            if ($this->isCheckMultiInstance) {
                $this->checkMultiInstance($class);
            }
            $ref = new \ReflectionClass($class);
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $this->methods[$ref->getName() . '::' . $method->getName()] = $this->resolveMethod($method);
            }
        }

        $this->checkCircularReference();
        return $this;
    }

    /**
     * @description open check multi instance
     *
     * @return ContainerInterface
     */
    public function openCheckMultiInstance() : ContainerInterface
    {
        $this->isCheckMultiInstance = true;
        return $this;
    }
}
