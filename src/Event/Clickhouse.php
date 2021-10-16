<?php
/**
 * @description clickhouse event
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
class Clickhouse implements EventInterface
{
    /**
     * @description pool name
     *
     * @var string
     */
    private string $clusterOpen;

    public function __construct(string $clusterOpen)
    {
        $this->clusterOpen = $clusterOpen;
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
    public function getClusterOpen() : string
    {
        return $this->clusterOpen;
    }
}
