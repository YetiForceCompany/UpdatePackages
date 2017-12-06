<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base3 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_osspasswords' => [
				'index' => [
					['linkto', 'linkto'],
					['linkextend', 'linkextend'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
