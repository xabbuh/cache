<?php

namespace Openroot\Library\Cache\Abstracts;

use Openroot\Library\Cache\Interfaces\CacheStrategyInterface;

/**
 * Class AbstractCacheStrategy
 *
 * @package Openroot\Library\Cache\Abstracts
 */
abstract class AbstractCacheStrategy implements CacheStrategyInterface
{
    /**
     * @param string   $cacheKey
     * @param array    $value
     * @param null|int $ttl
     *
     * @return self
     */
    public function setArray($cacheKey, array $value, $ttl = null)
    {
        return $this->setString($cacheKey, serialize($value), $ttl);
    }

    /**
     * @param string $cacheKey
     *
     * @return array|bool
     */
    public function getArray($cacheKey)
    {
        $data = $this->getString($cacheKey);
        if (false !== $data) {
            return (array)unserialize($data);
        }

        return false;
    }

    /**
     * @param string $cacheKey
     *
     * @return self
     */
    public function deleteArray($cacheKey)
    {
        $this->deleteString($cacheKey);

        return $this;
    }
}
