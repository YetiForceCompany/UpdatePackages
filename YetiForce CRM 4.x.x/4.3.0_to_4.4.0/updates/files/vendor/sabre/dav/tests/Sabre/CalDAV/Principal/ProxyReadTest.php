<?php

namespace Sabre\CalDAV\Principal;

use Sabre\DAVACL;

class ProxyReadTest extends \PHPUnit_Framework_TestCase
{
	protected $backend;

	public function getInstance()
	{
		$backend = new DAVACL\PrincipalBackend\Mock();
		$principal = new ProxyRead($backend, [
			'uri' => 'principal/user',
		]);
		$this->backend = $backend;
		return $principal;
	}

	public function testGetName()
	{
		$i = $this->getInstance();
		$this->assertSame('calendar-proxy-read', $i->getName());
	}

	public function testGetDisplayName()
	{
		$i = $this->getInstance();
		$this->assertSame('calendar-proxy-read', $i->getDisplayName());
	}

	public function testGetLastModified()
	{
		$i = $this->getInstance();
		$this->assertNull($i->getLastModified());
	}

	/**
	 * @expectedException Sabre\DAV\Exception\Forbidden
	 */
	public function testDelete()
	{
		$i = $this->getInstance();
		$i->delete();
	}

	/**
	 * @expectedException Sabre\DAV\Exception\Forbidden
	 */
	public function testSetName()
	{
		$i = $this->getInstance();
		$i->setName('foo');
	}

	public function testGetAlternateUriSet()
	{
		$i = $this->getInstance();
		$this->assertSame([], $i->getAlternateUriSet());
	}

	public function testGetPrincipalUri()
	{
		$i = $this->getInstance();
		$this->assertSame('principal/user/calendar-proxy-read', $i->getPrincipalUrl());
	}

	public function testGetGroupMemberSet()
	{
		$i = $this->getInstance();
		$this->assertSame([], $i->getGroupMemberSet());
	}

	public function testGetGroupMembership()
	{
		$i = $this->getInstance();
		$this->assertSame([], $i->getGroupMembership());
	}

	public function testSetGroupMemberSet()
	{
		$i = $this->getInstance();
		$i->setGroupMemberSet(['principals/foo']);

		$expected = [
			$i->getPrincipalUrl() => ['principals/foo']
		];

		$this->assertSame($expected, $this->backend->groupMembers);
	}
}
