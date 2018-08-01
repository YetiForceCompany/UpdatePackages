<?php

class RequestsTest_Auth_Basic extends PHPUnit_Framework_TestCase
{
	public static function transportProvider()
	{
		$transports = [
			['Requests_Transport_fsockopen'],
			['Requests_Transport_cURL'],
		];
		return $transports;
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingArray($transport)
	{
		if (!call_user_func([$transport, 'test'])) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = [
			'auth' => ['user', 'passwd'],
			'transport' => $transport,
		];
		$request = Requests::get(httpbin('/basic-auth/user/passwd'), [], $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertSame(true, $result->authenticated);
		$this->assertSame('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingInstantiation($transport)
	{
		if (!call_user_func([$transport, 'test'])) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = [
			'auth' => new Requests_Auth_Basic(['user', 'passwd']),
			'transport' => $transport,
		];
		$request = Requests::get(httpbin('/basic-auth/user/passwd'), [], $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertSame(true, $result->authenticated);
		$this->assertSame('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testPOSTUsingInstantiation($transport)
	{
		if (!call_user_func([$transport, 'test'])) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = [
			'auth' => new Requests_Auth_Basic(['user', 'passwd']),
			'transport' => $transport,
		];
		$data = 'test';
		$request = Requests::post(httpbin('/post'), [], $data, $options);
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);

		$auth = $result->headers->Authorization;
		$auth = explode(' ', $auth);

		$this->assertSame(base64_encode('user:passwd'), $auth[1]);
		$this->assertSame('test', $result->data);
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testMissingPassword()
	{
		$auth = new Requests_Auth_Basic(['user']);
	}
}
