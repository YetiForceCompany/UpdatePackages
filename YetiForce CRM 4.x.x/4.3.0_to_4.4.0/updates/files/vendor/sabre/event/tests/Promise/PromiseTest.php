<?php

namespace Sabre\Event\Promise;

use Sabre\Event\Loop;
use Sabre\Event\Promise;

class PromiseTest extends \PHPUnit_Framework_TestCase
{
	public function testSuccess()
	{
		$finalValue = 0;
		$promise = new Promise();
		$promise->fulfill(1);

		$promise->then(function ($value) use (&$finalValue) {
			$finalValue = $value + 2;
		});
		Loop\run();

		$this->assertSame(3, $finalValue);
	}

	public function testFail()
	{
		$finalValue = 0;
		$promise = new Promise();
		$promise->reject(1);

		$promise->then(null, function ($value) use (&$finalValue) {
			$finalValue = $value + 2;
		});
		Loop\run();

		$this->assertSame(3, $finalValue);
	}

	public function testChain()
	{
		$finalValue = 0;
		$promise = new Promise();
		$promise->fulfill(1);

		$promise->then(function ($value) use (&$finalValue) {
			$finalValue = $value + 2;
			return $finalValue;
		})->then(function ($value) use (&$finalValue) {
			$finalValue = $value + 4;
			return $finalValue;
		});
		Loop\run();

		$this->assertSame(7, $finalValue);
	}

	public function testChainPromise()
	{
		$finalValue = 0;
		$promise = new Promise();
		$promise->fulfill(1);

		$subPromise = new Promise();

		$promise->then(function ($value) use ($subPromise) {
			return $subPromise;
		})->then(function ($value) use (&$finalValue) {
			$finalValue = $value + 4;
			return $finalValue;
		});

		$subPromise->fulfill(2);
		Loop\run();

		$this->assertSame(6, $finalValue);
	}

	public function testPendingResult()
	{
		$finalValue = 0;
		$promise = new Promise();

		$promise->then(function ($value) use (&$finalValue) {
			$finalValue = $value + 2;
		});

		$promise->fulfill(4);
		Loop\run();

		$this->assertSame(6, $finalValue);
	}

	public function testPendingFail()
	{
		$finalValue = 0;
		$promise = new Promise();

		$promise->then(null, function ($value) use (&$finalValue) {
			$finalValue = $value + 2;
		});

		$promise->reject(4);
		Loop\run();

		$this->assertSame(6, $finalValue);
	}

	public function testExecutorSuccess()
	{
		$promise = (new Promise(function ($success, $fail) {
			$success('hi');
		}))->then(function ($result) use (&$realResult) {
			$realResult = $result;
		});
		Loop\run();

		$this->assertSame('hi', $realResult);
	}

	public function testExecutorFail()
	{
		$promise = (new Promise(function ($success, $fail) {
			$fail('hi');
		}))->then(function ($result) use (&$realResult) {
			$realResult = 'incorrect';
		}, function ($reason) use (&$realResult) {
			$realResult = $reason;
		});
		Loop\run();

		$this->assertSame('hi', $realResult);
	}

	/**
	 * @expectedException \Sabre\Event\PromiseAlreadyResolvedException
	 */
	public function testFulfillTwice()
	{
		$promise = new Promise();
		$promise->fulfill(1);
		$promise->fulfill(1);
	}

	/**
	 * @expectedException \Sabre\Event\PromiseAlreadyResolvedException
	 */
	public function testRejectTwice()
	{
		$promise = new Promise();
		$promise->reject(1);
		$promise->reject(1);
	}

	public function testFromFailureHandler()
	{
		$ok = 0;
		$promise = new Promise();
		$promise->otherwise(function ($reason) {
			$this->assertSame('foo', $reason);
			throw new \Exception('hi');
		})->then(function () use (&$ok) {
			$ok = -1;
		}, function () use (&$ok) {
			$ok = 1;
		});

		$this->assertSame(0, $ok);
		$promise->reject('foo');
		Loop\run();

		$this->assertSame(1, $ok);
	}

	public function testAll()
	{
		$promise1 = new Promise();
		$promise2 = new Promise();

		$finalValue = 0;
		Promise::all([$promise1, $promise2])->then(function ($value) use (&$finalValue) {
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
		Promise::all([$promise1, $promise2])->then(
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
		Promise::all([$promise1, $promise2])->then(
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

	public function testWaitResolve()
	{
		$promise = new Promise();
		Loop\nextTick(function () use ($promise) {
			$promise->fulfill(1);
		});
		$this->assertSame(
			1,
			$promise->wait()
		);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testWaitWillNeverResolve()
	{
		$promise = new Promise();
		$promise->wait();
	}

	public function testWaitRejectedException()
	{
		$promise = new Promise();
		Loop\nextTick(function () use ($promise) {
			$promise->reject(new \OutOfBoundsException('foo'));
		});
		try {
			$promise->wait();
			$this->fail('We did not get the expected exception');
		} catch (\Exception $e) {
			$this->assertInstanceOf('OutOfBoundsException', $e);
			$this->assertSame('foo', $e->getMessage());
		}
	}

	public function testWaitRejectedScalar()
	{
		$promise = new Promise();
		Loop\nextTick(function () use ($promise) {
			$promise->reject('foo');
		});
		try {
			$promise->wait();
			$this->fail('We did not get the expected exception');
		} catch (\Exception $e) {
			$this->assertInstanceOf('Exception', $e);
			$this->assertSame('foo', $e->getMessage());
		}
	}

	public function testWaitRejectedNonScalar()
	{
		$promise = new Promise();
		Loop\nextTick(function () use ($promise) {
			$promise->reject([]);
		});
		try {
			$promise->wait();
			$this->fail('We did not get the expected exception');
		} catch (\Exception $e) {
			$this->assertInstanceOf('Exception', $e);
			$this->assertSame('Promise was rejected with reason of type: array', $e->getMessage());
		}
	}
}
