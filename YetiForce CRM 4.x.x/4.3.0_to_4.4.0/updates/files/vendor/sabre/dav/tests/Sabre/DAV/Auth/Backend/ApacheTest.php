<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\HTTP;

class ApacheTest extends \PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		$backend = new Apache();
		$this->assertInstanceOf('Sabre\DAV\Auth\Backend\Apache', $backend);
	}

	public function testNoHeader()
	{
		$request = new HTTP\Request();
		$response = new HTTP\Response();
		$backend = new Apache();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);
	}

	public function testRemoteUser()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REMOTE_USER' => 'username',
		]);
		$response = new HTTP\Response();
		$backend = new Apache();

		$this->assertSame(
			[true, 'principals/username'],
			$backend->check($request, $response)
		);
	}

	public function testRedirectRemoteUser()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REDIRECT_REMOTE_USER' => 'username',
		]);
		$response = new HTTP\Response();
		$backend = new Apache();

		$this->assertSame(
			[true, 'principals/username'],
			$backend->check($request, $response)
		);
	}

	public function testRequireAuth()
	{
		$request = new HTTP\Request();
		$response = new HTTP\Response();

		$backend = new Apache();
		$backend->challenge($request, $response);

		$this->assertNull(
			$response->getHeader('WWW-Authenticate')
		);
	}
}
