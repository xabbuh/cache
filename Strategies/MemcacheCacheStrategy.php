<?php

namespace Openroot\Library\Cache\Strategies;

use Openroot\Library\Cache\Abstracts\AbstractCacheStrategy;
use Openroot\Library\Cache\Exceptions\MemcacheStrategyException;

/**
 * Class MemcacheCacheStrategy
 *
 * @package Openroot\Library\Cache\Strategies
 */
class MemcacheCacheStrategy extends AbstractCacheStrategy
{
    /**
     * @var \Memcache
     */
    private $memcache;

    /**
     * @var null|bool
     */
    private $connected;

    /**
     * @var int
     */
    private $compressionFlag;

    /**
     * @param string $host
     * @param int    $port
     * @param bool   $useZlibCompression
     */
    public function __construct($host = 'localhost', $port = 11211, $useZlibCompression = false)
    {
        $this->compressionFlag = ((bool)$useZlibCompression ? MEMCACHE_COMPRESSED : 0);

        $this->connected = false;
        $this->memcache = new \Memcache();
        $this->addServer($host, $port);
    }

    /**
     * @param string $host
     * @param int    $port
     *
     * @return self
     */
    public function addServer($host, $port)
    {
        $this->memcache->addServer((string)$host, (int)$port);

        return $this;
    }

    /**
     * @return \Memcache
     */
    private function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (bool)$this->connected;
    }

    /**
     * @return self
     */
    public function connect()
    {
        $this->connected = true;

        return $this;
    }

    /**
     * @return self
     */
    public function disconnect()
    {
        if ($this->connected) {
            $this->memcache->close();
            $this->connected = false;
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
        return sprintf("%s_%s", $prefix, $key);
    }

    /**
     * @param string   $cacheKey
     * @param string   $value
     * @param null|int $ttl
     *
     * @return self
     * @throws MemcacheStrategyException
     */
    public function setString($cacheKey, $value, $ttl = null)
    {
        // normalize the ttl
        $ttl = max(0, (int)$ttl);
        if ($ttl > 2592000) {
            throw MemcacheStrategyException::ttl($ttl);
        }

        $this->connect()->getMemcache()->set(
            $cacheKey,
            (string)$value,
            $this->compressionFlag,
            $ttl
        );

        return $this;
    }

    /**
     * @param string $cacheKey
     *
     * @return string|bool
     */
    public function getString($cacheKey)
    {
        $data = $this->connect()->getMemcache()->get($cacheKey, $this->compressionFlag);
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
        $this->connect()->getMemcache()->delete($cacheKey);

        return $this;
    }
}
