<?php

namespace Openroot\Library\Cache\Tests\Strategies;

use Openroot\Library\Cache\Strategies\MemcacheCacheStrategy;

require_once __DIR__ . '/../../Interfaces/CacheStrategyInterface.php';
require_once __DIR__ . '/../../Abstracts/AbstractCacheStrategy.php';
require_once __DIR__ . '/../../Strategies/MemcacheCacheStrategy.php';

class MemcacheStrategyTest extends \PHPUnit_Framework_TestCase
{
    const FLAG_VALUE = 999;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMemcacheMock()
    {
        return $this->getMock('\Memcache', ['isConnected', 'get', 'set', 'delete', 'addServer']);
    }

    /**
     * @return MemcacheCacheStrategy
     */
    private function getMemcacheStrategyFake($memcacheMock)
    {
        $ref = new \ReflectionClass('Openroot\Library\Cache\Strategies\MemcacheCacheStrategy');
        $memcacheProp = $ref->getProperty('memcache');
        $memcacheProp->setAccessible(true);

        $flagProp = $ref->getProperty('compressionFlag');
        $flagProp->setAccessible(true);

        $mock = $ref->newInstanceWithoutConstructor();
        $memcacheProp->setValue($mock, $memcacheMock);
        $flagProp->setValue($mock, static::FLAG_VALUE);

        return $mock;
    }

    public function testBasics()
    {
        $memcacheMock = $this->getMemcacheMock();

        $strategy = $this->getMemcacheStrategyFake($memcacheMock);

        $this->assertFalse($strategy->isConnected());
        $this->assertEquals($strategy, $strategy->disconnect());
        $this->assertEquals($strategy, $strategy->connect());
        $this->assertTrue($strategy->isConnected());
    }

    public function testStrings_1of2()
    {
        $memcacheMock = $this->getMemcacheMock();
        $memcacheMock
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn(false);

        $memcacheMock
            ->expects($this->once())
            ->method('set')
            ->with('test', 'value', static::FLAG_VALUE, 123);

        $strategy = $this->getMemcacheStrategyFake($memcacheMock);

        $this->assertFalse($strategy->getString('test'));
        $this->assertEquals($strategy, $strategy->setString('test', 'value', 123));
    }

    public function testStrings_2of2()
    {
        $memcacheMock = $this->getMemcacheMock();
        $memcacheMock
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn('value');

        $memcacheMock
            ->expects($this->once())
            ->method('delete')
            ->with('test');

        $strategy = $this->getMemcacheStrategyFake($memcacheMock);
        $this->assertEquals('value', $strategy->getString('test'));
        $this->assertEquals($strategy, $strategy->deleteString('test'));
    }

    public function testArray()
    {
        $key = 'test';
        $arr = ['my' => 'array'];
        $ttl = 123;

        $memcacheMock = $this->getMemcacheMock();
        $memcacheMock
            ->expects($this->once())
            ->method('set')
            ->with($key, serialize($arr), static::FLAG_VALUE, $ttl);
        $memcacheMock
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(serialize($arr));
        $memcacheMock
            ->expects($this->once())
            ->method('delete')
            ->with('test');

        $strategy = $this->getMemcacheStrategyFake($memcacheMock);

        $this->assertEquals($strategy, $strategy->setArray($key, $arr, $ttl));
        $this->assertEquals($arr, $strategy->getArray($key));
        $this->assertEquals($strategy, $strategy->deleteArray($key));
    }
}
