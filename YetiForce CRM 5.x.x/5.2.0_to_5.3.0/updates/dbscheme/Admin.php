<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Admin extends \App\Db\Importers\Base
{
	public $dbType = 'admin';

	public function scheme()
	{
		$this->tables = [
			's_#__companies' => [
				'columns' => [
					'companysize' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'content' => $this->mediumText(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
