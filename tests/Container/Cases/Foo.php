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
    public function test(Foo1 $foo1) : Foo1
    {
        return $foo1;
    }
}
