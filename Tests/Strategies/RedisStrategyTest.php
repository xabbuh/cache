<?php

namespace Openroot\Library\Cache\Tests\Strategies;

use Openroot\Library\Cache\Strategies\RedisCacheStrategy;

require_once __DIR__ . '/../../Interfaces/CacheStrategyInterface.php';
require_once __DIR__ . '/../../Abstracts/AbstractCacheStrategy.php';
require_once __DIR__ . '/../../Strategies/RedisCacheStrategy.php';

class RedisStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRedisMock()
    {
        return $this->getMock('Predis\Client', ['isConnected', 'GET', 'SET', 'DEL', 'EXPIRE', 'connect', 'disconnect']);
    }

    /**
     * @return RedisCacheStrategy
     */
    private function getRedisStrategyFake($redisMock)
    {
        $ref = new \ReflectionClass('Openroot\Library\Cache\Strategies\RedisCacheStrategy');
        $redisProp = $ref->getProperty('redis');
        $redisProp->setAccessible(true);

        $mock = $ref->newInstanceWithoutConstructor();
        $redisProp->setValue($mock, $redisMock);

        return $mock;
    }

    public function testBasics_1of2()
    {
        $redisMock = $this->getRedisMock();
        $redisMock
            ->expects($this->exactly(3))
            ->method('isConnected')
            ->willReturn(false);
        $redisMock
            ->expects($this->never())
            ->method('disconnect');
        $redisMock
            ->expects($this->once())
            ->method('connect');

        $strategy = $this->getRedisStrategyFake($redisMock);

        $this->assertFalse($strategy->isConnected());
        $this->assertEquals($strategy, $strategy->disconnect());
        $this->assertEquals($strategy, $strategy->connect());
    }

    public function testBasics_2of2()
    {
        $redisMock = $this->getRedisMock();
        $redisMock
            ->expects($this->exactly(2))
            ->method('isConnected')
            ->willReturn(true);
        $redisMock
            ->expects($this->never())
            ->method('connect');
        $redisMock
            ->expects($this->once())
            ->method('disconnect');

        $strategy = $this->getRedisStrategyFake($redisMock);

        $this->assertTrue($strategy->isConnected());
        $this->assertEquals($strategy, $strategy->disconnect());
    }

    public function testStrings_1of2()
    {
        $redisMock = $this->getRedisMock();
        $redisMock
            ->expects($this->once())
            ->method('GET')
            ->with('test')
            ->willReturn(null);

        $redisMock
            ->expects($this->once())
            ->method('SET')
            ->with('test', 'value');

        $redisMock
            ->expects($this->once())
            ->method('EXPIRE')
            ->with('test', 123);

        $strategy = $this->getRedisStrategyFake($redisMock);

        $this->assertFalse($strategy->getString('test'));
        $this->assertEquals($strategy, $strategy->setString('test', 'value', 123));
    }

    public function testStrings_2of2()
    {
        $redisMock = $this->getRedisMock();
        $redisMock
            ->expects($this->once())
            ->method('GET')
            ->with('test')
            ->willReturn('value');

        $redisMock
            ->expects($this->once())
            ->method('DEL')
            ->with('test');

        $strategy = $this->getRedisStrategyFake($redisMock);
        $this->assertEquals('value', $strategy->getString('test'));
        $this->assertEquals($strategy, $strategy->deleteString('test'));
    }

    public function testArray()
    {
        $key = 'test';
        $arr = ['my' => 'array'];
        $ttl = 123;

        $redisMock = $this->getRedisMock();
        $redisMock
            ->expects($this->once())
            ->method('SET')
            ->with($key, serialize($arr));
        $redisMock
            ->expects($this->once())
            ->method('EXPIRE')
            ->with($key, $ttl);
        $redisMock
            ->expects($this->once())
            ->method('GET')
            ->with($key)
            ->willReturn(serialize($arr));
        $redisMock
            ->expects($this->once())
            ->method('DEL')
            ->with('test');

        $strategy = $this->getRedisStrategyFake($redisMock);

        $this->assertEquals($strategy, $strategy->setArray($key, $arr, $ttl));
        $this->assertEquals($arr, $strategy->getArray($key));
        $this->assertEquals($strategy, $strategy->deleteArray($key));
    }
}
