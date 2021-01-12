<?php
/**
 * @description router event
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

#[\Attribute]
class Router implements EventInterface
{
    const ROUTER_CONTROLLER = 'Controller';

    const ROUTER_ACTION = 'Action';

    /**
     * @description path
     *
     * @var string
     */
    private string $path;

    /**
     * @description method
     *
     * @var string
     */
    private string $method;

    private string $controller;

    private string $action;

    private Array $rules;

    public function __construct(string $path, string $method)
    {
        $this->path = $path;
        $this->method = $method;
        $this->controller = '';
        $this->action = '';
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
     * @description get path
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * @description get method
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    public function getRouter() : string
    {
        return $this->action . '@' . $this->controller;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
        return $this;
    }

    public function setController(string $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function setRules(Array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function getRules() : Array
    {
        return $this->rules;
    }
}
