<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Admin extends \App\Db\Importers\Base
{

	public $dbType = 'admin';

	public function scheme()
	{
		$this->tables = [
			'a_#__record_converter' => [
				'columns' => [
					'id' => $this->smallInteger(10)->notNull(),
					'name' => $this->stringType(),
					'status' => $this->smallInteger(1),
					'source_module' => $this->smallInteger(5)->notNull(),
					'destiny_module' => $this->stringType()->notNull(),
					'field_merge' => $this->stringType(50),
					'field_mappging' => $this->text(),
					'inv_field_mapping' => $this->text(),
					'redirect_to_edit' => $this->smallInteger(1),
					'change_view' => $this->smallInteger(5),
					'check_duplicate' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1),
					'redirect_to_edit' => $this->tinyInteger(1),
					'check_duplicate' => $this->tinyInteger(1),
				],
				'index' => [
					['a_yf_record_converter_fk_tab', 'source_module'],
				],
				'primaryKeys' => [
					['record_converter_pk', ['id', 'source_module', 'destiny_module']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__batchmethod' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(11)->notNull(),
					'method' => $this->stringType(50)->notNull(),
					'params' => $this->text()->notNull(),
					'created_time' => $this->date()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull(),
					'userid' => $this->integer(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__address_finder' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(11)->notNull(),
					'name' => $this->stringType(),
					'level1' => $this->stringType(100),
					'level2' => $this->stringType(100),
					'level3' => $this->stringType(100),
					'level4' => $this->stringType(100),
					'level5' => $this->stringType(100),
					'level6' => $this->stringType(100),
					'level7' => $this->stringType(100),
					'level8' => $this->stringType(100),
					'source' => $this->stringType(10),
				],
				'index' => [
					['source', 'source'],
					['name', 'name'],
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			's_#__address_finder_config' => [
				'columns' => [
					'id' => $this->smallInteger(4)->unsigned()->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'type' => $this->stringType(50)->notNull(),
					'val' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['address_finder_config_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_global' => [
				'columns' => [
					'default' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'default' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
