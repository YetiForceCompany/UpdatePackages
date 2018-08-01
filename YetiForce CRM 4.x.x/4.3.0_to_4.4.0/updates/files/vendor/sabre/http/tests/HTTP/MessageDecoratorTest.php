<?php

namespace Sabre\HTTP;

class MessageDecoratorTest extends \PHPUnit_Framework_TestCase
{
	protected $inner;
	protected $outer;

	public function setUp()
	{
		$this->inner = new Request();
		$this->outer = new RequestDecorator($this->inner);
	}

	public function testBody()
	{
		$this->outer->setBody('foo');
		$this->assertSame('foo', stream_get_contents($this->inner->getBodyAsStream()));
		$this->assertSame('foo', stream_get_contents($this->outer->getBodyAsStream()));
		$this->assertSame('foo', $this->inner->getBodyAsString());
		$this->assertSame('foo', $this->outer->getBodyAsString());
		$this->assertSame('foo', $this->inner->getBody());
		$this->assertSame('foo', $this->outer->getBody());
	}

	public function testHeaders()
	{
		$this->outer->setHeaders([
			'a' => 'b',
			]);

		$this->assertSame(['a' => ['b']], $this->inner->getHeaders());
		$this->assertSame(['a' => ['b']], $this->outer->getHeaders());

		$this->outer->setHeaders([
			'c' => 'd',
		]);

		$this->assertSame(['a' => ['b'], 'c' => ['d']], $this->inner->getHeaders());
		$this->assertSame(['a' => ['b'], 'c' => ['d']], $this->outer->getHeaders());

		$this->outer->addHeaders([
			'e' => 'f',
			]);

		$this->assertSame(['a' => ['b'], 'c' => ['d'], 'e' => ['f']], $this->inner->getHeaders());
		$this->assertSame(['a' => ['b'], 'c' => ['d'], 'e' => ['f']], $this->outer->getHeaders());
	}

	public function testHeader()
	{
		$this->assertFalse($this->outer->hasHeader('a'));
		$this->assertFalse($this->inner->hasHeader('a'));
		$this->outer->setHeader('a', 'c');
		$this->assertTrue($this->outer->hasHeader('a'));
		$this->assertTrue($this->inner->hasHeader('a'));

		$this->assertSame('c', $this->inner->getHeader('A'));
		$this->assertSame('c', $this->outer->getHeader('A'));

		$this->outer->addHeader('A', 'd');

		$this->assertSame(
			['c', 'd'],
			$this->inner->getHeaderAsArray('A')
		);
		$this->assertSame(
			['c', 'd'],
			$this->outer->getHeaderAsArray('A')
		);

		$success = $this->outer->removeHeader('a');

		$this->assertTrue($success);
		$this->assertNull($this->inner->getHeader('A'));
		$this->assertNull($this->outer->getHeader('A'));

		$this->assertFalse($this->outer->removeHeader('i-dont-exist'));
	}

	public function testHttpVersion()
	{
		$this->outer->setHttpVersion('1.0');

		$this->assertSame('1.0', $this->inner->getHttpVersion());
		$this->assertSame('1.0', $this->outer->getHttpVersion());
	}
}
