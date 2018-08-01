<?php

use KzykHys\CsvParser\Iterator\CsvIterator;

class CsvIteratorTest extends PHPUnit_Framework_TestCase
{
	public function testBlank()
	{
		$iterator = new CsvIterator(new ArrayIterator());
		$result   = iterator_to_array($iterator);

		$this->assertSame([], $result);
	}

	public function testArrayKey()
	{
		$iterator = new CsvIterator(new ArrayIterator(['1,2']), ['header' => ['a', 'b']]);
		$result = iterator_to_array($iterator);

		$this->assertSame([['a' => 1, 'b' => 2]], $result);
	}
}
