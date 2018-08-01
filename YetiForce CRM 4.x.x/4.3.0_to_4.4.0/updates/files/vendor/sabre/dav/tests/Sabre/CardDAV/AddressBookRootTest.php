<?php

namespace Sabre\CardDAV;

use Sabre\DAVACL;

class AddressBookRootTest extends \PHPUnit_Framework_TestCase
{
	public function testGetName()
	{
		$pBackend = new DAVACL\PrincipalBackend\Mock();
		$cBackend = new Backend\Mock();
		$root = new AddressBookRoot($pBackend, $cBackend);
		$this->assertSame('addressbooks', $root->getName());
	}

	public function testGetChildForPrincipal()
	{
		$pBackend = new DAVACL\PrincipalBackend\Mock();
		$cBackend = new Backend\Mock();
		$root = new AddressBookRoot($pBackend, $cBackend);

		$children = $root->getChildren();
		$this->assertSame(3, count($children));

		$this->assertInstanceOf('Sabre\\CardDAV\\AddressBookHome', $children[0]);
		$this->assertSame('user1', $children[0]->getName());
	}
}
