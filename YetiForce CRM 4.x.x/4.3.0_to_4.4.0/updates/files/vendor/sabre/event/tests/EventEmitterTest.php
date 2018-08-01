<?php

namespace Sabre\Event;

class EventEmitterTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{
		$ee = new EventEmitter();
		$this->assertInstanceOf('Sabre\\Event\\EventEmitter', $ee);
	}

	public function testListeners()
	{
		$ee = new EventEmitter();

		$callback1 = function () {
		};
		$callback2 = function () {
		};
		$ee->on('foo', $callback1, 200);
		$ee->on('foo', $callback2, 100);

		$this->assertSame([$callback2, $callback1], $ee->listeners('foo'));
	}

	/**
	 * @depends testInit
	 */
	public function testHandleEvent()
	{
		$argResult = null;

		$ee = new EventEmitter();
		$ee->on('foo', function ($arg) use (&$argResult) {
			$argResult = $arg;
		});

		$this->assertTrue(
			$ee->emit('foo', ['bar'])
		);

		$this->assertSame('bar', $argResult);
	}

	/**
	 * @depends testHandleEvent
	 */
	public function testCancelEvent()
	{
		$argResult = 0;

		$ee = new EventEmitter();
		$ee->on('foo', function ($arg) use (&$argResult) {
			$argResult = 1;
			return false;
		});
		$ee->on('foo', function ($arg) use (&$argResult) {
			$argResult = 2;
		});

		$this->assertFalse(
			$ee->emit('foo', ['bar'])
		);

		$this->assertSame(1, $argResult);
	}

	/**
	 * @depends testCancelEvent
	 */
	public function testPriority()
	{
		$argResult = 0;

		$ee = new EventEmitter();
		$ee->on('foo', function ($arg) use (&$argResult) {
			$argResult = 1;
			return false;
		});
		$ee->on('foo', function ($arg) use (&$argResult) {
			$argResult = 2;
			return false;
		}, 1);

		$this->assertFalse(
			$ee->emit('foo', ['bar'])
		);

		$this->assertSame(2, $argResult);
	}

	/**
	 * @depends testPriority
	 */
	public function testPriority2()
	{
		$result = [];
		$ee = new EventEmitter();

		$ee->on('foo', function () use (&$result) {
			$result[] = 'a';
		}, 200);
		$ee->on('foo', function () use (&$result) {
			$result[] = 'b';
		}, 50);
		$ee->on('foo', function () use (&$result) {
			$result[] = 'c';
		}, 300);
		$ee->on('foo', function () use (&$result) {
			$result[] = 'd';
		});

		$ee->emit('foo');
		$this->assertSame(['b', 'd', 'a', 'c'], $result);
	}

	public function testRemoveListener()
	{
		$result = false;

		$callBack = function () use (&$result) {
			$result = true;
		};

		$ee = new EventEmitter();

		$ee->on('foo', $callBack);

		$ee->emit('foo');
		$this->assertTrue($result);
		$result = false;

		$this->assertTrue(
			$ee->removeListener('foo', $callBack)
		);

		$ee->emit('foo');
		$this->assertFalse($result);
	}

	public function testRemoveUnknownListener()
	{
		$result = false;

		$callBack = function () use (&$result) {
			$result = true;
		};

		$ee = new EventEmitter();

		$ee->on('foo', $callBack);

		$ee->emit('foo');
		$this->assertTrue($result);
		$result = false;

		$this->assertFalse($ee->removeListener('bar', $callBack));

		$ee->emit('foo');
		$this->assertTrue($result);
	}

	public function testRemoveListenerTwice()
	{
		$result = false;

		$callBack = function () use (&$result) {
			$result = true;
		};

		$ee = new EventEmitter();

		$ee->on('foo', $callBack);

		$ee->emit('foo');
		$this->assertTrue($result);
		$result = false;

		$this->assertTrue(
			$ee->removeListener('foo', $callBack)
		);
		$this->assertFalse(
			$ee->removeListener('foo', $callBack)
		);

		$ee->emit('foo');
		$this->assertFalse($result);
	}

	public function testRemoveAllListeners()
	{
		$result = false;
		$callBack = function () use (&$result) {
			$result = true;
		};

		$ee = new EventEmitter();
		$ee->on('foo', $callBack);

		$ee->emit('foo');
		$this->assertTrue($result);
		$result = false;

		$ee->removeAllListeners('foo');

		$ee->emit('foo');
		$this->assertFalse($result);
	}

	public function testRemoveAllListenersNoArg()
	{
		$result = false;

		$callBack = function () use (&$result) {
			$result = true;
		};

		$ee = new EventEmitter();
		$ee->on('foo', $callBack);

		$ee->emit('foo');
		$this->assertTrue($result);
		$result = false;

		$ee->removeAllListeners();

		$ee->emit('foo');
		$this->assertFalse($result);
	}

	public function testOnce()
	{
		$result = 0;

		$callBack = function () use (&$result) {
			$result++;
		};

		$ee = new EventEmitter();
		$ee->once('foo', $callBack);

		$ee->emit('foo');
		$ee->emit('foo');

		$this->assertSame(1, $result);
	}

	/**
	 * @depends testCancelEvent
	 */
	public function testPriorityOnce()
	{
		$argResult = 0;

		$ee = new EventEmitter();
		$ee->once('foo', function ($arg) use (&$argResult) {
			$argResult = 1;
			return false;
		});
		$ee->once('foo', function ($arg) use (&$argResult) {
			$argResult = 2;
			return false;
		}, 1);

		$this->assertFalse(
			$ee->emit('foo', ['bar'])
		);

		$this->assertSame(2, $argResult);
	}
}
