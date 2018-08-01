<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
	public function getComponent()
	{
		$data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";
		return Reader::read($data);
	}

	public function testWriteToMimeDir()
	{
		$result = Writer::write($this->getComponent());
		$this->assertSame("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $result);
	}

	public function testWriteToJson()
	{
		$result = Writer::writeJson($this->getComponent());
		$this->assertSame('["vcalendar",[],[]]', $result);
	}

	public function testWriteToXml()
	{
		$result = Writer::writeXml($this->getComponent());
		$this->assertSame(
			'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
			'<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">' . "\n" .
			' <vcalendar/>' . "\n" .
			'</icalendar>' . "\n",
			$result
		);
	}
}
