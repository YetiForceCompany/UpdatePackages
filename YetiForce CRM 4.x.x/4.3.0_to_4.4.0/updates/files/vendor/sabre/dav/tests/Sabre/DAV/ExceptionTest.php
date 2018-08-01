<?php

namespace Sabre\DAV;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testStatus()
	{
		$e = new Exception();
		$this->assertSame(500, $e->getHTTPCode());
	}

	public function testExceptionStatuses()
	{
		$c = [
			'Sabre\\DAV\\Exception\\NotAuthenticated'    => 401,
			'Sabre\\DAV\\Exception\\InsufficientStorage' => 507,
		];

		foreach ($c as $class => $status) {
			$obj = new $class();
			$this->assertSame($status, $obj->getHTTPCode());
		}
	}
}
