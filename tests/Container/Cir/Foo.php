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
class Foo
{
    #[Foo1]
    private Foo1 $foo1;

    public function getFoo1() : Foo1
    {
        return $this->foo1;
    }
}
