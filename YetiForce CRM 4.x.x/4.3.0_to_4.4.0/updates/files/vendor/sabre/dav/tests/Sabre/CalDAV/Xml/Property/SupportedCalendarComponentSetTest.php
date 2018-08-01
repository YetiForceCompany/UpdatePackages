<?php

namespace Sabre\CalDAV\Xml\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class SupportedCalendarComponentSetTest extends DAV\Xml\XmlTest
{
	public function setUp()
	{
		$this->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
		$this->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
	}

	public function testSimple()
	{
		$prop = new SupportedCalendarComponentSet(['VEVENT']);
		$this->assertSame(
			['VEVENT'],
			$prop->getValue()
		);
	}

	public function testMultiple()
	{
		$prop = new SupportedCalendarComponentSet(['VEVENT', 'VTODO']);
		$this->assertSame(
			['VEVENT', 'VTODO'],
			$prop->getValue()
		);
	}

	/**
	 * @depends testSimple
	 * @depends testMultiple
	 */
	public function testSerialize()
	{
		$property = new SupportedCalendarComponentSet(['VEVENT', 'VTODO']);
		$xml = $this->write(['{DAV:}root' => $property]);

		$this->assertXmlStringEqualsXmlString(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:cal="' . CalDAV\Plugin::NS_CALDAV . '" xmlns:cs="' . CalDAV\Plugin::NS_CALENDARSERVER . '">
  <cal:comp name="VEVENT"/>
  <cal:comp name="VTODO"/>
</d:root>
', $xml);
	}

	public function testUnserialize()
	{
		$cal = CalDAV\Plugin::NS_CALDAV;
		$cs = CalDAV\Plugin::NS_CALENDARSERVER;

		$xml = <<<XML
<?xml version="1.0"?>
 <d:root xmlns:cal="$cal" xmlns:cs="$cs" xmlns:d="DAV:">
   <cal:comp name="VEVENT"/>
   <cal:comp name="VTODO"/>
 </d:root>
XML;

		$result = $this->parse(
			$xml,
			['{DAV:}root' => 'Sabre\\CalDAV\\Xml\\Property\\SupportedCalendarComponentSet']
		);

		$this->assertSame(
			new SupportedCalendarComponentSet(['VEVENT', 'VTODO']),
			$result['value']
		);
	}

	/**
	 * @expectedException \Sabre\Xml\ParseException
	 */
	public function testUnserializeEmpty()
	{
		$cal = CalDAV\Plugin::NS_CALDAV;
		$cs = CalDAV\Plugin::NS_CALENDARSERVER;

		$xml = <<<XML
<?xml version="1.0"?>
 <d:root xmlns:cal="$cal" xmlns:cs="$cs" xmlns:d="DAV:">
 </d:root>
XML;

		$result = $this->parse(
			$xml,
			['{DAV:}root' => 'Sabre\\CalDAV\\Xml\\Property\\SupportedCalendarComponentSet']
		);
	}
}
