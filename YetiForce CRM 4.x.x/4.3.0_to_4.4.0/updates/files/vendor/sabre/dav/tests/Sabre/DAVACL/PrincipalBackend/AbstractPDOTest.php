<?php

namespace Sabre\DAVACL\PrincipalBackend;

use Sabre\DAV;
use Sabre\HTTP;

abstract class AbstractPDOTest extends \PHPUnit_Framework_TestCase
{
	use DAV\DbTestHelperTrait;

	public function setUp()
	{
		$this->dropTables(['principals', 'groupmembers']);
		$this->createSchema('principals');

		$pdo = $this->getPDO();

		$pdo->query("INSERT INTO principals (uri,email,displayname) VALUES ('principals/user','user@example.org','User')");
		$pdo->query("INSERT INTO principals (uri,email,displayname) VALUES ('principals/group','group@example.org','Group')");

		$pdo->query('INSERT INTO groupmembers (principal_id,member_id) VALUES (5,4)');
	}

	public function testConstruct()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);
		$this->assertTrue($backend instanceof PDO);
	}

	/**
	 * @depends testConstruct
	 */
	public function testGetPrincipalsByPrefix()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);

		$expected = [
			[
				'uri'                                   => 'principals/admin',
				'{http://sabredav.org/ns}email-address' => 'admin@example.org',
				'{DAV:}displayname'                     => 'Administrator',
			],
			[
				'uri'                                   => 'principals/user',
				'{http://sabredav.org/ns}email-address' => 'user@example.org',
				'{DAV:}displayname'                     => 'User',
			],
			[
				'uri'                                   => 'principals/group',
				'{http://sabredav.org/ns}email-address' => 'group@example.org',
				'{DAV:}displayname'                     => 'Group',
			],
		];

		$this->assertSame($expected, $backend->getPrincipalsByPrefix('principals'));
		$this->assertSame([], $backend->getPrincipalsByPrefix('foo'));
	}

	/**
	 * @depends testConstruct
	 */
	public function testGetPrincipalByPath()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);

		$expected = [
			'id'                                    => 4,
			'uri'                                   => 'principals/user',
			'{http://sabredav.org/ns}email-address' => 'user@example.org',
			'{DAV:}displayname'                     => 'User',
		];

		$this->assertSame($expected, $backend->getPrincipalByPath('principals/user'));
		$this->assertSame(null, $backend->getPrincipalByPath('foo'));
	}

	public function testGetGroupMemberSet()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);
		$expected = ['principals/user'];

		$this->assertSame($expected, $backend->getGroupMemberSet('principals/group'));
	}

	public function testGetGroupMembership()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);
		$expected = ['principals/group'];

		$this->assertSame($expected, $backend->getGroupMembership('principals/user'));
	}

	public function testSetGroupMemberSet()
	{
		$pdo = $this->getPDO();

		// Start situation
		$backend = new PDO($pdo);
		$this->assertSame(['principals/user'], $backend->getGroupMemberSet('principals/group'));

		// Removing all principals
		$backend->setGroupMemberSet('principals/group', []);
		$this->assertSame([], $backend->getGroupMemberSet('principals/group'));

		// Adding principals again
		$backend->setGroupMemberSet('principals/group', ['principals/user']);
		$this->assertSame(['principals/user'], $backend->getGroupMemberSet('principals/group'));
	}

	public function testSearchPrincipals()
	{
		$pdo = $this->getPDO();

		$backend = new PDO($pdo);

		$result = $backend->searchPrincipals('principals', ['{DAV:}blabla' => 'foo']);
		$this->assertSame([], $result);

		$result = $backend->searchPrincipals('principals', ['{DAV:}displayname' => 'ou']);
		$this->assertSame(['principals/group'], $result);

		$result = $backend->searchPrincipals('principals', ['{DAV:}displayname' => 'UsEr', '{http://sabredav.org/ns}email-address' => 'USER@EXAMPLE']);
		$this->assertSame(['principals/user'], $result);

		$result = $backend->searchPrincipals('mom', ['{DAV:}displayname' => 'UsEr', '{http://sabredav.org/ns}email-address' => 'USER@EXAMPLE']);
		$this->assertSame([], $result);
	}

	public function testUpdatePrincipal()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);

		$propPatch = new DAV\PropPatch([
			'{DAV:}displayname' => 'pietje',
		]);

		$backend->updatePrincipal('principals/user', $propPatch);
		$result = $propPatch->commit();

		$this->assertTrue($result);

		$this->assertSame([
			'id'                                    => 4,
			'uri'                                   => 'principals/user',
			'{DAV:}displayname'                     => 'pietje',
			'{http://sabredav.org/ns}email-address' => 'user@example.org',
		], $backend->getPrincipalByPath('principals/user'));
	}

	public function testUpdatePrincipalUnknownField()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);

		$propPatch = new DAV\PropPatch([
			'{DAV:}displayname' => 'pietje',
			'{DAV:}unknown'     => 'foo',
		]);

		$backend->updatePrincipal('principals/user', $propPatch);
		$result = $propPatch->commit();

		$this->assertFalse($result);

		$this->assertSame([
			'{DAV:}displayname' => 424,
			'{DAV:}unknown'     => 403
		], $propPatch->getResult());

		$this->assertSame([
			'id'                                    => '4',
			'uri'                                   => 'principals/user',
			'{DAV:}displayname'                     => 'User',
			'{http://sabredav.org/ns}email-address' => 'user@example.org',
		], $backend->getPrincipalByPath('principals/user'));
	}

	public function testFindByUriUnknownScheme()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);
		$this->assertNull($backend->findByUri('http://foo', 'principals'));
	}

	public function testFindByUri()
	{
		$pdo = $this->getPDO();
		$backend = new PDO($pdo);
		$this->assertSame(
			'principals/user',
			$backend->findByUri('mailto:user@example.org', 'principals')
		);
	}
}
