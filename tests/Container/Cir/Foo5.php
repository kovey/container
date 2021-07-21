<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-20 10:39:17
 *
 */
namespace Kovey\Container\Cir;

class Foo5
{
    #[Foo4]
    private Foo4 $foo4;

    public function getFoo4() : Foo4
    {
        return $this->foo4;
    }
}
