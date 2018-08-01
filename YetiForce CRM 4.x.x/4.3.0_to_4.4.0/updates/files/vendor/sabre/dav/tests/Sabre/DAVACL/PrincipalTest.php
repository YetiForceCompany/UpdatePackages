<?php

namespace Sabre\DAVACL;

use Sabre\DAV;
use Sabre\HTTP;

class PrincipalTest extends \PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertTrue($principal instanceof Principal);
	}

	/**
	 * @expectedException Sabre\DAV\Exception
	 */
	public function testConstructNoUri()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, []);
	}

	public function testGetName()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame('admin', $principal->getName());
	}

	public function testGetDisplayName()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame('admin', $principal->getDisplayname());

		$principal = new Principal($principalBackend, [
			'uri'               => 'principals/admin',
			'{DAV:}displayname' => 'Mr. Admin'
		]);
		$this->assertSame('Mr. Admin', $principal->getDisplayname());
	}

	public function testGetProperties()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, [
			'uri'                                   => 'principals/admin',
			'{DAV:}displayname'                     => 'Mr. Admin',
			'{http://www.example.org/custom}custom' => 'Custom',
			'{http://sabredav.org/ns}email-address' => 'admin@example.org',
		]);

		$keys = [
			'{DAV:}displayname',
			'{http://www.example.org/custom}custom',
			'{http://sabredav.org/ns}email-address',
		];
		$props = $principal->getProperties($keys);

		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, $props);
		}

		$this->assertSame('Mr. Admin', $props['{DAV:}displayname']);

		$this->assertSame('admin@example.org', $props['{http://sabredav.org/ns}email-address']);
	}

	public function testUpdateProperties()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);

		$propPatch = new DAV\PropPatch(['{DAV:}yourmom' => 'test']);

		$result = $principal->propPatch($propPatch);
		$result = $propPatch->commit();
		$this->assertTrue($result);
	}

	public function testGetPrincipalUrl()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame('principals/admin', $principal->getPrincipalUrl());
	}

	public function testGetAlternateUriSet()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, [
			'uri'                                   => 'principals/admin',
			'{DAV:}displayname'                     => 'Mr. Admin',
			'{http://www.example.org/custom}custom' => 'Custom',
			'{http://sabredav.org/ns}email-address' => 'admin@example.org',
			'{DAV:}alternate-URI-set'               => [
				'mailto:admin+1@example.org',
				'mailto:admin+2@example.org',
				'mailto:admin@example.org',
			],
		]);

		$expected = [
			'mailto:admin+1@example.org',
			'mailto:admin+2@example.org',
			'mailto:admin@example.org',
		];

		$this->assertSame($expected, $principal->getAlternateUriSet());
	}

	public function testGetAlternateUriSetEmpty()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, [
			'uri' => 'principals/admin',
		]);

		$expected = [];

		$this->assertSame($expected, $principal->getAlternateUriSet());
	}

	public function testGetGroupMemberSet()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame([], $principal->getGroupMemberSet());
	}

	public function testGetGroupMembership()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame([], $principal->getGroupMembership());
	}

	public function testSetGroupMemberSet()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$principal->setGroupMemberSet(['principals/foo']);

		$this->assertSame([
			'principals/admin' => ['principals/foo'],
		], $principalBackend->groupMembers);
	}

	public function testGetOwner()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame('principals/admin', $principal->getOwner());
	}

	public function testGetGroup()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertNull($principal->getGroup());
	}

	public function testGetACl()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertSame([
			[
				'privilege' => '{DAV:}all',
				'principal' => '{DAV:}owner',
				'protected' => true,
			]
		], $principal->getACL());
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\Forbidden
	 */
	public function testSetACl()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$principal->setACL([]);
	}

	public function testGetSupportedPrivilegeSet()
	{
		$principalBackend = new PrincipalBackend\Mock();
		$principal = new Principal($principalBackend, ['uri' => 'principals/admin']);
		$this->assertNull($principal->getSupportedPrivilegeSet());
	}
}
