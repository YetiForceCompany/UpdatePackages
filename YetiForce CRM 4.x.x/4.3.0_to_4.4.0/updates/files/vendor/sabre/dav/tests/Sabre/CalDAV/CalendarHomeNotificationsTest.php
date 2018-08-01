<?php

namespace Sabre\CalDAV;

class CalendarHomeNotificationsTest extends \PHPUnit_Framework_TestCase
{
	public function testGetChildrenNoSupport()
	{
		$backend = new Backend\Mock();
		$calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);

		$this->assertSame(
			[],
			$calendarHome->getChildren()
		);
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\NotFound
	 */
	public function testGetChildNoSupport()
	{
		$backend = new Backend\Mock();
		$calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);
		$calendarHome->getChild('notifications');
	}

	public function testGetChildren()
	{
		$backend = new Backend\MockSharing();
		$calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);

		$result = $calendarHome->getChildren();
		$this->assertSame('notifications', $result[0]->getName());
	}

	public function testGetChild()
	{
		$backend = new Backend\MockSharing();
		$calendarHome = new CalendarHome($backend, ['uri' => 'principals/user']);
		$result = $calendarHome->getChild('notifications');
		$this->assertSame('notifications', $result->getName());
	}
}
