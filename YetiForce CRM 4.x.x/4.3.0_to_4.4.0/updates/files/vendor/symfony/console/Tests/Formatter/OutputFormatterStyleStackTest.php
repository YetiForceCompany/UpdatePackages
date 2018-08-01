<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\Formatter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyleStack;

class OutputFormatterStyleStackTest extends TestCase
{
	public function testPush()
	{
		$stack = new OutputFormatterStyleStack();
		$stack->push($s1 = new OutputFormatterStyle('white', 'black'));
		$stack->push($s2 = new OutputFormatterStyle('yellow', 'blue'));

		$this->assertSame($s2, $stack->getCurrent());

		$stack->push($s3 = new OutputFormatterStyle('green', 'red'));

		$this->assertSame($s3, $stack->getCurrent());
	}

	public function testPop()
	{
		$stack = new OutputFormatterStyleStack();
		$stack->push($s1 = new OutputFormatterStyle('white', 'black'));
		$stack->push($s2 = new OutputFormatterStyle('yellow', 'blue'));

		$this->assertSame($s2, $stack->pop());
		$this->assertSame($s1, $stack->pop());
	}

	public function testPopEmpty()
	{
		$stack = new OutputFormatterStyleStack();
		$style = new OutputFormatterStyle();

		$this->assertSame($style, $stack->pop());
	}

	public function testPopNotLast()
	{
		$stack = new OutputFormatterStyleStack();
		$stack->push($s1 = new OutputFormatterStyle('white', 'black'));
		$stack->push($s2 = new OutputFormatterStyle('yellow', 'blue'));
		$stack->push($s3 = new OutputFormatterStyle('green', 'red'));

		$this->assertSame($s2, $stack->pop($s2));
		$this->assertSame($s1, $stack->pop());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidPop()
	{
		$stack = new OutputFormatterStyleStack();
		$stack->push(new OutputFormatterStyle('white', 'black'));
		$stack->pop(new OutputFormatterStyle('yellow', 'blue'));
	}
}
