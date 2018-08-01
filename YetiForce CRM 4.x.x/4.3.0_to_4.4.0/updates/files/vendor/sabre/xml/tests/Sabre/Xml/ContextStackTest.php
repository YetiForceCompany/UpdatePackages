<?php

namespace Sabre\Xml;

/**
 * Test for the ContextStackTrait.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class ContextStackTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->stack = $this->getMockForTrait('Sabre\\Xml\\ContextStackTrait');
	}

	public function testPushAndPull()
	{
		$this->stack->contextUri = '/foo/bar';
		$this->stack->elementMap['{DAV:}foo'] = 'Bar';
		$this->stack->namespaceMap['DAV:'] = 'd';

		$this->stack->pushContext();

		$this->assertSame('/foo/bar', $this->stack->contextUri);
		$this->assertSame('Bar', $this->stack->elementMap['{DAV:}foo']);
		$this->assertSame('d', $this->stack->namespaceMap['DAV:']);

		$this->stack->contextUri = '/gir/zim';
		$this->stack->elementMap['{DAV:}foo'] = 'newBar';
		$this->stack->namespaceMap['DAV:'] = 'dd';

		$this->stack->popContext();

		$this->assertSame('/foo/bar', $this->stack->contextUri);
		$this->assertSame('Bar', $this->stack->elementMap['{DAV:}foo']);
		$this->assertSame('d', $this->stack->namespaceMap['DAV:']);
	}
}
