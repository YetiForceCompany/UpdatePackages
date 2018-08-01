<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;

class PropertyTest extends TestCase
{
	public function testToString()
	{
		$cal = new VCalendar();

		$property = $cal->createProperty('propname', 'propvalue');
		$this->assertSame('PROPNAME', $property->name);
		$this->assertSame('propvalue', $property->__toString());
		$this->assertSame('propvalue', (string) $property);
		$this->assertSame('propvalue', $property->getValue());
	}

	public function testCreate()
	{
		$cal = new VCalendar();

		$params = [
			'param1' => 'value1',
			'param2' => 'value2',
		];

		$property = $cal->createProperty('propname', 'propvalue', $params);

		$this->assertSame('value1', $property['param1']->getValue());
		$this->assertSame('value2', $property['param2']->getValue());
	}

	public function testSetValue()
	{
		$cal = new VCalendar();

		$property = $cal->createProperty('propname', 'propvalue');
		$property->setValue('value2');

		$this->assertSame('PROPNAME', $property->name);
		$this->assertSame('value2', $property->__toString());
	}

	public function testParameterExists()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';

		$this->assertTrue(isset($property['PARAMNAME']));
		$this->assertTrue(isset($property['paramname']));
		$this->assertFalse(isset($property['foo']));
	}

	public function testParameterGet()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';

		$this->assertInstanceOf('Sabre\\VObject\\Parameter', $property['paramname']);
	}

	public function testParameterNotExists()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';

		$this->assertInternalType('null', $property['foo']);
	}

	public function testParameterMultiple()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';
		$property->add('paramname', 'paramvalue');

		$this->assertInstanceOf('Sabre\\VObject\\Parameter', $property['paramname']);
		$this->assertSame(2, count($property['paramname']->getParts()));
	}

	public function testSetParameterAsString()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';

		$this->assertSame(1, count($property->parameters()));
		$this->assertInstanceOf('Sabre\\VObject\\Parameter', $property->parameters['PARAMNAME']);
		$this->assertSame('PARAMNAME', $property->parameters['PARAMNAME']->name);
		$this->assertSame('paramvalue', $property->parameters['PARAMNAME']->getValue());
	}

	public function testUnsetParameter()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$property['paramname'] = 'paramvalue';

		unset($property['PARAMNAME']);
		$this->assertSame(0, count($property->parameters()));
	}

	public function testSerialize()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');

		$this->assertSame("PROPNAME:propvalue\r\n", $property->serialize());
	}

	public function testSerializeParam()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue', [
			'paramname'  => 'paramvalue',
			'paramname2' => 'paramvalue2',
		]);

		$this->assertSame("PROPNAME;PARAMNAME=paramvalue;PARAMNAME2=paramvalue2:propvalue\r\n", $property->serialize());
	}

	public function testSerializeNewLine()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('SUMMARY', "line1\nline2");

		$this->assertSame("SUMMARY:line1\\nline2\r\n", $property->serialize());
	}

	public function testSerializeLongLine()
	{
		$cal = new VCalendar();
		$value = str_repeat('!', 200);
		$property = $cal->createProperty('propname', $value);

		$expected = 'PROPNAME:' . str_repeat('!', 66) . "\r\n " . str_repeat('!', 74) . "\r\n " . str_repeat('!', 60) . "\r\n";

		$this->assertSame($expected, $property->serialize());
	}

	public function testSerializeUTF8LineFold()
	{
		$cal = new VCalendar();
		$value = str_repeat('!', 65) . "\xc3\xa4bla" . str_repeat('!', 142) . "\xc3\xa4foo"; // inserted umlaut-a
		$property = $cal->createProperty('propname', $value);

		// PROPNAME:!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ("PROPNAME:" + 65x"!" = 74 bytes)
		//  äbla!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! (" äbla"     + 69x"!" = 75 bytes)
		//  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! (" "         + 73x"!" = 74 bytes)
		//  äfoo
		$expected = 'PROPNAME:' . str_repeat('!', 65) . "\r\n \xc3\xa4bla" . str_repeat('!', 69) . "\r\n " . str_repeat('!', 73) . "\r\n \xc3\xa4foo\r\n";
		$this->assertSame($expected, $property->serialize());
	}

	public function testGetIterator()
	{
		$cal = new VCalendar();
		$it = new ElementList([]);
		$property = $cal->createProperty('propname', 'propvalue');
		$property->setIterator($it);
		$this->assertSame($it, $property->getIterator());
	}

	public function testGetIteratorDefault()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('propname', 'propvalue');
		$it = $property->getIterator();
		$this->assertTrue($it instanceof ElementList);
		$this->assertSame(1, count($it));
	}

	public function testAddScalar()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('EMAIL');

		$property->add('myparam', 'value');

		$this->assertSame(1, count($property->parameters()));

		$this->assertTrue($property->parameters['MYPARAM'] instanceof Parameter);
		$this->assertSame('MYPARAM', $property->parameters['MYPARAM']->name);
		$this->assertSame('value', $property->parameters['MYPARAM']->getValue());
	}

	public function testAddParameter()
	{
		$cal = new VCalendar();
		$prop = $cal->createProperty('EMAIL');

		$prop->add('MYPARAM', 'value');

		$this->assertSame(1, count($prop->parameters()));
		$this->assertSame('MYPARAM', $prop['myparam']->name);
	}

	public function testAddParameterTwice()
	{
		$cal = new VCalendar();
		$prop = $cal->createProperty('EMAIL');

		$prop->add('MYPARAM', 'value1');
		$prop->add('MYPARAM', 'value2');

		$this->assertSame(1, count($prop->parameters));
		$this->assertSame(2, count($prop->parameters['MYPARAM']->getParts()));

		$this->assertSame('MYPARAM', $prop['MYPARAM']->name);
	}

	public function testClone()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('EMAIL', 'value');
		$property['FOO'] = 'BAR';

		$property2 = clone $property;

		$property['FOO'] = 'BAZ';
		$this->assertSame('BAR', (string) $property2['FOO']);
	}

	public function testCreateParams()
	{
		$cal = new VCalendar();
		$property = $cal->createProperty('X-PROP', 'value', [
			'param1' => 'value1',
			'param2' => ['value2', 'value3']
		]);

		$this->assertSame(1, count($property['PARAM1']->getParts()));
		$this->assertSame(2, count($property['PARAM2']->getParts()));
	}

	public function testValidateNonUTF8()
	{
		$calendar = new VCalendar();
		$property = $calendar->createProperty('X-PROP', "Bla\x00");
		$result = $property->validate(Property::REPAIR);

		$this->assertSame('Property contained a control character (0x00)', $result[0]['message']);
		$this->assertSame('Bla', $property->getValue());
	}

	public function testValidateControlChars()
	{
		$s = 'chars[';
		foreach ([
			0x7F, 0x5E, 0x5C, 0x3B, 0x3A, 0x2C, 0x22, 0x20,
			0x1F, 0x1E, 0x1D, 0x1C, 0x1B, 0x1A, 0x19, 0x18,
			0x17, 0x16, 0x15, 0x14, 0x13, 0x12, 0x11, 0x10,
			0x0F, 0x0E, 0x0D, 0x0C, 0x0B, 0x0A, 0x09, 0x08,
			0x07, 0x06, 0x05, 0x04, 0x03, 0x02, 0x01, 0x00,
		  ] as $c) {
			$s .= sprintf('%02X(%c)', $c, $c);
		}
		$s .= ']end';

		$calendar = new VCalendar();
		$property = $calendar->createProperty('X-PROP', $s);
		$result = $property->validate(Property::REPAIR);

		$this->assertSame('Property contained a control character (0x7f)', $result[0]['message']);
		$this->assertSame("chars[7F()5E(^)5C(\\\\)3B(\\;)3A(:)2C(\\,)22(\")20( )1F()1E()1D()1C()1B()1A()19()18()17()16()15()14()13()12()11()10()0F()0E()0D()0C()0B()0A(\\n)09(\t)08()07()06()05()04()03()02()01()00()]end", $property->getRawMimeDirValue());
	}

	public function testValidateBadPropertyName()
	{
		$calendar = new VCalendar();
		$property = $calendar->createProperty('X_*&PROP*', 'Bla');
		$result = $property->validate(Property::REPAIR);

		$this->assertSame($result[0]['message'], 'The propertyname: X_*&PROP* contains invalid characters. Only A-Z, 0-9 and - are allowed');
		$this->assertSame('X-PROP', $property->name);
	}

	public function testGetValue()
	{
		$calendar = new VCalendar();
		$property = $calendar->createProperty('SUMMARY', null);
		$this->assertSame([], $property->getParts());
		$this->assertNull($property->getValue());

		$property->setValue([]);
		$this->assertSame([], $property->getParts());
		$this->assertNull($property->getValue());

		$property->setValue([1]);
		$this->assertSame([1], $property->getParts());
		$this->assertSame(1, $property->getValue());

		$property->setValue([1, 2]);
		$this->assertSame([1, 2], $property->getParts());
		$this->assertSame('1,2', $property->getValue());

		$property->setValue('str');
		$this->assertSame(['str'], $property->getParts());
		$this->assertSame('str', $property->getValue());
	}

	/**
	 * ElementList should reject this.
	 *
	 * @expectedException \LogicException
	 */
	public function testArrayAccessSetInt()
	{
		$calendar = new VCalendar();
		$property = $calendar->createProperty('X-PROP', null);

		$calendar->add($property);
		$calendar->{'X-PROP'}[0] = 'Something!';
	}

	/**
	 * ElementList should reject this.
	 *
	 * @expectedException \LogicException
	 */
	public function testArrayAccessUnsetInt()
	{
		$calendar = new VCalendar();
		$property = $calendar->createProperty('X-PROP', null);

		$calendar->add($property);
		unset($calendar->{'X-PROP'}[0]);
	}

	public function testValidateBadEncoding()
	{
		$document = new VCalendar();
		$property = $document->add('X-FOO', 'value');
		$property['ENCODING'] = 'invalid';

		$result = $property->validate();

		$this->assertSame('ENCODING=INVALID is not valid for this document type.', $result[0]['message']);
		$this->assertSame(3, $result[0]['level']);
	}

	public function testValidateBadEncodingVCard4()
	{
		$document = new VCard(['VERSION' => '4.0']);
		$property = $document->add('X-FOO', 'value');
		$property['ENCODING'] = 'BASE64';

		$result = $property->validate();

		$this->assertSame('ENCODING parameter is not valid in vCard 4.', $result[0]['message']);
		$this->assertSame(3, $result[0]['level']);
	}

	public function testValidateBadEncodingVCard3()
	{
		$document = new VCard(['VERSION' => '3.0']);
		$property = $document->add('X-FOO', 'value');
		$property['ENCODING'] = 'BASE64';

		$result = $property->validate();

		$this->assertSame('ENCODING=BASE64 is not valid for this document type.', $result[0]['message']);
		$this->assertSame(3, $result[0]['level']);
	}

	public function testValidateBadEncodingVCard21()
	{
		$document = new VCard(['VERSION' => '2.1']);
		$property = $document->add('X-FOO', 'value');
		$property['ENCODING'] = 'B';

		$result = $property->validate();

		$this->assertSame('ENCODING=B is not valid for this document type.', $result[0]['message']);
		$this->assertSame(3, $result[0]['level']);
	}
}
