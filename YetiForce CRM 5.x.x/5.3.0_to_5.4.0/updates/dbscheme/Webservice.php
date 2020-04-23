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
		];
		$this->foreignKey = [
		];
	}
}
