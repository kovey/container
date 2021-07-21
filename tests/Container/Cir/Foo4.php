<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-19 14:10:29
 *
 */
namespace Kovey\Container\Cir;

#[\Attribute]
class Foo4
{
    #[Foo5]
    private Foo5 $foo;

    public function getFoo() : Foo5
    {
        return $this->foo;
    }
}
