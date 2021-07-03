<?php
/**
 * @description module interface
 *
 * @package Kovey\Container
 *
 * @author kovey
 *
 * @time 2021-07-03 13:54:03
 *
 */
namespace Kovey\Container\Module;

interface HasGlobalIdInterface
{
    /**
     * @description set global id
     *
     * @param int $globalId
     *
     * @return HasGlobalIdInterface
     */
    public function setGlobalId(int $globalId) : HasGlobalIdInterface;
}
