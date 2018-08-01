<?php

namespace Sabre\DAV;

use Sabre\HTTP;

require_once 'Sabre/DAV/AbstractServer.php';
require_once 'Sabre/DAV/TestPlugin.php';

class ServerPluginTest extends AbstractServer
{
	/**
	 * @var Sabre\DAV\TestPlugin
	 */
	protected $testPlugin;

	public function setUp()
	{
		parent::setUp();

		$testPlugin = new TestPlugin();
		$this->server->addPlugin($testPlugin);
		$this->testPlugin = $testPlugin;
	}

	public function testBaseClass()
	{
		$p = new ServerPluginMock();
		$this->assertSame([], $p->getFeatures());
		$this->assertSame([], $p->getHTTPMethods(''));
		$this->assertSame(
			[
				'name'        => 'Sabre\DAV\ServerPluginMock',
				'description' => null,
				'link'        => null
			], $p->getPluginInfo()
		);
	}

	public function testOptions()
	{
		$serverVars = [
			'REQUEST_URI'    => '/',
			'REQUEST_METHOD' => 'OPTIONS',
		];

		$request = HTTP\Sapi::createFromServerArray($serverVars);
		$this->server->httpRequest = ($request);
		$this->server->exec();

		$this->assertSame([
			'DAV'             => ['1, 3, extended-mkcol, drinking'],
			'MS-Author-Via'   => ['DAV'],
			'Allow'           => ['OPTIONS, GET, HEAD, DELETE, PROPFIND, PUT, PROPPATCH, COPY, MOVE, REPORT, BEER, WINE'],
			'Accept-Ranges'   => ['bytes'],
			'Content-Length'  => ['0'],
			'X-Sabre-Version' => [Version::VERSION],
		], $this->response->getHeaders());

		$this->assertSame(200, $this->response->status);
		$this->assertSame('', $this->response->body);
		$this->assertSame('OPTIONS', $this->testPlugin->beforeMethod);
	}

	public function testGetPlugin()
	{
		$this->assertSame($this->testPlugin, $this->server->getPlugin(get_class($this->testPlugin)));
	}

	public function testUnknownPlugin()
	{
		$this->assertNull($this->server->getPlugin('SomeRandomClassName'));
	}

	public function testGetSupportedReportSet()
	{
		$this->assertSame([], $this->testPlugin->getSupportedReportSet('/'));
	}

	public function testGetPlugins()
	{
		$this->assertSame(
			[
				get_class($this->testPlugin) => $this->testPlugin,
				'core'                       => $this->server->getPlugin('core'),
			],
			$this->server->getPlugins()
		);
	}
}

class ServerPluginMock extends ServerPlugin
{
	public function initialize(Server $s)
	{
	}
}
