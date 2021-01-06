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
namespace Kovey\Container\Cases;

#[\Attribute]
class Foo2
{
    private string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
