<?php

namespace Importers;

/**
 * Class that imports webservice database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Webservice extends \App\Db\Importers\Base
{
	public $dbType = 'webservice';

	public function scheme()
	{
		$this->tables = [
			'w_#__portal_user' => [
				'columns' => [
					'password_t' => $this->stringType(500),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__servers' => [
				'columns' => [
					'pass' => $this->stringType(500),
					'api_key' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
		];
	}
}
