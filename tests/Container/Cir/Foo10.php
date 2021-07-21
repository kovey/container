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
class Foo10
{
    #[Foo11]
    private Foo11 $foo1;
}
