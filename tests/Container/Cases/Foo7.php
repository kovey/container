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
namespace Kovey\Container\Cases;

#[\Attribute]
class Foo7
{
    private string $name;

    #[Foo1]
    private Foo1 $foo1;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getFoo1() : Foo1
    {
        return $this->foo1;
    }
}
