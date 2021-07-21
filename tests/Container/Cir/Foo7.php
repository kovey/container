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
class Foo7
{
    #[Foo]
    private Foo $foo;

    public function getFoo() : Foo
    {
        return $this->foo;
    }
}
