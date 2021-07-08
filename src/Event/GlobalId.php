<?php
/**
 * @description global id event
 *
 * @package Kovey\Container\Event
 *
 * @author kovey
 *
 * @time 2021-01-06 14:49:04
 *
 */
namespace Kovey\Container\Event;

use Kovey\Event\EventInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class GlobalId implements EventInterface
{
    /**
     * @description redis pool name
     *
     * @var string
     */
    private string $redisPoolName;

    /**
     * @description global key
     *
     * @var string
     */
    private string $globalKey;

    public function __construct(string $redisPoolName, string $globalKey)
    {
        $this->redisPoolName = $redisPoolName;
        $this->globalKey = $globalKey;
    }

    /**
     * @description propagation stopped
     *
     * @return bool
     */
    public function isPropagationStopped() : bool
    {
        return true;
    }

    /**
     * @description stop propagation
     *
     * @return EventInterface
     */
    public function stopPropagation() : EventInterface
    {
        return $this;
    }

    /**
     * @description get redis pool name
     *
     * @return string
     */
    public function getRedisPoolName() : string
    {
        return $this->redisPoolName;
    }

    /**
     * @description get global key
     *
     * @return string
     */
    public function getGlobalKey() : string
    {
        return $this->globalKey;
    }
}
