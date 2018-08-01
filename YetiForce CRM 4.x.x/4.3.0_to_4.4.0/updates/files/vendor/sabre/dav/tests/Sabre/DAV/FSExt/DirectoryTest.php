<?php

namespace Sabre\DAV\FSExt;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
	public function create()
	{
		return new Directory(SABRE_TEMPDIR);
	}

	public function testCreate()
	{
		$dir = $this->create();
		$this->assertSame(basename(SABRE_TEMPDIR), $dir->getName());
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\Forbidden
	 */
	public function testChildExistDot()
	{
		$dir = $this->create();
		$dir->childExists('..');
	}
}
