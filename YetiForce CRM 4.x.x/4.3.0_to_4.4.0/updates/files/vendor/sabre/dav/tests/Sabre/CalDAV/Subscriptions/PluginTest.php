<?php

namespace Sabre\CalDAV\Subscriptions;

use Sabre\DAV\PropFind;

class PluginTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{
		$server = new \Sabre\DAV\Server();
		$plugin = new Plugin();

		$server->addPlugin($plugin);

		$this->assertSame(
			'{http://calendarserver.org/ns/}subscribed',
			$server->resourceTypeMapping['Sabre\\CalDAV\\Subscriptions\\ISubscription']
		);
		$this->assertSame(
			'Sabre\\DAV\\Xml\\Property\\Href',
			$server->xml->elementMap['{http://calendarserver.org/ns/}source']
		);

		$this->assertSame(
			['calendarserver-subscribed'],
			$plugin->getFeatures()
		);

		$this->assertSame(
			'subscriptions',
			$plugin->getPluginInfo()['name']
		);
	}

	public function testPropFind()
	{
		$propName = '{http://calendarserver.org/ns/}subscribed-strip-alarms';
		$propFind = new PropFind('foo', [$propName]);
		$propFind->set($propName, null, 200);

		$plugin = new Plugin();
		$plugin->propFind($propFind, new \Sabre\DAV\SimpleCollection('hi'));

		$this->assertFalse(is_null($propFind->get($propName)));
	}
}
