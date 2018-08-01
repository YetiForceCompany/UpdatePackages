<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\HTTP;

require_once 'Sabre/HTTP/ResponseMock.php';

class AbstractBearerTest extends \PHPUnit_Framework_TestCase
{
	public function testCheckNoHeaders()
	{
		$request = new HTTP\Request();
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);
	}

	public function testCheckInvalidToken()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'HTTP_AUTHORIZATION' => 'Bearer foo',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();

		$this->assertFalse(
			$backend->check($request, $response)[0]
		);
	}

	public function testCheckSuccess()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'HTTP_AUTHORIZATION' => 'Bearer valid',
		]);
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();
		$this->assertSame(
			[true, 'principals/username'],
			$backend->check($request, $response)
		);
	}

	public function testRequireAuth()
	{
		$request = new HTTP\Request();
		$response = new HTTP\Response();

		$backend = new AbstractBearerMock();
		$backend->setRealm('writing unittests on a saturday night');
		$backend->challenge($request, $response);

		$this->assertSame(
			'Bearer realm="writing unittests on a saturday night"',
			$response->getHeader('WWW-Authenticate')
		);
	}
}

class AbstractBearerMock extends AbstractBearer
{
	/**
	 * Validates a bearer token.
	 *
	 * This method should return true or false depending on if login
	 * succeeded.
	 *
	 * @param string $bearerToken
	 *
	 * @return bool
	 */
	public function validateBearerToken($bearerToken)
	{
		return 'valid' === $bearerToken ? 'principals/username' : false;
	}
}
