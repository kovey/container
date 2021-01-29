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
    /**
     * @description router controller suffix
     *
     * @var string
     */
    const ROUTER_CONTROLLER = 'Controller';

    /**
     * @description router action suffix
     *
     * @var string
     */
    const ROUTER_ACTION = 'Action';

    /**
     * @description http request method get
     *
     * @var string
     */
    const ROUTER_METHOD_GET = 'GET';

    /**
     * @description http request method post
     *
     * @var string
     */
    const ROUTER_METHOD_POST = 'POST';

    /**
     * @description http request method put
     *
     * @var string
     */
    const ROUTER_METHOD_PUT = 'PUT';

    /**
     * @description http request method delete
     *
     * @var string
     */
    const ROUTER_METHOD_DELETE = 'DELETE';

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

    /**
     * @description controller name
     *
     * @var string
     */
    private string $controller;

    /**
     * @description action name
     *
     * @var string
     */
    private string $action;

    /**
     * @description validator rules
     *
     * @var Array
     */
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

    /**
     * @description get router
     *
     * @return string
     */
    public function getRouter() : string
    {
        return $this->action . '@' . $this->controller;
    }

    /**
     * @description set action
     *
     * @param string $action
     *
     * @return Router
     */
    public function setAction(string $action) : Router
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @description set controller
     *
     * @param string $controller
     *
     * @return Router
     */
    public function setController(string $controller) : Router
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @description set rules
     *
     * @param Array $rules
     *
     * @return Array
     */
    public function setRules(Array $rules) : Router
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @description get rules
     *
     * @return Array
     */
    public function getRules() : Array
    {
        return $this->rules;
    }
}
