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

class FooController
{
    #[Router('/login/login', 'POST')]
    public function testAction()
    {
        return 'test';
    }
}
