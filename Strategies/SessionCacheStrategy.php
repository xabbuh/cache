<?php

namespace Openroot\Library\Cache\Strategies;

use Openroot\Library\Cache\Abstracts\AbstractCacheStrategy;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionCacheStrategy
 *
 * @package Openroot\Library\Cache\Strategies
 */
class SessionCacheStrategy extends AbstractCacheStrategy
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var int
     */
    private $time;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->time = time();
    }

    /**
     * @return Session
     */
    private function getSession()
    {
        return $this->session;
    }

    /**
     * @param int $time
     *
     * @return self
     */
    public function setTime($time)
    {
        $this->time = (int)$time;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->session->isStarted();
    }

    /**
     * @return self
     */
    public function connect()
    {
        if (!$this->isConnected()) {
            $this->session->start();
        }

        return $this;
    }

    /**
     * @return self
     */
    public function disconnect()
    {
        // do nothing
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
     */
    public function setString($cacheKey, $value, $ttl = null)
    {
        if (null !== $ttl) {
            $ttl = (int)$ttl + $this->time;
        }

        $this->connect()->getSession()->set(
            $cacheKey,
            [
                'value' => (string)$value,
                'ttl'   => $ttl
            ]
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
        $data = $this->connect()->getSession()->get($cacheKey);
        if ($data && is_array($data)) {
            if (isset($data['ttl']) && null !== $data['ttl'] && $data['ttl'] < $this->time) {
                $this->deleteString($cacheKey);
            } else {
                if (isset($data['value'])) {
                    return (string)$data['value'];
                }
            }
        }

        return false;
    }

    /**
     * @param string $cacheKey
     *
     * @return self
     */
    public function deleteString($cacheKey)
    {
        $session = $this->connect()->getSession();
        if ($session->has($cacheKey)) {
            $session->remove($cacheKey);
        }

        return $this;
    }
}
