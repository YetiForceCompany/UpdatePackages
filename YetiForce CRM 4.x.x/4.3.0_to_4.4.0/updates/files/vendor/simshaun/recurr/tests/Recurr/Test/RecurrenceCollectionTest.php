<?php

namespace Recurr\Test;

use Recurr\Recurrence;
use Recurr\RecurrenceCollection;

class RecurrenceCollectionTest extends \PHPUnit_Framework_TestCase
{
	/** @var RecurrenceCollection */
	protected $collection;

	public function setUp()
	{
		$this->collection = new RecurrenceCollection(
			[
				new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
				new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
				new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
				new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
				new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
			]
		);
	}

	public function testStartsBetween()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
		];

		$after  = new \DateTime('2014-01-01');
		$before = new \DateTime('2014-05-01');
		$result = $this->collection->startsBetween($after, $before);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testStartsBetweenInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$after  = new \DateTime('2014-01-01');
		$before = new \DateTime('2014-05-01');
		$result = $this->collection->startsBetween($after, $before, true);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testStartsBefore()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
		];

		$result = $this->collection->startsBefore(new \DateTime('2014-03-01'));

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testStartsBeforeInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
		];

		$result = $this->collection->startsBefore(new \DateTime('2014-03-01'), true);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testStartsAfter()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$result = $this->collection->startsAfter(new \DateTime('2014-03-01'));

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testStartsAfterInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$result = $this->collection->startsAfter(new \DateTime('2014-03-01'), true);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsBetween()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
		];

		$after  = new \DateTime('2014-01-15');
		$before = new \DateTime('2014-05-15');
		$result = $this->collection->endsBetween($after, $before);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsBetweenInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$after  = new \DateTime('2014-01-15');
		$before = new \DateTime('2014-05-15');
		$result = $this->collection->endsBetween($after, $before, true);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsBefore()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
		];

		$result = $this->collection->endsBefore(new \DateTime('2014-03-15'));

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsBeforeInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-01-01'), new \DateTime('2014-01-15')),
			new Recurrence(new \DateTime('2014-02-01'), new \DateTime('2014-02-15')),
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
		];

		$result = $this->collection->endsBefore(new \DateTime('2014-03-15'), true);

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsAfter()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$result = $this->collection->endsAfter(new \DateTime('2014-03-15'));

		$this->assertSame($expected, array_values($result->toArray()));
	}

	public function testEndsAfterInc()
	{
		$expected = [
			new Recurrence(new \DateTime('2014-03-01'), new \DateTime('2014-03-15')),
			new Recurrence(new \DateTime('2014-04-01'), new \DateTime('2014-04-15')),
			new Recurrence(new \DateTime('2014-05-01'), new \DateTime('2014-05-15')),
		];

		$result = $this->collection->endsAfter(new \DateTime('2014-03-15'), true);

		$this->assertSame($expected, array_values($result->toArray()));
	}
}
