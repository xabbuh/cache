<?php

namespace Openroot\Library\Cache\Interfaces;

/**
 * Interface CacheStrategyInterface
 *
 * @package Openroot\Library\Cache\Interfaces
 */
interface CacheStrategyInterface
{
    /**
     * @return bool
     */
    public function isConnected();

    /**
     * @return self
     */
    public function connect();

    /**
     * @return self
     */
    public function disconnect();

    /**
     * @param string $prefix
     * @param string $key
     *
     * @return string
     */
    public function createKey($prefix, $key);

    /**
     * @param string   $cacheKey
     * @param string   $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setString($cacheKey, $value, $ttl = null);

    /**
     * @param string $cacheKey
     *
     * @return string|bool
     */
    public function getString($cacheKey);

    /**
     * @param string $cacheKey
     *
     * @return self
     */
    public function deleteString($cacheKey);

    /**
     * @param string   $cacheKey
     * @param array    $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setArray($cacheKey, array $value, $ttl = null);

    /**
     * @param string $cacheKey
     *
     * @return array|bool
     */
    public function getArray($cacheKey);

    /**
     * @param string $cacheKey
     *
     * @return self
     */
    public function deleteArray($cacheKey);
}
