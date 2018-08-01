<?php

namespace Sabre\DAV\Browser;

class PropFindAllTest extends \PHPUnit_Framework_TestCase
{
	public function testHandleSimple()
	{
		$pf = new PropFindAll('foo');
		$pf->handle('{DAV:}displayname', 'foo');

		$this->assertSame(200, $pf->getStatus('{DAV:}displayname'));
		$this->assertSame('foo', $pf->get('{DAV:}displayname'));
	}

	public function testHandleCallBack()
	{
		$pf = new PropFindAll('foo');
		$pf->handle('{DAV:}displayname', function () {
			return 'foo';
		});

		$this->assertSame(200, $pf->getStatus('{DAV:}displayname'));
		$this->assertSame('foo', $pf->get('{DAV:}displayname'));
	}

	public function testSet()
	{
		$pf = new PropFindAll('foo');
		$pf->set('{DAV:}displayname', 'foo');

		$this->assertSame(200, $pf->getStatus('{DAV:}displayname'));
		$this->assertSame('foo', $pf->get('{DAV:}displayname'));
	}

	public function testSetNull()
	{
		$pf = new PropFindAll('foo');
		$pf->set('{DAV:}displayname', null);

		$this->assertSame(404, $pf->getStatus('{DAV:}displayname'));
		$this->assertSame(null, $pf->get('{DAV:}displayname'));
	}

	public function testGet404Properties()
	{
		$pf = new PropFindAll('foo');
		$pf->set('{DAV:}displayname', null);
		$this->assertSame(
			['{DAV:}displayname'],
			$pf->get404Properties()
		);
	}

	public function testGet404PropertiesNothing()
	{
		$pf = new PropFindAll('foo');
		$pf->set('{DAV:}displayname', 'foo');
		$this->assertSame(
			['{http://sabredav.org/ns}idk'],
			$pf->get404Properties()
		);
	}
}
