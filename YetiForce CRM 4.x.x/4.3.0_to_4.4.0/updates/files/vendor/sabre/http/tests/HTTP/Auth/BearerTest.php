<?php

namespace Sabre\HTTP\Auth;

use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

class BearerTest extends \PHPUnit_Framework_TestCase
{
	public function testGetToken()
	{
		$request = new Request('GET', '/', [
			'Authorization' => 'Bearer 12345'
		]);

		$bearer = new Bearer('Dagger', $request, new Response());

		$this->assertSame(
			'12345',
			$bearer->getToken()
		);
	}

	public function testGetCredentialsNoheader()
	{
		$request = new Request('GET', '/', []);
		$bearer = new Bearer('Dagger', $request, new Response());

		$this->assertNull($bearer->getToken());
	}

	public function testGetCredentialsNotBearer()
	{
		$request = new Request('GET', '/', [
			'Authorization' => 'QBearer 12345'
		]);
		$bearer = new Bearer('Dagger', $request, new Response());

		$this->assertNull($bearer->getToken());
	}

	public function testRequireLogin()
	{
		$response = new Response();
		$bearer = new Bearer('Dagger', new Request(), $response);

		$bearer->requireLogin();

		$this->assertSame('Bearer realm="Dagger"', $response->getHeader('WWW-Authenticate'));
		$this->assertSame(401, $response->getStatus());
	}
}
