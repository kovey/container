<?php
/**
 * @description protocol event
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

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Protocol implements EventInterface
{
    /**
     * @description code
     *
     * @var string | int
     */
    private string | int $code;

    /**
     * @description protobuf
     *
     * @var string
     */
    private string $protobuf;

    /**
     * @description protobuf base
     *
     * @var string
     */
    private string $protobufBase;

    /**
     * @description handler name
     *
     * @var string
     */
    private string $handler;

    /**
     * @description method
     *
     * @var string
     */
    private string $method;

    public function __construct(string | int $code, string $protobuf, string $protobufBase = '')
    {
        $this->code = $code;
        $this->protobuf = $protobuf;
        $this->protobufBase = $protobufBase;
        $this->handler = '';
        $this->method = '';
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
     * @description get code
     *
     * @return string | int
     */
    public function getCode() : string | int
    {
        return $this->code;
    }

    /**
     * @description get protobuf
     *
     * @return string
     */
    public function getProtobuf() : string
    {
        return $this->protobuf;
    }

    /**
     * @description get protobuf base
     *
     * @return string
     */
    public function getProtobufBase() : string
    {
        return $this->protobufBase;
    }

    /**
     * @description get router
     *
     * @return string
     */
    public function getRouter() : string
    {
        return $this->method . '@' . $this->handler;
    }

    /**
     * @description set method
     *
     * @param string $method
     *
     * @return Protocol
     */
    public function setMethod(string $method) : Protocol
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @description set handler
     *
     * @param string $handler
     *
     * @return Protocol
     */
    public function setHandler(string $handler) : Protocol
    {
        $this->handler = $handler;
        return $this;
    }
}
