<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;

class CompoundTest extends TestCase
{
	public function testSetParts()
	{
		$arr = [
			'ABC, Inc.',
			'North American Division',
			'Marketing;Sales',
		];

		$vcard = new VCard();
		$elem = $vcard->createProperty('ORG');
		$elem->setParts($arr);

		$this->assertSame('ABC\, Inc.;North American Division;Marketing\;Sales', $elem->getValue());
		$this->assertSame(3, count($elem->getParts()));
		$parts = $elem->getParts();
		$this->assertSame('Marketing;Sales', $parts[2]);
	}

	public function testGetParts()
	{
		$str = 'ABC\, Inc.;North American Division;Marketing\;Sales';

		$vcard = new VCard();
		$elem = $vcard->createProperty('ORG');
		$elem->setRawMimeDirValue($str);

		$this->assertSame(3, count($elem->getParts()));
		$parts = $elem->getParts();
		$this->assertSame('Marketing;Sales', $parts[2]);
	}

	public function testGetPartsNull()
	{
		$vcard = new VCard();
		$elem = $vcard->createProperty('ORG', null);

		$this->assertSame(0, count($elem->getParts()));
	}
}
