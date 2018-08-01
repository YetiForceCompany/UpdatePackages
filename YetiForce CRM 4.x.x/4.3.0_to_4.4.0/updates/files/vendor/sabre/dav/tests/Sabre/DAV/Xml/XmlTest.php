<?php

namespace Sabre\DAV\Xml;

use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

abstract class XmlTest extends \PHPUnit_Framework_TestCase
{
	protected $elementMap = [];
	protected $namespaceMap = ['DAV:' => 'd'];
	protected $contextUri = '/';

	public function write($input)
	{
		$writer = new Writer();
		$writer->contextUri = $this->contextUri;
		$writer->namespaceMap = $this->namespaceMap;
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->write($input);
		return $writer->outputMemory();
	}

	public function parse($xml, array $elementMap = [])
	{
		$reader = new Reader();
		$reader->elementMap = array_merge($this->elementMap, $elementMap);
		$reader->xml($xml);
		return $reader->parse();
	}

	public function assertParsedValue($expected, $xml, array $elementMap = [])
	{
		$result = $this->parse($xml, $elementMap);
		$this->assertSame($expected, $result['value']);
	}

	public function cleanUp()
	{
		libxml_clear_errors();
	}
}
