<?php

namespace Openroot\Library\Cache\Service;

use Openroot\Library\Cache\Interfaces\CacheStrategyInterface;

/**
 * Class CacheManager
 *
 * @package Openroot\Library\Cache\Service
 */
class CacheManager
{
    /**
     * @var CacheStrategyInterface
     */
    private $cacheStrategy;

    /**
     * @var null|int
     */
    private $defaultTtl;

    /**
     * @var string
     */
    private $keyPrefix;

    /**
     * @param CacheStrategyInterface $cacheStrategy
     * @param string                 $keyPrefix
     * @param null|int               $defaultTtl
     */
    public function __construct(CacheStrategyInterface $cacheStrategy, $keyPrefix = 'cache', $defaultTtl = null)
    {
        $this->cacheStrategy = $cacheStrategy;
        $this->keyPrefix = $keyPrefix;
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Close open connections
     */
    public function __destruct()
    {
        if ($this->cacheStrategy->isConnected()) {
            $this->cacheStrategy->disconnect();
        }
    }

    /**
     * @param string   $key
     * @param string   $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setString($key, $value, $ttl = null)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $this->cacheStrategy->setString($cacheKey, (string)$value, ($ttl ?: $this->defaultTtl));

        return $this;
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public function getString($key)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $cacheData = $this->cacheStrategy->getString($cacheKey);
        if (false === $cacheData) {
            return null;
        }

        return (string)$cacheData;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function deleteString($key)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $this->cacheStrategy->deleteString($cacheKey);

        return $this;
    }

    /**
     * @param string   $key
     * @param array    $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setArray($key, array $value, $ttl = null)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $this->cacheStrategy->setArray($cacheKey, $value, ($ttl ?: $this->defaultTtl));

        return $this;
    }

    /**
     * @param string $key
     *
     * @return null|array
     */
    public function getArray($key)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $cacheData = $this->cacheStrategy->getArray($cacheKey);
        if (!$cacheData || !is_array($cacheData)) {
            return null;
        }

        return $cacheData;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function deleteArray($key)
    {
        $cacheKey = $this->cacheStrategy->createKey($this->keyPrefix, $key);
        $this->cacheStrategy->deleteArray($cacheKey);

        return $this;
    }
}
