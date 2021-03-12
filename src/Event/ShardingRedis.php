<?php
/**
 * @description sharding redis event
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
class ShardingRedis implements EventInterface
{
    /**
     * @description pool name
     *
     * @var string
     */
    private string $poolName;

    public function __construct(string $poolName)
    {
        $this->poolName = $poolName;
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
     * @description get pool name
     *
     * @return string
     */
    public function getPoolName() : string
    {
        return $this->poolName;
    }
}
