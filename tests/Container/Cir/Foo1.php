<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-16 13:21:35
 *
 */
namespace Kovey\Container\Cir;

#[\Attribute]
class Foo1
{
    #[Foo2("this is test")]
    private Foo2 $foo2;

    public function getFoo2() : Foo2
    {
        return $this->foo2;
    }
}
