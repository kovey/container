<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-16 13:16:13
 *
 */
namespace Kovey\Container;

require_once __DIR__ . '/Cases/Foo.php';
require_once __DIR__ . '/Cases/Foo1.php';
require_once __DIR__ . '/Cases/Foo2.php';
require_once __DIR__ . '/Cases/Foo4.php';
require_once __DIR__ . '/Cases/Foo5.php';
require_once __DIR__ . '/Cases/Foo6.php';
require_once __DIR__ . '/Cases/Foo7.php';
require_once __DIR__ . '/Cases/FooController.php';

require_once __DIR__ . '/Cir/Foo.php';
require_once __DIR__ . '/Cir/Foo1.php';
require_once __DIR__ . '/Cir/Foo2.php';
require_once __DIR__ . '/Cir/Foo4.php';
require_once __DIR__ . '/Cir/Foo5.php';
require_once __DIR__ . '/Cir/Foo6.php';
require_once __DIR__ . '/Cir/Foo7.php';
require_once __DIR__ . '/Cir/Foo8.php';
require_once __DIR__ . '/Cir/Foo9.php';
require_once __DIR__ . '/Cir/Foo10.php';
require_once __DIR__ . '/Cir/Foo11.php';

use PHPUnit\Framework\TestCase;
use Kovey\Container\Cases;
use Kovey\Container\Cir;
use Kovey\Container\Event;
use Kovey\Container\Exception\ContainerException;

class ContainerTest extends TestCase
{
    public function testGet()
    {
        $traceId = hash('sha256', '123456');
        $spanId = md5('123456');
        $container = new Container();
        $foo = $container->get('Kovey\Container\Cases\Foo', $traceId, $spanId);
        $container->on('Database', function (Event\Database $event) {
            return $event->getPoolName();
        });
        $container->on('ShardingDatabase', function (Event\ShardingDatabase $event) {
            return $event->getPoolName();
        });
        $container->on('Redis', function (Event\Redis $event) {
            return $event->getPoolName();
        });
        $container->on('ShardingRedis', function (Event\ShardingRedis $event) {
            return $event->getPoolName();
        });
        $container->on('GlobalId', function (Event\GlobalId $event) {
            return $event;
        });

        $this->assertInstanceOf(Cases\Foo::class, $foo);
        $this->assertEquals($traceId, $foo->traceId);
        $this->assertEquals($spanId, $foo->spanId);
        $foo1 = $foo->getFoo1();
        $this->assertInstanceOf(Cases\Foo1::class, $foo1);
        $this->assertEquals($traceId, $foo1->traceId);
        $this->assertEquals($spanId, $foo1->spanId);
        $foo2 = $foo1->getFoo2();
        $this->assertInstanceOf(Cases\Foo2::class, $foo2);
        $this->assertEquals($traceId, $foo2->traceId);
        $this->assertEquals($spanId, $foo2->spanId);
        $this->assertEquals('this is test', $foo2->getName());
        $args = $container->getMethodArguments('Kovey\Container\Cases\Foo', 'test', $traceId, $spanId);
        $keywords = $container->getKeywords('Kovey\Container\Cases\Foo', 'test');
        $this->assertEquals(1, count($args));
        $this->assertInstanceOf(Cases\Foo1::class, $args[0]);
        $this->assertEquals('db', $keywords['ext']['database']);
        $this->assertTrue($keywords['openTransaction']);
        $this->assertEquals('db', $keywords['database']);
        $this->assertEquals('redis', $keywords['ext']['redis']);
        $this->assertEquals('redis', $keywords['ext']['globalId']->getGlobalKey());
        $this->assertEquals('mysql', $keywords['ext']['globalId']->getRedisPoolName());
        $keywords = $container->getKeywords('Kovey\Container\Cases\Foo', 'testSharding');
        $this->assertEquals('mysql', $keywords['ext']['database']);
        $this->assertEquals('redis', $keywords['ext']['redis']);
    }

    public function testGetFailure()
    {
        $traceId = hash('sha256', '123456');
        $spanId = md5('12355');
        $this->expectException(\ReflectionException::class);
        $container = new Container();
        $foo = $container->get('Kovey\\NotExistsClass', $traceId, $spanId);
    }

    public function testGetFailureWithNonAttributeClass()
    {
        $traceId = hash('sha256', '123456');
        $spanId = md5('12355');
        $this->expectException(\Error::class);
        $container = new Container();
        $foo = $container->get('Kovey\\Container\\Cases\\Foo4', $traceId, $spanId);
    }

    public function testGetConstructDefault()
    {
        $traceId = hash('sha256', '123456');
        $spanId = md5('12355');
        $container = new Container();
        $foo5 = $container->get('Kovey\\Container\\Cases\\Foo5', $traceId, $spanId);
        $this->assertInstanceOf(Cases\Foo5::class, $foo5);
        $this->assertEquals($traceId, $foo5->traceId);
        $this->assertEquals($spanId, $foo5->spanId);
        $foo6 = $foo5->getFoo6();
        $this->assertInstanceOf(Cases\Foo6::class, $foo6);
        $this->assertEquals($traceId, $foo6->traceId);
        $this->assertEquals($spanId, $foo6->spanId);
        $foo7 = $foo6->getFoo7();
        $this->assertInstanceOf(Cases\Foo7::class, $foo7);
        $this->assertEquals($traceId, $foo7->traceId);
        $this->assertEquals($spanId, $foo7->spanId);
        $this->assertEquals('this is foo7', $foo7->getName());
        $foo1 = $foo7->getFoo1();
        $this->assertInstanceOf(Cases\Foo1::class, $foo1);
        $this->assertEquals($traceId, $foo1->traceId);
        $this->assertEquals($spanId, $foo1->spanId);
        $foo2 = $foo1->getFoo2();
        $this->assertInstanceOf(Cases\Foo2::class, $foo2);
        $this->assertEquals($traceId, $foo2->traceId);
        $this->assertEquals($spanId, $foo2->spanId);
        $this->assertEquals('this is test', $foo2->getName());
    }

    public function testParse()
    {
        $traceId = hash('sha256', '123456');
        $spanId = md5('12355');
        $container = new Container();
        $container->on('Database', function (Event\Database $event) {
            return $event->getPoolName();
        });
        $path = '';
        $method = '';
        $router = '';
        $code = 0;
        $protobuf = '';
        $base = '';
        $container->on('Router', function (Event\Router $event) use (&$path, &$method, &$router) {
            $path = $event->getPath();
            $method = $event->getMethod();
            $router = $event->getRouter();
        })
          ->on('Protocol', function (Event\Protocol $event) use (&$code, &$protobuf, &$base) {
              $code = $event->getCode();
              $protobuf = $event->getProtobuf();
              $base = $event->getProtobufBase();
          });

        $container->parse(__DIR__ . '/Cases', 'Kovey\\Container\\Cases', '');

        $foo = $container->get('Kovey\Container\Cases\Foo', $traceId, $spanId);

        $this->assertEquals('/login/login', $path);
        $this->assertEquals('POST', $method);
        $this->assertEquals('test@Kovey\Container\Cases\Foo', $router);
        $this->assertEquals(1001, $code);
        $this->assertEquals(Event\Protocol::class, $protobuf);
        $this->assertEquals(Event\Router::class, $base);
        $this->assertInstanceOf(Cases\Foo::class, $foo);
        $this->assertEquals($traceId, $foo->traceId);
        $this->assertEquals($spanId, $foo->spanId);
        $foo1 = $foo->getFoo1();
        $this->assertInstanceOf(Cases\Foo1::class, $foo1);
        $this->assertEquals($traceId, $foo1->traceId);
        $this->assertEquals($spanId, $foo1->spanId);
        $foo2 = $foo1->getFoo2();
        $this->assertInstanceOf(Cases\Foo2::class, $foo2);
        $this->assertEquals($traceId, $foo2->traceId);
        $this->assertEquals($spanId, $foo2->spanId);
        $this->assertEquals('this is test', $foo2->getName());
        $args = $container->getMethodArguments('Kovey\Container\Cases\Foo', 'test', $traceId, $spanId);
        $keywords = $container->getKeywords('Kovey\Container\Cases\Foo', 'test');
        $this->assertEquals(1, count($args));
        $this->assertInstanceOf(Cases\Foo1::class, $args[0]);
        $this->assertEquals($traceId, $args[0]->traceId);
        $this->assertEquals($spanId, $args[0]->spanId);
        $this->assertEquals('db', $keywords['ext']['database']);
        $this->assertTrue(!isset($keywords['ext']['redis']));
        $this->assertTrue($keywords['openTransaction']);
        $this->assertEquals('db', $keywords['database']);
    }

    public function testCircularReference()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('"Kovey\Container\Cir\Foo" circular reference in dependency link: "Kovey\Container\Cir\Foo -> Kovey\Container\Cir\Foo1 -> Kovey\Container\Cir\Foo2 -> Kovey\Container\Cir\Foo6 -> Kovey\Container\Cir\Foo7 -> Kovey\Container\Cir\Foo"');
        $container = new Container();
        $foo = $container->get('Kovey\Container\Cir\Foo', '', '');
    }

    public function testCircularReferenceOne()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('"Kovey\Container\Cir\Foo4" circular reference in dependency link: "Kovey\Container\Cir\Foo4 -> Kovey\Container\Cir\Foo5 -> Kovey\Container\Cir\Foo4"');
        $container = new Container();
        $foo = $container->get('Kovey\Container\Cir\Foo4', '', '');
    }

    public function testCircularReferenceMiddle()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('"Kovey\Container\Cir\Foo1" circular reference in dependency link: "Kovey\Container\Cir\Foo8 -> Kovey\Container\Cir\Foo1 -> Kovey\Container\Cir\Foo2 -> Kovey\Container\Cir\Foo6 -> Kovey\Container\Cir\Foo7 -> Kovey\Container\Cir\Foo -> Kovey\Container\Cir\Foo1"');
        $container = new Container();
        $foo = $container->get('Kovey\Container\Cir\Foo8', '', '');
    }

    public function testCheckMultiInstance()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('"Kovey\Container\Cir\Foo11" more than one instance in dependency links: "Kovey\Container\Cir\Foo9 -> Kovey\Container\Cir\Foo10 -> Kovey\Container\Cir\Foo11"');
        $container = new Container();
        $container->openCheckMultiInstance();
        $foo = $container->get('Kovey\Container\Cir\Foo9', '', '');
    }
}
