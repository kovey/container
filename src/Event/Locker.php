<?php
/**
 * @description locker event
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
class Locker implements EventInterface
{
    /**
     * @description locker name
     *
     * @var string
     */
    private int $expire;

    /**
     * @description locker key
     */
    private string $key;

    public function __construct(string | int $key, int $expire = 60)
    {
        $this->key = $key;
        $this->expire = $expire;
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
    public function getExpire() : int
    {
        return $this->expire;
    }

    public function getKey() : string
    {
        return $this->key;
    }
}
