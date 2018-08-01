<?php

namespace Sabre\Event;

class ContinueCallbackTest extends \PHPUnit_Framework_TestCase
{
	public function testContinueCallBack()
	{
		$ee = new EventEmitter();

		$handlerCounter = 0;
		$bla = function () use (&$handlerCounter) {
			$handlerCounter++;
		};
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);

		$continueCounter = 0;
		$r = $ee->emit('foo', [], function () use (&$continueCounter) {
			$continueCounter++;
			return true;
		});
		$this->assertTrue($r);
		$this->assertSame(3, $handlerCounter);
		$this->assertSame(2, $continueCounter);
	}

	public function testContinueCallBackBreak()
	{
		$ee = new EventEmitter();

		$handlerCounter = 0;
		$bla = function () use (&$handlerCounter) {
			$handlerCounter++;
		};
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);

		$continueCounter = 0;
		$r = $ee->emit('foo', [], function () use (&$continueCounter) {
			$continueCounter++;
			return false;
		});
		$this->assertTrue($r);
		$this->assertSame(1, $handlerCounter);
		$this->assertSame(1, $continueCounter);
	}

	public function testContinueCallBackBreakByHandler()
	{
		$ee = new EventEmitter();

		$handlerCounter = 0;
		$bla = function () use (&$handlerCounter) {
			$handlerCounter++;
			return false;
		};
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);
		$ee->on('foo', $bla);

		$continueCounter = 0;
		$r = $ee->emit('foo', [], function () use (&$continueCounter) {
			$continueCounter++;
			return false;
		});
		$this->assertFalse($r);
		$this->assertSame(1, $handlerCounter);
		$this->assertSame(0, $continueCounter);
	}
}
