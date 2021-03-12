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
     * @description db pool name
     *
     * @var string
     */
    private string $dbPoolName;

    /**
     * @description redis pool name
     *
     * @var string
     */
    private string $redisPoolName;

    /**
     * @description table name
     *
     * @var string
     */
    private string $tableName;

    /**
     * @description field name
     *
     * @var string
     */
    private string $fieldName;

    /**
     * @description primary name
     *
     * @var string
     */
    private string $primaryName;

    public function __construct(string $dbPoolName, string $redisPoolName, string $tableName, string $fieldName, string $primaryName)
    {
        $this->dbPoolName = $dbPoolName;
        $this->redisPoolName = $redisPoolName;
        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
        $this->primaryName = $primaryName;
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
     * @description get db pool name
     *
     * @return string
     */
    public function getDbPoolName() : string
    {
        return $this->dbPoolName;
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
     * @description get table name
     *
     * @return string
     */
    public function getTableName() : string
    {
        return $this->tableName;
    }

    /**
     * @description get feild name
     *
     * @return string
     */
    public function getFieldName() : string
    {
        return $this->fieldName;
    }

    /**
     * @description get primary name
     *
     * @return string
     */
    public function getPrimaryName() : string
    {
        return $this->primaryName;
    }
}
