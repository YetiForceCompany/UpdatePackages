<?php

class RequestsTest_Session extends PHPUnit_Framework_TestCase
{
	public function testURLResolution()
	{
		$session = new Requests_Session(httpbin('/'));

		// Set the cookies up
		$response = $session->get('/get');
		$this->assertTrue($response->success);
		$this->assertSame(httpbin('/get'), $response->url);

		$data = json_decode($response->body, true);
		$this->assertNotNull($data);
		$this->assertArrayHasKey('url', $data);
		$this->assertSame(httpbin('/get'), $data['url']);
	}

	public function testBasicGET()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicGET',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->get('/get', ['X-Requests-Request' => 'GET']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);

		$data = json_decode($response->body, true);
		$this->assertArrayHasKey('X-Requests-Session', $data['headers']);
		$this->assertSame('BasicGET', $data['headers']['X-Requests-Session']);
		$this->assertArrayHasKey('X-Requests-Request', $data['headers']);
		$this->assertSame('GET', $data['headers']['X-Requests-Request']);
	}

	public function testBasicHEAD()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicHEAD',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->head('/get', ['X-Requests-Request' => 'HEAD']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);
	}

	public function testBasicDELETE()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicDELETE',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->delete('/delete', ['X-Requests-Request' => 'DELETE']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);

		$data = json_decode($response->body, true);
		$this->assertArrayHasKey('X-Requests-Session', $data['headers']);
		$this->assertSame('BasicDELETE', $data['headers']['X-Requests-Session']);
		$this->assertArrayHasKey('X-Requests-Request', $data['headers']);
		$this->assertSame('DELETE', $data['headers']['X-Requests-Request']);
	}

	public function testBasicPOST()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicPOST',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->post('/post', ['X-Requests-Request' => 'POST'], ['postdata' => 'exists']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);

		$data = json_decode($response->body, true);
		$this->assertArrayHasKey('X-Requests-Session', $data['headers']);
		$this->assertSame('BasicPOST', $data['headers']['X-Requests-Session']);
		$this->assertArrayHasKey('X-Requests-Request', $data['headers']);
		$this->assertSame('POST', $data['headers']['X-Requests-Request']);
	}

	public function testBasicPUT()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicPUT',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->put('/put', ['X-Requests-Request' => 'PUT'], ['postdata' => 'exists']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);

		$data = json_decode($response->body, true);
		$this->assertArrayHasKey('X-Requests-Session', $data['headers']);
		$this->assertSame('BasicPUT', $data['headers']['X-Requests-Session']);
		$this->assertArrayHasKey('X-Requests-Request', $data['headers']);
		$this->assertSame('PUT', $data['headers']['X-Requests-Request']);
	}

	public function testBasicPATCH()
	{
		$session_headers = [
			'X-Requests-Session' => 'BasicPATCH',
			'X-Requests-Request' => 'notset',
		];
		$session = new Requests_Session(httpbin('/'), $session_headers);
		$response = $session->patch('/patch', ['X-Requests-Request' => 'PATCH'], ['postdata' => 'exists']);
		$response->throw_for_status(false);
		$this->assertSame(200, $response->status_code);

		$data = json_decode($response->body, true);
		$this->assertArrayHasKey('X-Requests-Session', $data['headers']);
		$this->assertSame('BasicPATCH', $data['headers']['X-Requests-Session']);
		$this->assertArrayHasKey('X-Requests-Request', $data['headers']);
		$this->assertSame('PATCH', $data['headers']['X-Requests-Request']);
	}

	public function testMultiple()
	{
		$session = new Requests_Session(httpbin('/'), ['X-Requests-Session' => 'Multiple']);
		$requests = [
			'test1' => [
				'url' => httpbin('/get')
			],
			'test2' => [
				'url' => httpbin('/get')
			],
		];
		$responses = $session->request_multiple($requests);

		// test1
		$this->assertNotEmpty($responses['test1']);
		$this->assertInstanceOf('Requests_Response', $responses['test1']);
		$this->assertSame(200, $responses['test1']->status_code);

		$result = json_decode($responses['test1']->body, true);
		$this->assertSame(httpbin('/get'), $result['url']);
		$this->assertEmpty($result['args']);

		// test2
		$this->assertNotEmpty($responses['test2']);
		$this->assertInstanceOf('Requests_Response', $responses['test2']);
		$this->assertSame(200, $responses['test2']->status_code);

		$result = json_decode($responses['test2']->body, true);
		$this->assertSame(httpbin('/get'), $result['url']);
		$this->assertEmpty($result['args']);
	}

	public function testPropertyUsage()
	{
		$headers = [
			'X-TestHeader' => 'testing',
			'X-TestHeader2' => 'requests-test'
		];
		$data = [
			'testdata' => 'value1',
			'test2' => 'value2',
			'test3' => [
				'foo' => 'bar',
				'abc' => 'xyz'
			]
		];
		$options = [
			'testoption' => 'test',
			'foo' => 'bar'
		];

		$session = new Requests_Session('http://example.com/', $headers, $data, $options);
		$this->assertSame('http://example.com/', $session->url);
		$this->assertSame($headers, $session->headers);
		$this->assertSame($data, $session->data);
		$this->assertSame($options['testoption'], $session->options['testoption']);

		// Test via property access
		$this->assertSame($options['testoption'], $session->testoption);

		// Test setting new property
		$session->newoption = 'foobar';
		$options['newoption'] = 'foobar';
		$this->assertSame($options['newoption'], $session->options['newoption']);

		// Test unsetting property
		unset($session->newoption);
		$this->assertFalse(isset($session->newoption));

		// Update property
		$session->testoption = 'foobar';
		$options['testoption'] = 'foobar';
		$this->assertSame($options['testoption'], $session->testoption);

		// Test getting invalid property
		$this->assertNull($session->invalidoption);
	}

	public function testSharedCookies()
	{
		$session = new Requests_Session(httpbin('/'));

		$options = [
			'follow_redirects' => false
		];
		$response = $session->get('/cookies/set?requests-testcookie=testvalue', [], $options);
		$this->assertSame(302, $response->status_code);

		// Check the cookies
		$response = $session->get('/cookies');
		$this->assertTrue($response->success);

		// Check the response
		$data = json_decode($response->body, true);
		$this->assertNotNull($data);
		$this->assertArrayHasKey('cookies', $data);

		$cookies = [
			'requests-testcookie' => 'testvalue'
		];
		$this->assertSame($cookies, $data['cookies']);
	}
}
