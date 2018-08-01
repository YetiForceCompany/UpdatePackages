<?php

abstract class RequestsTest_Transport_Base extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$callback = [$this->transport, 'test'];
		$supported = call_user_func($callback);

		if (!$supported) {
			$this->markTestSkipped($this->transport . ' is not available');
			return;
		}

		$ssl_supported = call_user_func($callback, ['ssl' => true]);
		if (!$ssl_supported) {
			$this->skip_https = true;
		}
	}

	protected $skip_https = false;

	protected function getOptions($other = [])
	{
		$options = [
			'transport' => $this->transport
		];
		$options = array_merge($options, $other);
		return $options;
	}

	public function testResponseByteLimit()
	{
		$limit = 104;
		$options = [
			'max_bytes' => $limit,
		];
		$response = Requests::get(httpbin('/bytes/325'), [], $this->getOptions($options));
		$this->assertSame($limit, strlen($response->body));
	}

	public function testResponseByteLimitWithFile()
	{
		$limit = 300;
		$options = [
			'max_bytes' => $limit,
			'filename' => tempnam(sys_get_temp_dir(), 'RLT') // RequestsLibraryTest
		];
		$response = Requests::get(httpbin('/bytes/482'), [], $this->getOptions($options));
		$this->assertEmpty($response->body);
		$this->assertSame($limit, filesize($options['filename']));
		unlink($options['filename']);
	}

	public function testSimpleGET()
	{
		$request = Requests::get(httpbin('/get'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/get'), $result['url']);
		$this->assertEmpty($result['args']);
	}

	public function testGETWithArgs()
	{
		$request = Requests::get(httpbin('/get?test=true&test2=test'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/get?test=true&test2=test'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['args']);
	}

	public function testGETWithData()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::request(httpbin('/get'), [], $data, Requests::GET, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/get?test=true&test2=test'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['args']);
	}

	public function testGETWithNestedData()
	{
		$data = [
			'test' => 'true',
			'test2' => [
				'test3' => 'test',
				'test4' => 'test-too',
			],
		];
		$request = Requests::request(httpbin('/get'), [], $data, Requests::GET, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/get?test=true&test2%5Btest3%5D=test&test2%5Btest4%5D=test-too'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2[test3]' => 'test', 'test2[test4]' => 'test-too'], $result['args']);
	}

	public function testGETWithDataAndQuery()
	{
		$data = [
			'test2' => 'test',
		];
		$request = Requests::request(httpbin('/get?test=true'), [], $data, Requests::GET, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/get?test=true&test2=test'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['args']);
	}

	public function testGETWithHeaders()
	{
		$headers = [
			'Requested-At' => time(),
		];
		$request = Requests::get(httpbin('/get'), $headers, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame($headers['Requested-At'], $result['headers']['Requested-At']);
	}

	public function testChunked()
	{
		$request = Requests::get(httpbin('/stream/1'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/stream/1'), $result['url']);
		$this->assertEmpty($result['args']);
	}

	public function testHEAD()
	{
		$request = Requests::head(httpbin('/get'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
		$this->assertSame('', $request->body);
	}

	public function testTRACE()
	{
		$request = Requests::trace(httpbin('/trace'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
	}

	public function testRawPOST()
	{
		$data = 'test';
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame('test', $result['data']);
	}

	public function testFormPost()
	{
		$data = 'test=true&test2=test';
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testPOSTWithArray()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testPOSTWithNestedData()
	{
		$data = [
			'test' => 'true',
			'test2' => [
				'test3' => 'test',
				'test4' => 'test-too',
			],
		];
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2[test3]' => 'test', 'test2[test4]' => 'test-too'], $result['form']);
	}

	public function testRawPUT()
	{
		$data = 'test';
		$request = Requests::put(httpbin('/put'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame('test', $result['data']);
	}

	public function testFormPUT()
	{
		$data = 'test=true&test2=test';
		$request = Requests::put(httpbin('/put'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testPUTWithArray()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::put(httpbin('/put'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testRawPATCH()
	{
		$data = 'test';
		$request = Requests::patch(httpbin('/patch'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame('test', $result['data']);
	}

	public function testFormPATCH()
	{
		$data = 'test=true&test2=test';
		$request = Requests::patch(httpbin('/patch'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code, $request->body);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testPATCHWithArray()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::patch(httpbin('/patch'), [], $data, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testOPTIONS()
	{
		$request = Requests::options(httpbin('/options'), [], [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
	}

	public function testDELETE()
	{
		$request = Requests::delete(httpbin('/delete'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/delete'), $result['url']);
		$this->assertEmpty($result['args']);
	}

	public function testDELETEWithData()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::request(httpbin('/delete'), [], $data, Requests::DELETE, $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/delete?test=true&test2=test'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['args']);
	}

	public function testLOCK()
	{
		$request = Requests::request(httpbin('/lock'), [], [], 'LOCK', $this->getOptions());
		$this->assertSame(200, $request->status_code);
	}

	public function testLOCKWithData()
	{
		$data = [
			'test' => 'true',
			'test2' => 'test',
		];
		$request = Requests::request(httpbin('/lock'), [], $data, 'LOCK', $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}

	public function testRedirects()
	{
		$request = Requests::get(httpbin('/redirect/6'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$this->assertSame(6, $request->redirects);
	}

	public function testRelativeRedirects()
	{
		$request = Requests::get(httpbin('/relative-redirect/6'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$this->assertSame(6, $request->redirects);
	}

	/**
	 * @expectedException Requests_Exception
	 *
	 * @todo This should also check that the type is "toomanyredirects"
	 */
	public function testTooManyRedirects()
	{
		$options = [
			'redirects' => 10, // default, but force just in case
		];
		$request = Requests::get(httpbin('/redirect/11'), [], $this->getOptions($options));
	}

	public static function statusCodeSuccessProvider()
	{
		return [
			[200, true],
			[201, true],
			[202, true],
			[203, true],
			[204, true],
			[205, true],
			[206, true],
			[300, false],
			[301, false],
			[302, false],
			[303, false],
			[304, false],
			[305, false],
			[306, false],
			[307, false],
			[400, false],
			[401, false],
			[402, false],
			[403, false],
			[404, false],
			[405, false],
			[406, false],
			[407, false],
			[408, false],
			[409, false],
			[410, false],
			[411, false],
			[412, false],
			[413, false],
			[414, false],
			[415, false],
			[416, false],
			[417, false],
			[418, false], // RFC 2324
			[428, false], // RFC 6585
			[429, false], // RFC 6585
			[431, false], // RFC 6585
			[500, false],
			[501, false],
			[502, false],
			[503, false],
			[504, false],
			[505, false],
			[511, false], // RFC 6585
		];
	}

	/**
	 * @dataProvider statusCodeSuccessProvider
	 */
	public function testStatusCode($code, $success)
	{
		$transport = new MockTransport();
		$transport->code = $code;

		$url = sprintf(httpbin('/status/%d'), $code);

		$options = [
			'follow_redirects' => false,
			'transport' => $transport,
		];
		$request = Requests::get($url, [], $options);
		$this->assertSame($code, $request->status_code);
		$this->assertSame($success, $request->success);
	}

	/**
	 * @dataProvider statusCodeSuccessProvider
	 */
	public function testStatusCodeThrow($code, $success)
	{
		$transport = new MockTransport();
		$transport->code = $code;

		$url = sprintf(httpbin('/status/%d'), $code);
		$options = [
			'follow_redirects' => false,
			'transport' => $transport,
		];

		if (!$success) {
			if ($code >= 400) {
				$this->setExpectedException('Requests_Exception_HTTP_' . $code, '', $code);
			} elseif ($code >= 300 && $code < 400) {
				$this->setExpectedException('Requests_Exception');
			}
		}
		$request = Requests::get($url, [], $options);
		$request->throw_for_status(false);
	}

	/**
	 * @dataProvider statusCodeSuccessProvider
	 */
	public function testStatusCodeThrowAllowRedirects($code, $success)
	{
		$transport = new MockTransport();
		$transport->code = $code;

		$url = sprintf(httpbin('/status/%d'), $code);
		$options = [
			'follow_redirects' => false,
			'transport' => $transport,
		];

		if (!$success) {
			if ($code >= 400 || $code === 304 || $code === 305 || $code === 306) {
				$this->setExpectedException('Requests_Exception_HTTP_' . $code, '', $code);
			}
		}
		$request = Requests::get($url, [], $options);
		$request->throw_for_status(true);
	}

	public function testStatusCodeUnknown()
	{
		$transport = new MockTransport();
		$transport->code = 599;

		$options = [
			'transport' => $transport,
		];

		$request = Requests::get(httpbin('/status/599'), [], $options);
		$this->assertSame(599, $request->status_code);
		$this->assertSame(false, $request->success);
	}

	/**
	 * @expectedException Requests_Exception_HTTP_Unknown
	 */
	public function testStatusCodeThrowUnknown()
	{
		$transport = new MockTransport();
		$transport->code = 599;

		$options = [
			'transport' => $transport,
		];

		$request = Requests::get(httpbin('/status/599'), [], $options);
		$request->throw_for_status(true);
	}

	public function testGzipped()
	{
		$request = Requests::get(httpbin('/gzip'), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertSame(true, $result->gzipped);
	}

	public function testStreamToFile()
	{
		$options = [
			'filename' => tempnam(sys_get_temp_dir(), 'RLT') // RequestsLibraryTest
		];
		$request = Requests::get(httpbin('/get'), [], $this->getOptions($options));
		$this->assertSame(200, $request->status_code);
		$this->assertEmpty($request->body);

		$contents = file_get_contents($options['filename']);
		$result = json_decode($contents, true);
		$this->assertSame(httpbin('/get'), $result['url']);
		$this->assertEmpty($result['args']);

		unlink($options['filename']);
	}

	public function testNonblocking()
	{
		$options = [
			'blocking' => false
		];
		$request = Requests::get(httpbin('/get'), [], $this->getOptions($options));
		$empty = new Requests_Response();
		$this->assertSame($empty, $request);
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testBadIP()
	{
		$request = Requests::get('http://256.256.256.0/', [], $this->getOptions());
	}

	public function testHTTPS()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::get(httpbin('/get', true), [], $this->getOptions());
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		// Disable, since httpbin always returns http
		// $this->assertEquals(httpbin('/get', true), $result['url']);
		$this->assertEmpty($result['args']);
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testExpiredHTTPS()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::get('https://testssl-expire.disig.sk/index.en.html', [], $this->getOptions());
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testRevokedHTTPS()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::get('https://testssl-revoked.disig.sk/index.en.html', [], $this->getOptions());
	}

	/**
	 * Test that SSL fails with a bad certificate.
	 *
	 * @expectedException Requests_Exception
	 */
	public function testBadDomain()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::head('https://wrong.host.badssl.com/', [], $this->getOptions());
	}

	/**
	 * Test that the transport supports Server Name Indication with HTTPS.
	 *
	 * badssl.com is used for SSL testing, and the common name is set to
	 * `*.badssl.com` as such. Without alternate name support, this will fail
	 * as `badssl.com` is only in the alternate name
	 */
	public function testAlternateNameSupport()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::head('https://badssl.com/', [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
	}

	/**
	 * Test that the transport supports Server Name Indication with HTTPS.
	 *
	 * feelingrestful.com (owned by hmn.md and used with permission) points to
	 * CloudFlare, and will fail if SNI isn't sent.
	 */
	public function testSNISupport()
	{
		if ($this->skip_https) {
			$this->markTestSkipped('SSL support is not available.');
			return;
		}

		$request = Requests::head('https://feelingrestful.com/', [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testTimeout()
	{
		$options = [
			'timeout' => 1,
		];
		$request = Requests::get(httpbin('/delay/10'), [], $this->getOptions($options));
		var_dump($request);
	}

	public function testMultiple()
	{
		$requests = [
			'test1' => [
				'url' => httpbin('/get')
			],
			'test2' => [
				'url' => httpbin('/get')
			],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions());

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

	public function testMultipleWithDifferingMethods()
	{
		$requests = [
			'get' => [
				'url' => httpbin('/get'),
			],
			'post' => [
				'url' => httpbin('/post'),
				'type' => Requests::POST,
				'data' => 'test',
			],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions());

		// get
		$this->assertSame(200, $responses['get']->status_code);

		// post
		$this->assertSame(200, $responses['post']->status_code);
		$result = json_decode($responses['post']->body, true);
		$this->assertSame('test', $result['data']);
	}

	/**
	 * @depends testTimeout
	 */
	public function testMultipleWithFailure()
	{
		$requests = [
			'success' => [
				'url' => httpbin('/get'),
			],
			'timeout' => [
				'url' => httpbin('/delay/10'),
				'options' => [
					'timeout' => 1,
				],
			],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions());
		$this->assertSame(200, $responses['success']->status_code);
		$this->assertInstanceOf('Requests_Exception', $responses['timeout']);
	}

	public function testMultipleUsingCallback()
	{
		$requests = [
			'get' => [
				'url' => httpbin('/get'),
			],
			'post' => [
				'url' => httpbin('/post'),
				'type' => Requests::POST,
				'data' => 'test',
			],
		];
		$this->completed = [];
		$options = [
			'complete' => [$this, 'completeCallback'],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions($options));

		$this->assertSame($this->completed, $responses);
		$this->completed = [];
	}

	public function testMultipleUsingCallbackAndFailure()
	{
		$requests = [
			'success' => [
				'url' => httpbin('/get'),
			],
			'timeout' => [
				'url' => httpbin('/delay/10'),
				'options' => [
					'timeout' => 1,
				],
			],
		];
		$this->completed = [];
		$options = [
			'complete' => [$this, 'completeCallback'],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions($options));

		$this->assertSame($this->completed, $responses);
		$this->completed = [];
	}

	public function completeCallback($response, $key)
	{
		$this->completed[$key] = $response;
	}

	public function testMultipleToFile()
	{
		$requests = [
			'get' => [
				'url' => httpbin('/get'),
				'options' => [
					'filename' => tempnam(sys_get_temp_dir(), 'RLT') // RequestsLibraryTest
				],
			],
			'post' => [
				'url' => httpbin('/post'),
				'type' => Requests::POST,
				'data' => 'test',
				'options' => [
					'filename' => tempnam(sys_get_temp_dir(), 'RLT') // RequestsLibraryTest
				],
			],
		];
		$responses = Requests::request_multiple($requests, $this->getOptions());

		// GET request
		$contents = file_get_contents($requests['get']['options']['filename']);
		$result = json_decode($contents, true);
		$this->assertSame(httpbin('/get'), $result['url']);
		$this->assertEmpty($result['args']);
		unlink($requests['get']['options']['filename']);

		// POST request
		$contents = file_get_contents($requests['post']['options']['filename']);
		$result = json_decode($contents, true);
		$this->assertSame(httpbin('/post'), $result['url']);
		$this->assertSame('test', $result['data']);
		unlink($requests['post']['options']['filename']);
	}

	public function testAlternatePort()
	{
		$request = Requests::get('http://portquiz.net:8080/', [], $this->getOptions());
		$this->assertSame(200, $request->status_code);
		$num = preg_match('#You have reached this page on port <b>(\d+)</b>#i', $request->body, $matches);
		$this->assertSame(1, $num, 'Response should contain the port number');
		$this->assertSame(8080, $matches[1]);
	}

	public function testProgressCallback()
	{
		$mock = $this->getMockBuilder('stdClass')->setMethods(['progress'])->getMock();
		$mock->expects($this->atLeastOnce())->method('progress');
		$hooks = new Requests_Hooks();
		$hooks->register('request.progress', [$mock, 'progress']);
		$options = [
			'hooks' => $hooks,
		];
		$options = $this->getOptions($options);

		$response = Requests::get(httpbin('/get'), [], $options);
	}

	public function testAfterRequestCallback()
	{
		$mock = $this->getMockBuilder('stdClass')
			->setMethods(['after_request'])
			->getMock();

		$mock->expects($this->atLeastOnce())
			->method('after_request')
			->with(
				$this->isType('string'),
				$this->logicalAnd($this->isType('array'), $this->logicalNot($this->isEmpty()))
			);
		$hooks = new Requests_Hooks();
		$hooks->register('curl.after_request', [$mock, 'after_request']);
		$hooks->register('fsockopen.after_request', [$mock, 'after_request']);
		$options = [
			'hooks' => $hooks,
		];
		$options = $this->getOptions($options);

		$response = Requests::get(httpbin('/get'), [], $options);
	}

	public function testReusableTransport()
	{
		$options = $this->getOptions(['transport' => new $this->transport()]);

		$request1 = Requests::get(httpbin('/get'), [], $options);
		$request2 = Requests::get(httpbin('/get'), [], $options);

		$this->assertSame(200, $request1->status_code);
		$this->assertSame(200, $request2->status_code);

		$result1 = json_decode($request1->body, true);
		$result2 = json_decode($request2->body, true);

		$this->assertSame(httpbin('/get'), $result1['url']);
		$this->assertSame(httpbin('/get'), $result2['url']);

		$this->assertEmpty($result1['args']);
		$this->assertEmpty($result2['args']);
	}

	public function testQueryDataFormat()
	{
		$data = ['test' => 'true', 'test2' => 'test'];
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions(['data_format' => 'query']));
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/post') . '?test=true&test2=test', $result['url']);
		$this->assertSame('', $result['data']);
	}

	public function testBodyDataFormat()
	{
		$data = ['test' => 'true', 'test2' => 'test'];
		$request = Requests::post(httpbin('/post'), [], $data, $this->getOptions(['data_format' => 'body']));
		$this->assertSame(200, $request->status_code);

		$result = json_decode($request->body, true);
		$this->assertSame(httpbin('/post'), $result['url']);
		$this->assertSame(['test' => 'true', 'test2' => 'test'], $result['form']);
	}
}
