<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAVACL\PrincipalBackend\Mock as PrincipalBackend;

class HomeCollectionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * System under test.
	 *
	 * @var HomeCollection
	 */
	protected $sut;

	protected $path;
	protected $name = 'thuis';

	public function setUp()
	{
		$principalBackend = new PrincipalBackend();

		$this->path = SABRE_TEMPDIR . '/home';

		$this->sut = new HomeCollection($principalBackend, $this->path);
		$this->sut->collectionName = $this->name;
	}

	public function tearDown()
	{
		\Sabre\TestUtil::clearTempDir();
	}

	public function testGetName()
	{
		$this->assertSame(
			$this->name,
			$this->sut->getName()
		);
	}

	public function testGetChild()
	{
		$child = $this->sut->getChild('user1');
		$this->assertInstanceOf('Sabre\\DAVACL\\FS\\Collection', $child);
		$this->assertSame('user1', $child->getName());

		$owner = 'principals/user1';
		$acl = [
			[
				'privilege' => '{DAV:}all',
				'principal' => '{DAV:}owner',
				'protected' => true,
			],
		];

		$this->assertSame($acl, $child->getACL());
		$this->assertSame($owner, $child->getOwner());
	}

	public function testGetOwner()
	{
		$this->assertNull(
			$this->sut->getOwner()
		);
	}

	public function testGetGroup()
	{
		$this->assertNull(
			$this->sut->getGroup()
		);
	}

	public function testGetACL()
	{
		$acl = [
			[
				'principal' => '{DAV:}authenticated',
				'privilege' => '{DAV:}read',
				'protected' => true,
			]
		];

		$this->assertSame(
			$acl,
			$this->sut->getACL()
		);
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\Forbidden
	 */
	public function testSetAcl()
	{
		$this->sut->setACL([]);
	}

	public function testGetSupportedPrivilegeSet()
	{
		$this->assertNull(
			$this->sut->getSupportedPrivilegeSet()
		);
	}
}
