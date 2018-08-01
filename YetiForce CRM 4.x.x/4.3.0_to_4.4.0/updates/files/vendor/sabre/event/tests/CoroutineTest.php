<?php

namespace Sabre\Event;

class CoroutineTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNonGenerator()
	{
		coroutine(function () {
		});
	}

	public function testBasicCoroutine()
	{
		$start = 0;

		coroutine(function () use (&$start) {
			$start += 1;
			yield;
		});

		$this->assertSame(1, $start);
	}

	public function testFulfilledPromise()
	{
		$start = 0;
		$promise = new Promise(function ($fulfill, $reject) {
			$fulfill(2);
		});

		coroutine(function () use (&$start, $promise) {
			$start += 1;
			$start += (yield $promise);
		});

		Loop\run();
		$this->assertSame(3, $start);
	}

	public function testRejectedPromise()
	{
		$start = 0;
		$promise = new Promise(function ($fulfill, $reject) {
			$reject(2);
		});

		coroutine(function () use (&$start, $promise) {
			$start += 1;
			try {
				$start += (yield $promise);
				// This line is unreachable, but it's our control
				$start += 4;
			} catch (\Exception $e) {
				$start += $e->getMessage();
			}
		});

		Loop\run();
		$this->assertSame(3, $start);
	}

	public function testRejectedPromiseException()
	{
		$start = 0;
		$promise = new Promise(function ($fulfill, $reject) {
			$reject(new \LogicException('2'));
		});

		coroutine(function () use (&$start, $promise) {
			$start += 1;
			try {
				$start += (yield $promise);
				// This line is unreachable, but it's our control
				$start += 4;
			} catch (\LogicException $e) {
				$start += $e->getMessage();
			}
		});

		Loop\run();
		$this->assertSame(3, $start);
	}

	public function testRejectedPromiseArray()
	{
		$start = 0;
		$promise = new Promise(function ($fulfill, $reject) {
			$reject([]);
		});

		coroutine(function () use (&$start, $promise) {
			$start += 1;
			try {
				$start += (yield $promise);
				// This line is unreachable, but it's our control
				$start += 4;
			} catch (\Exception $e) {
				$this->assertTrue(strpos($e->getMessage(), 'Promise was rejected with') === 0);
				$start += 2;
			}
		})->wait();

		$this->assertSame(3, $start);
	}

	public function testFulfilledPromiseAsync()
	{
		$start = 0;
		$promise = new Promise();
		coroutine(function () use (&$start, $promise) {
			$start += 1;
			$start += (yield $promise);
		});
		Loop\run();

		$this->assertSame(1, $start);

		$promise->fulfill(2);
		Loop\run();

		$this->assertSame(3, $start);
	}

	public function testRejectedPromiseAsync()
	{
		$start = 0;
		$promise = new Promise();
		coroutine(function () use (&$start, $promise) {
			$start += 1;
			try {
				$start += (yield $promise);
				// This line is unreachable, but it's our control
				$start += 4;
			} catch (\Exception $e) {
				$start += $e->getMessage();
			}
		});

		$this->assertSame(1, $start);

		$promise->reject(new \Exception(2));
		Loop\run();

		$this->assertSame(3, $start);
	}

	public function testCoroutineException()
	{
		$start = 0;
		coroutine(function () use (&$start) {
			$start += 1;
			$start += (yield 2);

			throw new \Exception('4');
		})->error(function ($e) use (&$start) {
			$start += $e->getMessage();
		});
		Loop\run();

		$this->assertSame(7, $start);
	}

	public function testDeepException()
	{
		$start = 0;
		$promise = new Promise();
		coroutine(function () use (&$start, $promise) {
			$start += 1;
			$start += (yield $promise);
		})->error(function ($e) use (&$start) {
			$start += $e->getMessage();
		});

		$this->assertSame(1, $start);

		$promise->reject(new \Exception(2));
		Loop\run();

		$this->assertSame(3, $start);
	}

	public function testResolveToLastYield()
	{
		$ok = false;
		coroutine(function () {
			yield 1;
			yield 2;
			$hello = 'hi';
		})->then(function ($value) use (&$ok) {
			$this->assertSame(2, $value);
			$ok = true;
		})->error(function ($reason) {
			$this->fail($reason);
		});
		Loop\run();

		$this->assertTrue($ok);
	}

	public function testResolveToLastYieldPromise()
	{
		$ok = false;

		$promise = new Promise();

		coroutine(function () use ($promise) {
			yield 'fail';
			yield $promise;
			$hello = 'hi';
		})->then(function ($value) use (&$ok) {
			$ok = $value;
			$this->fail($reason);
		});

		$promise->fulfill('omg it worked');
		Loop\run();

		$this->assertSame('omg it worked', $ok);
	}
}
