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

interface HasDbInterface
{
    /**
     * @description set database
     *
     * @param mixed $database
     *
     * @return HasDbInterface
     */
    public function setDatabase(mixed $database) : HasDbInterface;
}
