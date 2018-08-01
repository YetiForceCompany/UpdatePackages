<?php

namespace Sabre\DAV;

use Sabre\HTTP\Response;

require_once 'Sabre/DAV/ClientMock.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if (!function_exists('curl_init')) {
			$this->markTestSkipped('CURL must be installed to test the client');
		}
	}

	public function testConstruct()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);
		$this->assertInstanceOf('Sabre\DAV\ClientMock', $client);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testConstructNoBaseUri()
	{
		$client = new ClientMock([]);
	}

	public function testAuth()
	{
		$client = new ClientMock([
			'baseUri'  => '/',
			'userName' => 'foo',
			'password' => 'bar',
		]);

		$this->assertSame('foo:bar', $client->curlSettings[CURLOPT_USERPWD]);
		$this->assertSame(CURLAUTH_BASIC | CURLAUTH_DIGEST, $client->curlSettings[CURLOPT_HTTPAUTH]);
	}

	public function testBasicAuth()
	{
		$client = new ClientMock([
			'baseUri'  => '/',
			'userName' => 'foo',
			'password' => 'bar',
			'authType' => Client::AUTH_BASIC
		]);

		$this->assertSame('foo:bar', $client->curlSettings[CURLOPT_USERPWD]);
		$this->assertSame(CURLAUTH_BASIC, $client->curlSettings[CURLOPT_HTTPAUTH]);
	}

	public function testDigestAuth()
	{
		$client = new ClientMock([
			'baseUri'  => '/',
			'userName' => 'foo',
			'password' => 'bar',
			'authType' => Client::AUTH_DIGEST
		]);

		$this->assertSame('foo:bar', $client->curlSettings[CURLOPT_USERPWD]);
		$this->assertSame(CURLAUTH_DIGEST, $client->curlSettings[CURLOPT_HTTPAUTH]);
	}

	public function testNTLMAuth()
	{
		$client = new ClientMock([
			'baseUri'  => '/',
			'userName' => 'foo',
			'password' => 'bar',
			'authType' => Client::AUTH_NTLM
		]);

		$this->assertSame('foo:bar', $client->curlSettings[CURLOPT_USERPWD]);
		$this->assertSame(CURLAUTH_NTLM, $client->curlSettings[CURLOPT_HTTPAUTH]);
	}

	public function testProxy()
	{
		$client = new ClientMock([
			'baseUri' => '/',
			'proxy'   => 'localhost:8888',
		]);

		$this->assertSame('localhost:8888', $client->curlSettings[CURLOPT_PROXY]);
	}

	public function testEncoding()
	{
		$client = new ClientMock([
			'baseUri'  => '/',
			'encoding' => Client::ENCODING_IDENTITY | Client::ENCODING_GZIP | Client::ENCODING_DEFLATE,
		]);

		$this->assertSame('identity,deflate,gzip', $client->curlSettings[CURLOPT_ENCODING]);
	}

	public function testPropFind()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$responseBody = <<<XML
<?xml version="1.0"?>
<multistatus xmlns="DAV:">
  <response>
    <href>/foo</href>
    <propstat>
      <prop>
        <displayname>bar</displayname>
      </prop>
      <status>HTTP/1.1 200 OK</status>
    </propstat>
  </response>
</multistatus>
XML;

		$client->response = new Response(207, [], $responseBody);
		$result = $client->propFind('foo', ['{DAV:}displayname', '{urn:zim}gir']);

		$this->assertSame(['{DAV:}displayname' => 'bar'], $result);

		$request = $client->request;
		$this->assertSame('PROPFIND', $request->getMethod());
		$this->assertSame('/foo', $request->getUrl());
		$this->assertSame([
			'Depth'        => ['0'],
			'Content-Type' => ['application/xml'],
		], $request->getHeaders());
	}

	/**
	 * @expectedException \Sabre\HTTP\ClientHttpException
	 */
	public function testPropFindError()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$client->response = new Response(405, []);
		$client->propFind('foo', ['{DAV:}displayname', '{urn:zim}gir']);
	}

	public function testPropFindDepth1()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$responseBody = <<<XML
<?xml version="1.0"?>
<multistatus xmlns="DAV:">
  <response>
    <href>/foo</href>
    <propstat>
      <prop>
        <displayname>bar</displayname>
      </prop>
      <status>HTTP/1.1 200 OK</status>
    </propstat>
  </response>
</multistatus>
XML;

		$client->response = new Response(207, [], $responseBody);
		$result = $client->propFind('foo', ['{DAV:}displayname', '{urn:zim}gir'], 1);

		$this->assertSame([
			'/foo' => [
			'{DAV:}displayname' => 'bar'
			],
		], $result);

		$request = $client->request;
		$this->assertSame('PROPFIND', $request->getMethod());
		$this->assertSame('/foo', $request->getUrl());
		$this->assertSame([
			'Depth'        => ['1'],
			'Content-Type' => ['application/xml'],
		], $request->getHeaders());
	}

	public function testPropPatch()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$responseBody = <<<XML
<?xml version="1.0"?>
<multistatus xmlns="DAV:">
  <response>
    <href>/foo</href>
    <propstat>
      <prop>
        <displayname>bar</displayname>
      </prop>
      <status>HTTP/1.1 200 OK</status>
    </propstat>
  </response>
</multistatus>
XML;

		$client->response = new Response(207, [], $responseBody);
		$result = $client->propPatch('foo', ['{DAV:}displayname' => 'hi', '{urn:zim}gir' => null]);
		$this->assertTrue($result);
		$request = $client->request;
		$this->assertSame('PROPPATCH', $request->getMethod());
		$this->assertSame('/foo', $request->getUrl());
		$this->assertSame([
			'Content-Type' => ['application/xml'],
		], $request->getHeaders());
	}

	/**
	 * @depends testPropPatch
	 * @expectedException \Sabre\HTTP\ClientHttpException
	 */
	public function testPropPatchHTTPError()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$client->response = new Response(403, [], '');
		$client->propPatch('foo', ['{DAV:}displayname' => 'hi', '{urn:zim}gir' => null]);
	}

	/**
	 * @depends testPropPatch
	 * @expectedException Sabre\HTTP\ClientException
	 */
	public function testPropPatchMultiStatusError()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$responseBody = <<<XML
<?xml version="1.0"?>
<multistatus xmlns="DAV:">
<response>
  <href>/foo</href>
  <propstat>
    <prop>
      <displayname />
    </prop>
    <status>HTTP/1.1 403 Forbidden</status>
  </propstat>
</response>
</multistatus>
XML;

		$client->response = new Response(207, [], $responseBody);
		$client->propPatch('foo', ['{DAV:}displayname' => 'hi', '{urn:zim}gir' => null]);
	}

	public function testOPTIONS()
	{
		$client = new ClientMock([
			'baseUri' => '/',
		]);

		$client->response = new Response(207, [
			'DAV' => 'calendar-access, extended-mkcol',
		]);
		$result = $client->options();

		$this->assertSame(
			['calendar-access', 'extended-mkcol'],
			$result
		);

		$request = $client->request;
		$this->assertSame('OPTIONS', $request->getMethod());
		$this->assertSame('/', $request->getUrl());
		$this->assertSame([
		], $request->getHeaders());
	}
}
