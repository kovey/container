<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-16 13:25:19
 *
 */
namespace Kovey\Container\Cir;

#[\Attribute]
class Foo2
{
    #[Foo6]
    private Foo6 $foo;

    private string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getFoo() : Foo6
    {
        return $this->foo;
    }
}
