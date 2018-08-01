<?php

namespace Sabre\CalDAV\Schedule;

class PluginBasicTest extends \Sabre\DAVServerTest
{
	public $setupCalDAV = true;
	public $setupCalDAVScheduling = true;

	public function testSimple()
	{
		$plugin = new Plugin();
		$this->assertSame(
			'caldav-schedule',
			$plugin->getPluginInfo()['name']
		);
	}

	public function testOptions()
	{
		$plugin = new Plugin();
		$expected = [
			'calendar-auto-schedule',
			'calendar-availability',
		];
		$this->assertSame($expected, $plugin->getFeatures());
	}

	public function testGetHTTPMethods()
	{
		$this->assertSame([], $this->caldavSchedulePlugin->getHTTPMethods('notfound'));
		$this->assertSame([], $this->caldavSchedulePlugin->getHTTPMethods('calendars/user1'));
		$this->assertSame(['POST'], $this->caldavSchedulePlugin->getHTTPMethods('calendars/user1/outbox'));
	}
}
