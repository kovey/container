<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2021-07-03 14:12:06
 *
 */
namespace Kovey\Container\Keyword;

class EventName
{
    const EVENT_SHARDING_DATABASE = 'ShardingDatabase';

    const EVENT_SHARDING_REDIS = 'ShardingRedis';

    const EVENT_DATABASE = 'Database';

    const EVENT_REDIS = 'Redis';

    const EVENT_GLOBAL_ID = 'GlobalId';

    const EVENT_ROUTER = 'Router';

    const EVENT_PROTOCOL = 'Protocol';
}
