<?php

class RequestsTest_Proxy_HTTP extends PHPUnit_Framework_TestCase
{
	protected function checkProxyAvailable($type = '')
	{
		switch ($type) {
			case 'auth':
				$has_proxy = defined('REQUESTS_HTTP_PROXY_AUTH') && REQUESTS_HTTP_PROXY_AUTH;
				break;
			default:
				$has_proxy = defined('REQUESTS_HTTP_PROXY') && REQUESTS_HTTP_PROXY;
				break;
		}

		if (!$has_proxy) {
			$this->markTestSkipped('Proxy not available');
		}
	}

	public function transportProvider()
	{
		return [
			['Requests_Transport_cURL'],
			['Requests_Transport_fsockopen'],
		];
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testConnectWithString($transport)
	{
		$this->checkProxyAvailable();

		$options = [
			'proxy' => REQUESTS_HTTP_PROXY,
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
		$this->assertSame('http', $response->headers['x-requests-proxied']);

		$data = json_decode($response->body, true);
		$this->assertSame('http', $data['headers']['x-requests-proxy']);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testConnectWithArray($transport)
	{
		$this->checkProxyAvailable();

		$options = [
			'proxy' => [REQUESTS_HTTP_PROXY],
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
		$this->assertSame('http', $response->headers['x-requests-proxied']);

		$data = json_decode($response->body, true);
		$this->assertSame('http', $data['headers']['x-requests-proxy']);
	}

	/**
	 * @dataProvider transportProvider
	 * @expectedException Requests_Exception
	 */
	public function testConnectInvalidParameters($transport)
	{
		$this->checkProxyAvailable();

		$options = [
			'proxy' => [REQUESTS_HTTP_PROXY, 'testuser', 'password', 'something'],
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testConnectWithInstance($transport)
	{
		$this->checkProxyAvailable();

		$options = [
			'proxy' => new Requests_Proxy_HTTP(REQUESTS_HTTP_PROXY),
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
		$this->assertSame('http', $response->headers['x-requests-proxied']);

		$data = json_decode($response->body, true);
		$this->assertSame('http', $data['headers']['x-requests-proxy']);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testConnectWithAuth($transport)
	{
		$this->checkProxyAvailable('auth');

		$options = [
			'proxy' => [
				REQUESTS_HTTP_PROXY_AUTH,
				REQUESTS_HTTP_PROXY_AUTH_USER,
				REQUESTS_HTTP_PROXY_AUTH_PASS
			],
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
		$this->assertSame(200, $response->status_code);
		$this->assertSame('http', $response->headers['x-requests-proxied']);

		$data = json_decode($response->body, true);
		$this->assertSame('http', $data['headers']['x-requests-proxy']);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testConnectWithInvalidAuth($transport)
	{
		$this->checkProxyAvailable('auth');

		$options = [
			'proxy' => [
				REQUESTS_HTTP_PROXY_AUTH,
				REQUESTS_HTTP_PROXY_AUTH_USER . '!',
				REQUESTS_HTTP_PROXY_AUTH_PASS . '!'
			],
			'transport' => $transport,
		];
		$response = Requests::get(httpbin('/get'), [], $options);
		$this->assertSame(407, $response->status_code);
	}
}
