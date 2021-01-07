<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-16 13:19:23
 *
 */
namespace Kovey\Container\Cases;

use Kovey\Container\Event\Database;
use Kovey\Container\Event\ShardingDatabase;
use Kovey\Container\Event\Redis;
use Kovey\Container\Event\ShardingRedis;
use Kovey\Container\Event\Router;
use Kovey\Container\Event\GlobalId;

class Foo
{
    #[Foo1()]
    private Foo1 $foo1;

    public function getFoo1() : Foo1
    {
        return $this->foo1;
    }

    #[Foo1]
    #[Transaction]
    #[Database('db')]
    #[Router('/login/login', 'POST')]
    #[Redis('redis')]
    #[GlobalId('mysql', 'redis', 'global_id', 'test_id', 'id')]
    public function test(Foo1 $foo1) : Foo1
    {
        return $foo1;
    }

    #[ShardingDatabase('mysql')]
    #[ShardingRedis('redis')]
    public function testSharding()
    {
        return 'aaa';
    }
}
