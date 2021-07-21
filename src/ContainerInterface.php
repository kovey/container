<?php
/**
 *
 * @description container interface
 *
 * @package     Kovey\Container
 *
 * @time        2019-10-18 09:15:37
 *
 * @author      kovey
 */

namespace Kovey\Container;

interface ContainerInterface
{
    /**
     * @description 获取实例
     *
     * @param string $class
     *
     * @param string $traceId
     *
     * @param Array $ext
     *
     * @param ...mixed $args
     *
     * @return mixed
     */
    public function get(string $class, string $traceId, string $spanId, Array $ext = array(), ...$args) : mixed;

    /**
     * @description method arguments
     *
     * @param string $class
     *
     * @param string $method
     *
     * @param string $traceId
     *
     * @param Array $ext
     *
     * @return Array
     */
    public function getMethodArguments(string $class, string $method, string $traceId, string $spanId, Array $ext = array()) : Array;

    /**
     * @description 获取关键字
     *
     * @param string $class
     *
     * @param string $methods
     * 
     * @return Array
     */
    public function getKeywords(string $class, string $method) : Array;

    /**
     * @description event
     *
     * @param string $event
     * 
     * @param callable | Array $fun
     *
     * @return $this
     */
    public function on(string $event, callable | Array $fun) : ContainerInterface;

    /**
     * @description parse
     *
     * @param string $dir
     *
     * @param string $namespace
     *
     * @param string $suffix = ''
     * 
     * @return $this
     */
    public function parse(string $dir, string $namespace, string $suffix = '') : ContainerInterface;

    /**
     * @description open check multi instance
     *
     * @return ContainerInterface
     */
    public function openCheckMultiInstance() : ContainerInterface;
}
