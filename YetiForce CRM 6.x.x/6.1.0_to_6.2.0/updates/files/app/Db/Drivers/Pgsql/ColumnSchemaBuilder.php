<?php
/**
 * Column schema builder file is the schema builder for PgSQL databases.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Db\Drivers\Pgsql;

/**
 * Column schema builder class is the schema builder for PgSQL databases.
 */
class ColumnSchemaBuilder extends \yii\db\ColumnSchemaBuilder
{
	use \App\Db\Drivers\ColumnSchemaBuilderTrait;
}
