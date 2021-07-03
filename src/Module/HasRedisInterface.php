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

interface HasRedisInterface
{
    /**
     * @description set redis
     *
     * @param mixed $redis
     *
     * @return HasRedisInterface
     */
    public function setRedis(mixed $redis) : HasRedisInterface;
}
