<?php

namespace Sabre\DAV;

class SimpleFileTest extends \PHPUnit_Framework_TestCase
{
	public function testAll()
	{
		$file = new SimpleFile('filename.txt', 'contents', 'text/plain');

		$this->assertSame('filename.txt', $file->getName());
		$this->assertSame('contents', $file->get());
		$this->assertSame(8, $file->getSize());
		$this->assertSame('"' . sha1('contents') . '"', $file->getETag());
		$this->assertSame('text/plain', $file->getContentType());
	}
}
