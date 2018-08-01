<?php

namespace Sabre\DAV;

class PropFindTest extends \PHPUnit_Framework_TestCase
{
	public function testHandle()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname']);
		$propFind->handle('{DAV:}displayname', 'foobar');

		$this->assertSame([
			200 => ['{DAV:}displayname' => 'foobar'],
			404 => [],
		], $propFind->getResultForMultiStatus());
	}

	public function testHandleCallBack()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname']);
		$propFind->handle('{DAV:}displayname', function () {
			return 'foobar';
		});

		$this->assertSame([
			200 => ['{DAV:}displayname' => 'foobar'],
			404 => [],
		], $propFind->getResultForMultiStatus());
	}

	public function testAllPropDefaults()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname'], 0, PropFind::ALLPROPS);

		$this->assertSame([
			200 => [],
		], $propFind->getResultForMultiStatus());
	}

	public function testSet()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname']);
		$propFind->set('{DAV:}displayname', 'bar');

		$this->assertSame([
			200 => ['{DAV:}displayname' => 'bar'],
			404 => [],
		], $propFind->getResultForMultiStatus());
	}

	public function testSetAllpropCustom()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname'], 0, PropFind::ALLPROPS);
		$propFind->set('{DAV:}customproperty', 'bar');

		$this->assertSame([
			200 => ['{DAV:}customproperty' => 'bar'],
		], $propFind->getResultForMultiStatus());
	}

	public function testSetUnset()
	{
		$propFind = new PropFind('foo', ['{DAV:}displayname']);
		$propFind->set('{DAV:}displayname', 'bar');
		$propFind->set('{DAV:}displayname', null);

		$this->assertSame([
			200 => [],
			404 => ['{DAV:}displayname' => null],
		], $propFind->getResultForMultiStatus());
	}
}
