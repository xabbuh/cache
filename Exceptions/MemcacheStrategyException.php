<?php

namespace Openroot\Library\Cache\Exceptions;

/**
 * Class MemcacheStrategyException
 *
 * @package Openroot\Library\Cache\Exceptions
 */
class MemcacheStrategyException extends CacheStrategyException
{
    /**
     * @param int $ttl
     *
     * @return static
     */
    public static function ttl($ttl)
    {
        return new static(
            sprintf(
                "The provided TTL (%s) is not allowed! The maximum TTL for 'Memcache' is set to 30 days (2592000 seconds). Timestamps aren't supported by this library yet.",
                $ttl
            )
        );
    }
}
