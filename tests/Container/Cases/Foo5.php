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
namespace Kovey\Container\Cases;

class Foo5
{
    private Foo6 $foo6;

    #[Foo6]
    public function __construct(Foo6 $foo6)
    {
        $this->foo6 = $foo6;
    }

    public function getFoo6() : Foo6
    {
        return $this->foo6;
    }
}
