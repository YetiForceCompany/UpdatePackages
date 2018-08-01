<?php

namespace Sabre\Event\Promise;

use Sabre\Event\Loop;
use Sabre\Event\Promise;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
	public function testAll()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise\all([$promise1, $promise2])->then(function ($value) use (&$finalValue) {
			$finalValue = $value;
		});

		$promise1->fulfill(1);
		Loop\run();
		$this->assertSame(0, $finalValue);

		$promise2->fulfill(2);
		Loop\run();
		$this->assertSame([1, 2], $finalValue);
	}

	public function testAllReject()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise\all([$promise1, $promise2])->then(
			function ($value) use (&$finalValue) {
				$finalValue = 'foo';
				return 'test';
			},
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			}
		);

		$promise1->reject(1);
		Loop\run();
		$this->assertSame(1, $finalValue);
		$promise2->reject(2);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}

	public function testAllRejectThenResolve()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise\all([$promise1, $promise2])->then(
			function ($value) use (&$finalValue) {
				$finalValue = 'foo';
				return 'test';
			},
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			}
		);

		$promise1->reject(1);
		Loop\run();
		$this->assertSame(1, $finalValue);
		$promise2->fulfill(2);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}

	public function testRace()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise\race([$promise1, $promise2])->then(
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			},
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			}
		);

		$promise1->fulfill(1);
		Loop\run();
		$this->assertSame(1, $finalValue);
		$promise2->fulfill(2);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}

	public function testRaceReject()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise\race([$promise1, $promise2])->then(
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			},
			function ($value) use (&$finalValue) {
				$finalValue = $value;
			}
		);

		$promise1->reject(1);
		Loop\run();
		$this->assertSame(1, $finalValue);
		$promise2->reject(2);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}

	public function testResolve()
	{
		$finalValue = 0;

		$promise = resolve(1);
		$promise->then(function ($value) use (&$finalValue) {
			$finalValue = $value;
		});

		$this->assertSame(0, $finalValue);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testResolvePromise()
	{
		$finalValue = 0;

		$promise = new Promise();
		$promise->reject(new \Exception('uh oh'));

		$newPromise = resolve($promise);
		$newPromise->wait();
	}

	public function testReject()
	{
		$finalValue = 0;

		$promise = reject(1);
		$promise->then(function ($value) use (&$finalValue) {
			$finalValue = 'im broken';
		}, function ($reason) use (&$finalValue) {
			$finalValue = $reason;
		});

		$this->assertSame(0, $finalValue);
		Loop\run();
		$this->assertSame(1, $finalValue);
	}
}
