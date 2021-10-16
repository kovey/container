<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2021-01-09 14:47:10
 *
 */
namespace Kovey\Container\Cases;

use Kovey\Container\Event\Router;
use Kovey\Container\Event\Protocol;
use Kovey\Container\Event\Clickhouse;

class FooController
{
    #[Router('/login/login', 'POST')]
    public function testAction()
    {
        return 'test';
    }

    #[Protocol(1001, Protocol::class, Router::class)]
    #[Clickhouse('On')]
    public function handler()
    {
        return 'handler';
    }
}
