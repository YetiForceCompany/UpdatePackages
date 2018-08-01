<?php

namespace Sabre\HTTP;

class RequestDecoratorTest extends \PHPUnit_Framework_TestCase
{
	protected $inner;
	protected $outer;

	public function setUp()
	{
		$this->inner = new Request();
		$this->outer = new RequestDecorator($this->inner);
	}

	public function testMethod()
	{
		$this->outer->setMethod('FOO');
		$this->assertSame('FOO', $this->inner->getMethod());
		$this->assertSame('FOO', $this->outer->getMethod());
	}

	public function testUrl()
	{
		$this->outer->setUrl('/foo');
		$this->assertSame('/foo', $this->inner->getUrl());
		$this->assertSame('/foo', $this->outer->getUrl());
	}

	public function testAbsoluteUrl()
	{
		$this->outer->setAbsoluteUrl('http://example.org/foo');
		$this->assertSame('http://example.org/foo', $this->inner->getAbsoluteUrl());
		$this->assertSame('http://example.org/foo', $this->outer->getAbsoluteUrl());
	}

	public function testBaseUrl()
	{
		$this->outer->setBaseUrl('/foo');
		$this->assertSame('/foo', $this->inner->getBaseUrl());
		$this->assertSame('/foo', $this->outer->getBaseUrl());
	}

	public function testPath()
	{
		$this->outer->setBaseUrl('/foo');
		$this->outer->setUrl('/foo/bar');
		$this->assertSame('bar', $this->inner->getPath());
		$this->assertSame('bar', $this->outer->getPath());
	}

	public function testQueryParams()
	{
		$this->outer->setUrl('/foo?a=b&c=d&e');
		$expected = [
			'a' => 'b',
			'c' => 'd',
			'e' => null,
		];

		$this->assertSame($expected, $this->inner->getQueryParameters());
		$this->assertSame($expected, $this->outer->getQueryParameters());
	}

	public function testPostData()
	{
		$postData = [
			'a' => 'b',
			'c' => 'd',
			'e' => null,
		];

		$this->outer->setPostData($postData);
		$this->assertSame($postData, $this->inner->getPostData());
		$this->assertSame($postData, $this->outer->getPostData());
	}

	public function testServerData()
	{
		$serverData = [
			'HTTPS' => 'On',
		];

		$this->outer->setRawServerData($serverData);
		$this->assertSame('On', $this->inner->getRawServerValue('HTTPS'));
		$this->assertSame('On', $this->outer->getRawServerValue('HTTPS'));

		$this->assertNull($this->inner->getRawServerValue('FOO'));
		$this->assertNull($this->outer->getRawServerValue('FOO'));
	}

	public function testToString()
	{
		$this->inner->setMethod('POST');
		$this->inner->setUrl('/foo/bar/');
		$this->inner->setBody('foo');
		$this->inner->setHeader('foo', 'bar');

		$this->assertSame((string) $this->inner, (string) $this->outer);
	}
}
