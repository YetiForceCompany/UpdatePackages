<?php

namespace Sabre\DAV;

class CorePluginTest extends \PHPUnit_Framework_TestCase
{
	public function testGetInfo()
	{
		$corePlugin = new CorePlugin();
		$this->assertSame('core', $corePlugin->getPluginInfo()['name']);
	}
}
