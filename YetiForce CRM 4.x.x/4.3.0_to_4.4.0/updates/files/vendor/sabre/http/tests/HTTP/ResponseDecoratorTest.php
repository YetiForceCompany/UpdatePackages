<?php

namespace Sabre\HTTP;

class ResponseDecoratorTest extends \PHPUnit_Framework_TestCase
{
	protected $inner;
	protected $outer;

	public function setUp()
	{
		$this->inner = new Response();
		$this->outer = new ResponseDecorator($this->inner);
	}

	public function testStatus()
	{
		$this->outer->setStatus(201);
		$this->assertSame(201, $this->inner->getStatus());
		$this->assertSame(201, $this->outer->getStatus());
		$this->assertSame('Created', $this->inner->getStatusText());
		$this->assertSame('Created', $this->outer->getStatusText());
	}

	public function testToString()
	{
		$this->inner->setStatus(201);
		$this->inner->setBody('foo');
		$this->inner->setHeader('foo', 'bar');

		$this->assertSame((string) $this->inner, (string) $this->outer);
	}
}
