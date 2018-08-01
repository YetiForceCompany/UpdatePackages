<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
	public function testNoScheduleStatus()
	{
		$message = new Message();
		$this->assertFalse($message->getScheduleStatus());
	}

	public function testScheduleStatus()
	{
		$message = new Message();
		$message->scheduleStatus = '1.2;Delivered';

		$this->assertSame('1.2', $message->getScheduleStatus());
	}

	public function testUnexpectedScheduleStatus()
	{
		$message = new Message();
		$message->scheduleStatus = '9.9.9';

		$this->assertSame('9.9.9', $message->getScheduleStatus());
	}
}
