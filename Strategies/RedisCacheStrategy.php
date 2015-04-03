<?php

namespace Openroot\Library\Cache\Strategies;

use Openroot\Library\Cache\Abstracts\AbstractCacheStrategy;
use Predis\Client as Redis;

/**
 * Class RedisCacheStrategy
 *
 * @package Openroot\Library\Cache\Strategies
 */
class RedisCacheStrategy extends AbstractCacheStrategy
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @return Redis
     */
    private function getRedis()
    {
        return $this->redis;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->redis->isConnected();
    }

    /**
     * @return self
     */
    public function connect()
    {
        if (!$this->isConnected()) {
            $this->redis->connect();
        }

        return $this;
    }

    /**
     * @return self
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->redis->disconnect();
        }

        return $this;
    }

    /**
     * @param string $prefix
     * @param string $key
     *
     * @return string
     */
    public function createKey($prefix, $key)
    {
        return sprintf("%s:%s", $prefix, $key);
    }

    /**
     * @param string   $cacheKey
     * @param string   $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setString($cacheKey, $value, $ttl = null)
    {
        $redis = $this->connect()->getRedis();

        $redis->SET($cacheKey, $value);
        if (null !== $ttl) {
            $redis->EXPIRE($cacheKey, $ttl);
        }

        return $this;
    }

    /**
     * @param string $cacheKey
     *
     * @return string|bool
     */
    public function getString($cacheKey)
    {
        $data = $this->connect()->getRedis()->GET($cacheKey);
        if (null === $data || false === $data) {
            return false;
        }

        return (string)$data;
    }

    /**
     * @param string $cacheKey
     *
     * @return self
     */
    public function deleteString($cacheKey)
    {
        $this->connect()->getRedis()->DEL($cacheKey);

        return $this;
    }
}
