<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\HTTP\Response;
use Sabre\HTTP\Sapi;

class BasicCallBackTest extends \PHPUnit_Framework_TestCase
{
	public function testCallBack()
	{
		$args = [];
		$callBack = function ($user, $pass) use (&$args) {
			$args = [$user, $pass];
			return true;
		};

		$backend = new BasicCallBack($callBack);

		$request = Sapi::createFromServerArray([
			'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('foo:bar'),
		]);
		$response = new Response();

		$this->assertSame(
			[true, 'principals/foo'],
			$backend->check($request, $response)
		);

		$this->assertSame(['foo', 'bar'], $args);
	}
}
