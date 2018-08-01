<?php

namespace Sabre\Xml;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
	public function testGetReader()
	{
		$elems = [
			'{http://sabre.io/ns}test' => 'Test!',
		];

		$util = new Service();
		$util->elementMap = $elems;

		$reader = $util->getReader();
		$this->assertInstanceOf('Sabre\\Xml\\Reader', $reader);
		$this->assertSame($elems, $reader->elementMap);
	}

	public function testGetWriter()
	{
		$ns = [
			'http://sabre.io/ns' => 's',
		];

		$util = new Service();
		$util->namespaceMap = $ns;

		$writer = $util->getWriter();
		$this->assertInstanceOf('Sabre\\Xml\\Writer', $writer);
		$this->assertSame($ns, $writer->namespaceMap);
	}

	/**
	 * @depends testGetReader
	 */
	public function testParse()
	{
		$xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
		$util = new Service();
		$result = $util->parse($xml, null, $rootElement);
		$this->assertSame('{http://sabre.io/ns}root', $rootElement);

		$expected = [
			[
				'name'       => '{http://sabre.io/ns}child',
				'value'      => 'value',
				'attributes' => [],
			]
		];

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @depends testGetReader
	 */
	public function testParseStream()
	{
		$xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
		$stream = fopen('php://memory', 'r+');
		fwrite($stream, $xml);
		rewind($stream);

		$util = new Service();
		$result = $util->parse($stream, null, $rootElement);
		$this->assertSame('{http://sabre.io/ns}root', $rootElement);

		$expected = [
			[
				'name'       => '{http://sabre.io/ns}child',
				'value'      => 'value',
				'attributes' => [],
			]
		];

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @depends testGetReader
	 */
	public function testExpect()
	{
		$xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
		$util = new Service();
		$result = $util->expect('{http://sabre.io/ns}root', $xml);

		$expected = [
			[
				'name'       => '{http://sabre.io/ns}child',
				'value'      => 'value',
				'attributes' => [],
			]
		];

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @depends testGetReader
	 */
	public function testExpectStream()
	{
		$xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;

		$stream = fopen('php://memory', 'r+');
		fwrite($stream, $xml);
		rewind($stream);

		$util = new Service();
		$result = $util->expect('{http://sabre.io/ns}root', $stream);

		$expected = [
			[
				'name'       => '{http://sabre.io/ns}child',
				'value'      => 'value',
				'attributes' => [],
			]
		];

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @depends testGetReader
	 * @expectedException \Sabre\Xml\ParseException
	 */
	public function testExpectWrong()
	{
		$xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
		$util = new Service();
		$util->expect('{http://sabre.io/ns}error', $xml);
	}

	/**
	 * @depends testGetWriter
	 */
	public function testWrite()
	{
		$util = new Service();
		$util->namespaceMap = [
			'http://sabre.io/ns' => 's',
		];
		$result = $util->write('{http://sabre.io/ns}root', [
			'{http://sabre.io/ns}child' => 'value',
		]);

		$expected = <<<XML
<?xml version="1.0"?>
<s:root xmlns:s="http://sabre.io/ns">
 <s:child>value</s:child>
</s:root>

XML;
		$this->assertSame(
			$expected,
			$result
		);
	}

	public function testMapValueObject()
	{
		$input = <<<XML
<?xml version="1.0"?>
<order xmlns="http://sabredav.org/ns">
 <id>1234</id>
 <amount>99.99</amount>
 <description>black friday deal</description>
 <status>
  <id>5</id>
  <label>processed</label>
 </status>
</order>

XML;

		$ns = 'http://sabredav.org/ns';
		$orderService = new \Sabre\Xml\Service();
		$orderService->mapValueObject('{' . $ns . '}order', 'Sabre\Xml\Order');
		$orderService->mapValueObject('{' . $ns . '}status', 'Sabre\Xml\OrderStatus');
		$orderService->namespaceMap[$ns] = null;

		$order = $orderService->parse($input);
		$expected = new Order();
		$expected->id = 1234;
		$expected->amount = 99.99;
		$expected->description = 'black friday deal';
		$expected->status = new OrderStatus();
		$expected->status->id = 5;
		$expected->status->label = 'processed';

		$this->assertSame($expected, $order);

		$writtenXml = $orderService->writeValueObject($order);
		$this->assertSame($input, $writtenXml);
	}

	public function testMapValueObjectArrayProperty()
	{
		$input = <<<XML
<?xml version="1.0"?>
<order xmlns="http://sabredav.org/ns">
 <id>1234</id>
 <amount>99.99</amount>
 <description>black friday deal</description>
 <status>
  <id>5</id>
  <label>processed</label>
 </status>
 <link>http://example.org/</link>
 <link>http://example.com/</link>
</order>

XML;

		$ns = 'http://sabredav.org/ns';
		$orderService = new \Sabre\Xml\Service();
		$orderService->mapValueObject('{' . $ns . '}order', 'Sabre\Xml\Order');
		$orderService->mapValueObject('{' . $ns . '}status', 'Sabre\Xml\OrderStatus');
		$orderService->namespaceMap[$ns] = null;

		$order = $orderService->parse($input);
		$expected = new Order();
		$expected->id = 1234;
		$expected->amount = 99.99;
		$expected->description = 'black friday deal';
		$expected->status = new OrderStatus();
		$expected->status->id = 5;
		$expected->status->label = 'processed';
		$expected->link = ['http://example.org/', 'http://example.com/'];

		$this->assertSame($expected, $order);

		$writtenXml = $orderService->writeValueObject($order);
		$this->assertSame($input, $writtenXml);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testWriteVoNotFound()
	{
		$service = new Service();
		$service->writeValueObject(new \StdClass());
	}

	public function testParseClarkNotation()
	{
		$this->assertSame([
			'http://sabredav.org/ns',
			'elem',
		], Service::parseClarkNotation('{http://sabredav.org/ns}elem'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testParseClarkNotationFail()
	{
		Service::parseClarkNotation('http://sabredav.org/ns}elem');
	}
}

/**
 * asset for testMapValueObject().
 *
 * @internal
 */
class Order
{
	public $id;
	public $amount;
	public $description;
	public $status;
	public $empty;
	public $link = [];
}

/**
 * asset for testMapValueObject().
 *
 * @internal
 */
class OrderStatus
{
	public $id;
	public $label;
}
