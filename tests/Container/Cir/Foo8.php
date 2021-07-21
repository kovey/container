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
namespace Kovey\Container\Cir;

#[\Attribute]
class Foo8
{
    #[Foo1]
    private Foo1 $foo1;

    #[Foo2]
    private Foo2 $foo2;

    #[Foo4]
    private Foo4 $foo4;

    public function getFoo1() : Foo1
    {
        return $this->foo1;
    }

    public function getFoo4() : Foo4
    {
        return $this->foo4;
    }
}
