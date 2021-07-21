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
class Foo6
{
    #[Foo7]
    private Foo7 $foo7;

    public function getFoo7() : Foo7
    {
        return $this->foo7;
    }
}
