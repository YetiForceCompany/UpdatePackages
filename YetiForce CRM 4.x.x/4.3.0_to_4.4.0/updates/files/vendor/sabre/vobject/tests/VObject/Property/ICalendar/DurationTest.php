<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;

class DurationTest extends TestCase
{
	public function testGetDateInterval()
	{
		$vcal = new VCalendar();
		$event = $vcal->add('VEVENT', ['DURATION' => ['PT1H']]);

		$this->assertSame(
			new \DateInterval('PT1H'),
			$event->{'DURATION'}->getDateInterval()
		);
	}
}
