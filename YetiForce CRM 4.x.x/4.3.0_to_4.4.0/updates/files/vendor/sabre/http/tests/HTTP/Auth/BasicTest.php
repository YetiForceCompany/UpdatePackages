<?php

namespace Sabre\HTTP\Auth;

use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

class BasicTest extends \PHPUnit_Framework_TestCase
{
	public function testGetCredentials()
	{
		$request = new Request('GET', '/', [
			'Authorization' => 'Basic ' . base64_encode('user:pass:bla')
		]);

		$basic = new Basic('Dagger', $request, new Response());

		$this->assertSame([
			'user',
			'pass:bla',
		], $basic->getCredentials());
	}

	public function testGetInvalidCredentialsColonMissing()
	{
		$request = new Request('GET', '/', [
			'Authorization' => 'Basic ' . base64_encode('userpass')
		]);

		$basic = new Basic('Dagger', $request, new Response());

		$this->assertNull($basic->getCredentials());
	}

	public function testGetCredentialsNoheader()
	{
		$request = new Request('GET', '/', []);
		$basic = new Basic('Dagger', $request, new Response());

		$this->assertNull($basic->getCredentials());
	}

	public function testGetCredentialsNotBasic()
	{
		$request = new Request('GET', '/', [
			'Authorization' => 'QBasic ' . base64_encode('user:pass:bla')
		]);
		$basic = new Basic('Dagger', $request, new Response());

		$this->assertNull($basic->getCredentials());
	}

	public function testRequireLogin()
	{
		$response = new Response();
		$basic = new Basic('Dagger', new Request(), $response);

		$basic->requireLogin();

		$this->assertSame('Basic realm="Dagger", charset="UTF-8"', $response->getHeader('WWW-Authenticate'));
		$this->assertSame(401, $response->getStatus());
	}
}
