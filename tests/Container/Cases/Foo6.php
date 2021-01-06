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
class Foo6
{
    private Foo7 $foo7;

    #[Foo7('this is foo7')]
    public function __construct(Foo7 $foo7)
    {
        $this->foo7 = $foo7;
    }

    public function getFoo7() : Foo7
    {
        return $this->foo7;
    }
}
