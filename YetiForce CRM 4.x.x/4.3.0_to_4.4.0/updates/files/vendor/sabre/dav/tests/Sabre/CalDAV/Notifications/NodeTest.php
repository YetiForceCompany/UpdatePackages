<?php

namespace Sabre\CalDAV\Notifications;

use Sabre\CalDAV;

class NodeTest extends \PHPUnit_Framework_TestCase
{
	protected $systemStatus;
	protected $caldavBackend;

	public function getInstance()
	{
		$principalUri = 'principals/user1';

		$this->systemStatus = new CalDAV\Xml\Notification\SystemStatus(1, '"1"');

		$this->caldavBackend = new CalDAV\Backend\MockSharing([], [], [
			'principals/user1' => [
				$this->systemStatus
			]
		]);

		$node = new Node($this->caldavBackend, 'principals/user1', $this->systemStatus);
		return $node;
	}

	public function testGetId()
	{
		$node = $this->getInstance();
		$this->assertSame($this->systemStatus->getId() . '.xml', $node->getName());
	}

	public function testGetEtag()
	{
		$node = $this->getInstance();
		$this->assertSame('"1"', $node->getETag());
	}

	public function testGetNotificationType()
	{
		$node = $this->getInstance();
		$this->assertSame($this->systemStatus, $node->getNotificationType());
	}

	public function testDelete()
	{
		$node = $this->getInstance();
		$node->delete();
		$this->assertSame([], $this->caldavBackend->getNotificationsForPrincipal('principals/user1'));
	}

	public function testGetGroup()
	{
		$node = $this->getInstance();
		$this->assertNull($node->getGroup());
	}

	public function testGetACL()
	{
		$node = $this->getInstance();
		$expected = [
			[
				'privilege' => '{DAV:}all',
				'principal' => '{DAV:}owner',
				'protected' => true,
			],
		];

		$this->assertSame($expected, $node->getACL());
	}

	/**
	 * @expectedException \Sabre\DAV\Exception\Forbidden
	 */
	public function testSetACL()
	{
		$node = $this->getInstance();
		$node->setACL([]);
	}

	public function testGetSupportedPrivilegeSet()
	{
		$node = $this->getInstance();
		$this->assertNull($node->getSupportedPrivilegeSet());
	}
}
