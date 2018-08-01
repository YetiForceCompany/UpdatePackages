<?php

namespace Sabre\HTTP;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		$response = new Response(200, ['Content-Type' => 'text/xml']);
		$this->assertSame(200, $response->getStatus());
		$this->assertSame('OK', $response->getStatusText());
	}

	public function testSetStatus()
	{
		$response = new Response();
		$response->setStatus('402 Where\'s my money?');
		$this->assertSame(402, $response->getStatus());
		$this->assertSame('Where\'s my money?', $response->getStatusText());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidStatus()
	{
		$response = new Response(1000);
	}

	public function testToString()
	{
		$response = new Response(200, ['Content-Type' => 'text/xml']);
		$response->setBody('foo');

		$expected = <<<HI
HTTP/1.1 200 OK\r
Content-Type: text/xml\r
\r
foo
HI;
		$this->assertSame($expected, (string) $response);
	}
}
