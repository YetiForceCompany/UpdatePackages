<?php

namespace Sabre\DAV\Exception;

class ServiceUnavailableTest extends \PHPUnit_Framework_TestCase
{
	public function testGetHTTPCode()
	{
		$ex = new ServiceUnavailable();
		$this->assertSame(503, $ex->getHTTPCode());
	}
}
