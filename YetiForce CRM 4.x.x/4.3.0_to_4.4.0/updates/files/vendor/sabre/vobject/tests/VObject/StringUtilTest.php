<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
	public function testNonUTF8()
	{
		$string = StringUtil::isUTF8(chr(0xbf));

		$this->assertSame(false, $string);
	}

	public function testIsUTF8()
	{
		$string = StringUtil::isUTF8('I 💚 SabreDAV');

		$this->assertSame(true, $string);
	}

	public function testUTF8ControlChar()
	{
		$string = StringUtil::isUTF8(chr(0x00));

		$this->assertSame(false, $string);
	}

	public function testConvertToUTF8nonUTF8()
	{
		$string = StringUtil::convertToUTF8(chr(0xbf));

		$this->assertSame(utf8_encode(chr(0xbf)), $string);
	}

	public function testConvertToUTF8IsUTF8()
	{
		$string = StringUtil::convertToUTF8('I 💚 SabreDAV');

		$this->assertSame('I 💚 SabreDAV', $string);
	}

	public function testConvertToUTF8ControlChar()
	{
		$string = StringUtil::convertToUTF8(chr(0x00));

		$this->assertSame('', $string);
	}
}
