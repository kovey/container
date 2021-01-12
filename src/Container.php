<?php
/**
 * @description container
 *
 * @package Parse
 *
 * @author kovey
 *
 * @time 2019-10-16 10:36:36
 *
 */
namespace Kovey\Container;

use Kovey\Container\Exception\ContainerException;
use Kovey\Container\Event;
use Kovey\Event\Listener\ListenerProvider;
use Kovey\Event\Listener\Listener;
use Kovey\Event\Dispatch;
use Kovey\Event\EventInterface;
use Kovey\Validator\RuleInterface;

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
     * @description on events
     *
     * @var Array
     */
    private Array $onEvents;

    /**
     * @description events
     *
     * @var Array
     */
    private static Array $events = array(
        'ShardingDatabase' => Event\ShardingDatabase::class,
        'ShardingRedis' => Event\ShardingRedis::class,
        'Database' => Event\Database::class,
        'Redis' => Event\Redis::class,
        'GlobalId' => Event\GlobalId::class,
        'Router' => Event\Router::class
    );

    /**
     * @description dispatch
     *
     * @var Dispatch
     */
    private Dispatch $dispatch;

    /**
     * @description listener provider
     *
     * @var ListenerProvider
     */
    private ListenerProvider $provider;

    /**
     * @description construct
     *
     * @return Container
     */
    public function __construct()
    {
        $this->instances = array();
        $this->methods = array();
        $this->onEvents = array();
        $this->keywords = array(
            Event\ShardingDatabase::class => 'database', 
            Event\ShardingRedis::class => 'redis', 
            Event\Transaction::class => true, 
            Event\Database::class => 'database', 
            Event\Redis::class => 'redis', 
            Event\GlobalId::class => 'globalId',
            Event\Router::class => true
        );

        $this->provider = new ListenerProvider();
        $this->dispatch = new Dispatch($this->provider);
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
    public function get(string $class, string $traceId, Array $ext = array(), ...$args)
    {
        if (!isset($this->instances[$class])) {
            $this->resolve($class);
        }

        $class = $this->instances[$class]['class'];

        if (count($args) < 1) {
            if ($class instanceof \ReflectionClass) {
                if ($class->hasMethod(self::CLASS_METHOD_CONSTRUCT)) {
                    $args = $this->getMethodArguments($class->getName(), self::CLASS_METHOD_CONSTRUCT, $traceId);
                }
            }
        }

        return $this->bind($class, $traceId, $this->instances[$class->getName()]['dependencies'] ?? array(), $ext, $args);
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
    private function bind(\ReflectionClass | \ReflectionAttribute $class, string $traceId, Array $dependencies, Array $ext = array(), Array $args = array())
    {
        $obj = null;
        if (count($args) > 0) {
            $obj = $class->newInstanceArgs($args);
        } else {
            $obj = $class->newInstance();
        }

        if (!empty($traceId)) {
            $obj->traceId = $traceId;
        }

        foreach ($ext as $field => $val) {
            $obj->$field = $val;
        }

        if (count($dependencies) < 1) {
            return $obj;
        }

        foreach ($dependencies as $dependency) {
            $dep = $this->bind($this->instances[$dependency['class']]['class'], $traceId, $this->instances[$dependency['class']]['dependencies'] ?? array(), $ext);
            $dependency['property']->setValue($obj, $dep);
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

        if ($method->getName() !== self::CLASS_METHOD_CONSTRUCT) {
            foreach ($method->getAttributes() as $attr) {
                if (isset($this->keywords[$attr->getName()])) {
                    continue;
                }

                $validRule = $attr->newInstance();
                if (!$validRule instanceof RuleInterface) {
                    continue;
                }

                $validRules[$attr->getName()] = $validRule;
            }
        }

        foreach ($method->getAttributes() as $attr) {
            if (isset($validRules[$attr->getName()])) {
                continue;
            }

            if (!isset($this->keywords[$attr->getName()])) {
                $attrs['arguments'][] = $attr;
                continue;
            }

            if ($method->getName() === self::CLASS_METHOD_CONSTRUCT) {
                continue;
            }

            if ($attr->getName() !== Event\Router::class) {
                $attrs['keywords'][$attr->getName()] = $attr;
                continue;
            }

            $suffix = substr($method->class, 0 - strlen(Event\Router::ROUTER_CONTROLLER));
            if ($suffix !== Event\Router::ROUTER_CONTROLLER) {
                continue;
            }

            $suffix = substr($method->name, 0 - strlen(Event\Router::ROUTER_ACTION));
            if ($suffix !== Event\Router::ROUTER_ACTION) {
                continue;
            }

            $router = $attr->newInstance();
            $router->setController(substr($method->class, 0, 0 - strlen(Event\Router::ROUTER_CONTROLLER)))
                   ->setAction(substr($method->name, 0, 0 - strlen(Event\Router::ROUTER_ACTION)))
                   ->setRules($validRules);

            $this->dispatch->dispatch($router);

        }

        return $attrs;
    }

    /**
     * @description cache
     *
     * @param string | ReflectionAttribute $class
     *
     * @return null
     */
    private function resolve(string | \ReflectionAttribute $class) : void
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

        $dependencies = $this->getAts($class);
        if (empty($dependencies)) {
            return;
        }

        foreach ($dependencies as $dependency) {
            $this->instances[$class->getName()]['dependencies'][] = array(
                'class' => $dependency['class']->getName(),
                'property' => $dependency['property']
            );

            if (isset($this->instances[$dependency['class']->getName()])) {
                continue;
            }

            $this->resolve($dependency['class']);
        }
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
    public function getMethodArguments(string $class, string $method, string $traceId, Array $ext = array()) : Array
    {
        $classMethod = $class . '::' . $method;
        $this->methods[$classMethod] ??= $this->resolveMethod($classMethod);
        $attrs = $this->methods[$classMethod]['arguments'];
        array_walk ($attrs, function(&$attr) use ($traceId, $ext) {
            $obj = $this->get($attr->getName(), $traceId, $ext, ...$attr->getArguments());
            $obj->traceId = $traceId;
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

        $hasTransation = false;
        $hasDatabase = false;
        foreach ($this->methods[$classMethod]['keywords'] as $keyword => $event) {
            if ($keyword === Event\Transaction::class) {
                $hasTransation = true;
                continue;
            }

            if (!isset($this->onEvents[$keyword])) {
                continue;
            }

            if ($keyword === Event\Database::class) {
                $hasDatabase = true;
            }

            $pool = $this->dispatch->dispatchWithReturn($event->newInstance());

            if ($keyword === Event\Database::class || $keyword === Event\Redis::class) {
                $keywords[$this->keywords[$keyword]] = $pool;
                if (is_object($pool) && method_exists($pool, 'getConnection')) {
                    $keywords['ext'][$this->keywords[$keyword]] = $pool->getConnection();
                } else {
                    $keywords['ext'][$this->keywords[$keyword]] = $pool;
                }
            } else {
                $keywords['ext'][$this->keywords[$keyword]] = $pool;
            }
        }

        $keywords['openTransaction'] = $hasTransation && $hasDatabase;
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
        if (!isset(self::$events[$event])) {
            throw new ContainerException("$event is not support.");
        }

        if (!is_callable($fun)) {
            throw new ContainerException('fun is not callable');
        }

        $listener = new Listener();
        $listener->addEvent(self::$events[$event], $fun);
        $this->provider->addListener($listener);
        $this->onEvents[self::$events[$event]] = $event;
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
            $ref = new \ReflectionClass($class);
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $this->methods[$ref->getName() . '::' . $method->getName()] = $this->resolveMethod($method);
            }
        }

        return $this;
    }
}
