<?php

namespace Openroot\Library\Cache\Tests\Strategies;

use Openroot\Library\Cache\Strategies\SessionCacheStrategy;

require_once __DIR__ . '/../../Interfaces/CacheStrategyInterface.php';
require_once __DIR__ . '/../../Abstracts/AbstractCacheStrategy.php';
require_once __DIR__ . '/../../Strategies/SessionCacheStrategy.php';

class SessionStrategyTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TIME = 123456;

    private function getSessionMock($isStarted, \PHPUnit_Framework_MockObject_Matcher_Invocation $expected)
    {
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session', ['isStarted', 'get', 'set', 'has', 'remove']);
        $session
            ->expects($expected)
            ->method('isStarted')
            ->willReturn((bool)$isStarted);

        return $session;
    }

    public function testBasics()
    {
        $strategy = new SessionCacheStrategy($this->getSessionMock(false, $this->once()));

        $this->assertAttributeNotEquals(static::TEST_TIME, 'time', $strategy);
        $strategy->setTime(static::TEST_TIME);
        $this->assertAttributeEquals(static::TEST_TIME, 'time', $strategy);

        $this->assertFalse($strategy->isConnected());
        $this->assertEquals($strategy, $strategy->disconnect());
        $this->assertEquals('prefix_key', $strategy->createKey('prefix', 'key'));
    }

    public function testStrings_1of3()
    {
        $session = $this->getSessionMock(true, $this->atLeastOnce());
        $session
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn(null);

        $session
            ->expects($this->once())
            ->method('set')
            ->with('test', ['value' => 'value', 'ttl' => (static::TEST_TIME + 123)])
            ->willReturn(null);

        $strategy = new SessionCacheStrategy($session);
        $strategy->setTime(static::TEST_TIME);

        $this->assertFalse($strategy->getString('test'));
        $this->assertEquals($strategy, $strategy->setString('test', 'value', 123));
    }

    public function testStrings_2of3()
    {
        $session = $this->getSessionMock(true, $this->atLeastOnce());
        $session
            ->expects($this->exactly(2))
            ->method('get')
            ->with('test')
            ->willReturn(['value' => 'value', 'ttl' => 123]);

        $strategy = new SessionCacheStrategy($session);

        $this->assertFalse($strategy->getString('test'));

        $strategy->setTime(100);
        $this->assertEquals('value', $strategy->getString('test'));
    }

    public function testStrings_3of3()
    {
        $session = $this->getSessionMock(true, $this->atLeastOnce());
        $session
            ->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(true);
        $session
            ->expects($this->once())
            ->method('remove')
            ->with('test');

        $strategy = new SessionCacheStrategy($session);

        $this->assertEquals($strategy, $strategy->deleteString('test'));
    }

    public function testArray_1of2()
    {
        $key = 'test';
        $arr = ['my' => 'array'];
        $ttl = 123;

        $session = $this->getSessionMock(true, $this->atLeastOnce());
        $session
            ->expects($this->once())
            ->method('set')
            ->with($key, ['value' => serialize($arr), 'ttl' => $ttl]);
        $session
            ->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(['value' => serialize($arr), 'ttl' => $ttl]);

        $strategy = new SessionCacheStrategy($session);
        $strategy->setTime(0);

        $this->assertEquals($strategy, $strategy->setArray($key, $arr, $ttl));
        $this->assertEquals($arr, $strategy->getArray($key));
    }

    public function testArray_2of2()
    {
        $session = $this->getSessionMock(true, $this->atLeastOnce());
        $session
            ->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(true);
        $session
            ->expects($this->once())
            ->method('remove')
            ->with('test');

        $strategy = new SessionCacheStrategy($session);

        $this->assertEquals($strategy, $strategy->deleteArray('test'));
    }
}
