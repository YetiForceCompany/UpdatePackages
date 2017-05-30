<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class
 */
class YetiForceUpdate
{

	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;

	/**
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * Constructor
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
		$this->filesToDelete = require_once('deleteFiles.php');
	}

	/**
	 * Preupdate
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Update
	 */
	public function update()
	{
		$db = App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$this->updateDbSchema();
		$this->updatePicklistType();
		$db->createCommand()->checkIntegrity(true)->execute();
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		return true;
	}

	/**
	 * Update
	 */
	public function updateDbSchema()
	{
		$this->dropTables();
		$this->createTables($this->getTables(1));
		$this->renameTables();
		$this->dropColumns();
		$this->addColumns();
		$this->renameColumns();
		$this->alterColumns();
		$this->dropColumns();
		$this->createIndex();
		$this->addForeignKey();
	}

	/**
	 * Create tables
	 * @param array $tables
	 */
	public function createTables($tables)
	{
		\App\Log::trace('Entering ' . __METHOD__ . ' tables: ' . print_r(array_keys($tables), true));
		$db = \App\Db::getInstance();

		$importer = new \App\Db\Importers\Base();
		$base = new \App\Db\Importer();
		$base->dieOnError = AppConfig::debug('SQL_DIE_ON_ERROR');
		foreach ($tables as $tableName => $data) {
			if (!$db->isTableExists($tableName)) {
				$importer->tables = [$tableName => $data];
				$base->addTables($importer);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Tables
	 * @param int $index
	 * @return array
	 */
	private function getTables($index)
	{
		$importer = new \App\Db\Importers\Base();
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					'a_#__smsnotifier_servers' => [
						'columns' => [
							'id' => $importer->primaryKey(),
							'providertype' => $importer->stringType(50)->notNull(),
							'isactive' => $importer->smallInteger(1),
							'api_key' => $importer->stringType(255)->notNull(),
							'parameters' => $importer->text()
						],
						'columns_mysql' => [
							'isactive' => "tinyint(1) DEFAULT '0'",
						],
						'engine' => 'InnoDB',
						'charset' => 'utf8'
					],
					's_#__smsnotifier_queue' => [
						'columns' => [
							'id' => $importer->primaryKey(),
							'message' => $importer->stringType(255)->notNull(),
							'tonumbers' => $importer->text()->notNull(),
							'records' => $importer->text()->notNull(),
							'module' => $importer->stringType(30)->notNull()
						],
						'engine' => 'InnoDB',
						'charset' => 'utf8'
					],
					'u_#__attachments' => [
						'columns' => [
							'attachmentid' => $importer->primaryKey(19),
							'name' => $importer->stringType(255)->notNull(),
							'type' => $importer->stringType(100),
							'path' => $importer->text()->notNull(),
							'status' => $importer->smallInteger(1)->defaultValue(0),
							'fieldid' => $importer->integer(19),
							'crmid' => $importer->integer(19),
							'createdtime' => $importer->datetime(),
						],
						'engine' => 'InnoDB',
						'charset' => 'utf8'
					],
					'u_#__browsinghistory' => [
						'columns' => [
							'id' => $importer->primaryKey(19),
							'userid' => $importer->integer(11)->notNull(),
							'date' => $importer->datetime(),
							'title' => $importer->stringType(255),
							'url' => $importer->text(),
						],
						'engine' => 'InnoDB',
						'charset' => 'utf8'
					]
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	/**
	 * Rename tables
	 */
	public function renameTables()
	{
		$tables = [
			['u_#__mail_address_boock', 'u_#__mail_address_book']
		];
		$this->renameTablesExecute($tables);
	}

	/**
	 * Renaming tables function
	 * @param array $tables
	 */
	public function renameTablesExecute($tables)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($tables as $table) {
			if ($db->isTableExists($table[0])) {
				$dbCommand->renameTable($table[0], $table[1])->execute();
			}
		}
	}

	/**
	 * delete tables
	 */
	public function dropTables()
	{
		$tables = [
			'vtiger_home_layout',
			'vtiger_homedashbd',
			'vtiger_homedefault',
			'vtiger_homemodule',
			'vtiger_homemoduleflds',
			'vtiger_homereportchart',
			'vtiger_homerss',
			'vtiger_homestuff',
			'vtiger_homestuff_seq',
			'vtiger_smsnotifier_servers',
			'vtiger_tracker'
		];
		$this->dropTablesExecute($tables);
		$table = 'vtiger_smsnotifier_status';
		if ($db->isTableExists($table) && !is_null($db->getSchema()->getTableSchema($table, true)->getColumn('smsmessageid'))) {
			$dbCommand->dropTable($table)->execute();
		}
	}

	/**
	 * Drop tables function
	 * @param array $tables
	 */
	public function dropTablesExecute($tables)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($tables as $name) {
			if ($db->isTableExists($name)) {
				$dbCommand->dropTable($name)->execute();
			}
		}
	}

	/**
	 * Add columns
	 */
	public function addColumns()
	{
		$columns = [
//				['u_#__announcement', 'is_mandatory', 'smallint'],
//				['vtiger_project', 'parentid', 'int(19) DEFAULT NULL'],
			['vtiger_trees_templates', 'share', 'string DEFAULT NULL'],
		];
		$this->addColumnsExecute($columns);
	}

	/**
	 * add columns function
	 * @param array $columns
	 */
	public function addColumnsExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($tables as $name) {
			if ($db->isTableExists($name)) {
				$dbCommand->dropTable($name)->execute();
			}
		}
	}

	/**
	 * Changing name of columns
	 */
	public function renameColumns()
	{
		$columns = [
			['s_#_mail_smtp', 'replay_to', 'reply_to'],
			['vtiger_smsnotifier', 'status', 'smsnotifier_status'],
		];
		$this->renameColumnsExecute($columns);
	}

	/**
	 * rename columns function
	 * @param array $columns
	 */
	public function renameColumnsExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($columns as $column) {
			if ($db->isTableExists($column[0]) && !is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[1])) && is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[2]))) {
				$dbCommand->renameColumn($column[0], $column[1], $column[2])->execute();
			}
		}
	}

	/**
	 * alter columns
	 */
	public function alterColumns()
	{
		$columns = [
			['a_#__bruteforce_blocked', 'time', 'timestamp NULL DEFAULT NULL'],
			['s_#__mail_queue', 'from', 'text'],
			['s_#__mail_queue', 'to', 'text'],
			['u_#__favorites', 'data', 'timestamp NULL DEFAULT NULL'],
			['vtiger_currency_info', 'defaultid', 'smallint'],
			['vtiger_import_maps', 'date_entered', 'timestamp NULL DEFAULT NULL'],
			['vtiger_import_maps', 'date_modified', 'timestamp NULL DEFAULT NULL'],
			['vtiger_loginhistory', 'login_time', 'timestamp NOT NULL DEFAULT "0000-00-00 00:00:00"'],
			['vtiger_ossmails_logs', 'start_time', 'timestamp NULL DEFAULT NULL'],
			['vtiger_ossmailscanner_folders_uid', 'user_id', 'int(10) unsigned DEFAULT NULL'],
			['vtiger_ossmailscanner_log_cron', 'created_time', 'timestamp NULL DEFAULT NULL'],
			['vtiger_scheduled_reports', 'next_trigger_time', 'timestamp NULL DEFAULT NULL'],
			['vtiger_schedulereports', 'next_trigger_time', 'timestamp NULL DEFAULT NULL'],
			['vtiger_smsnotifier', 'smsnotifier_status', 'string DEFAULT NULL'],
			['vtiger_trees_templates_data', 'state', "varchar(100) NOT NULL DEFAULT ''"],
			['vtiger_users', 'date_entered', 'timestamp NULL DEFAULT NULL'],
			['vtiger_users', 'date_modified', 'timestamp NULL DEFAULT NULL'],
			['yetiforce_updates', 'time', 'timestamp NULL DEFAULT NULL'],
		];
		$this->alterColumnsExecute($columns);
	}

	/**
	 * alter columns function
	 * @param array $columns
	 */
	public function alterColumnsExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($columns as $column) {
			if ($db->isTableExists($column[0]) && !is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[1])) && is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[2]))) {
				$dbCommand->alterColumn($column[0], $column[1], $column[2])->execute();
			}
		}
	}

	/**
	 * alter columns
	 */
	public function dropColumns()
	{
		$columns = [
			['vtiger_fixed_assets_fuel_type', 'picklist_valueid'],
			['vtiger_users', 'user_hash'],
		];
		$this->dropColumnsExecute($columns);
	}

	/**
	 * drop columns function
	 * @param array $columns
	 */
	public function dropColumnsExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($columns as $column) {
			if ($db->isTableExists($column[0]) && !is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[1]))) {
				$dbCommand->dropColumn($column[0], $column[1])->execute();
			}
		}
	}

	/**
	 * alter columns
	 */
	public function createIndex()
	{
		$columns = [
			['u_#__ssalesprocesses', 'ssalesprocesses_no', 'ssalesprocesses_no', false],
			['vtiger_currency_info', 'deleted', 'deleted', false],
			['vtiger_ossmailscanner_folders_uid', 'user_id', 'user_id', false],
			['vtiger_ossmailscanner_folders_uid', 'folder', 'folder', false],
			['vtiger_ossmailview', 'verify', 'verify', false],
			['vtiger_ossmailview', 'message_id', 'message_id', false],
			['vtiger_ossmailview', 'mbox', 'mbox', false],
			['vtiger_project', 'project_parentid_idx', 'parentid', false],
			['vtiger_project', 'project_no', 'project_no', false],
			['vtiger_troubletickets', 'ticket_no', 'ticket_no', false],
			['u_#__browsinghistory', 'userid', 'browsinghistory_user_idx', false]
		];
		$this->createIndexExecute($columns);
	}

	/**
	 * create index function
	 * @param array $columns
	 */
	public function createIndexExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($columns as $column) {
			if ($db->isTableExists($column[0]) && !is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[1]))) {
				$dbCommand->createIndex($column[2], $column[0], $column[1], $column[3])->execute();
			}
		}
	}

	/**
	 * add foreign key
	 */
	public function addForeignKey()
	{
		$columns = [
			['vtiger_ossmailscanner_folders_uid_ibfk_1', 'user_id', 'vtiger_ossmailscanner_folders_uid', 'roundcube_users', 'user_id', 'CASCADE', null],
		];
		$this->addForeignKeyExecute($columns);
	}

	/**
	 * create index function
	 * @param array $columns
	 */
	public function addForeignKeyExecute($columns)
	{
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($columns as $column) {
			if ($db->isTableExists($column[0]) && !is_null($db->getSchema()->getTableSchema($column[0], true)->getColumn($column[1]))) {
				$dbCommand->addForeignKey($column[2], $column[1], $column[1], $column[3], $column[4], $column[5], $column[6])->execute();
			}
		}
	}

	/**
	 * Update picklist type
	 */
	public function updatePicklistType()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = App\Db::getInstance();
		$schema = $db->getSchema();
		$query = (new \App\Db\Query())->from('vtiger_field')
			->where(['uitype' => 16])
			->andWhere(['fieldname' => [
				'osstimecontrol_status',
				'lin_status',
				'lout_status',
				'reservations_status',
				'squoteenquiries_status',
				'srequirementscards_status',
				'scalculations_status',
				'squotes_status',
				'ssingleorders_status',
				'srecurringorders_status',
				'storage_status',
				'ssalesprocesses_status',
				'igrn_status',
				'igdn_status',
				'iidn_status',
				'igin_status',
				'ipreorder_status',
				'istdn_status',
				'istn_status',
				'istrn_status',
				'knowledgebase_status',
				'igrnc_status',
				'igdnc_status',
//				'notification_status',
				'svendorenquiries_status'
		]]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$picklistTable = 'vtiger_' . $row['fieldname'];
			if ($db->isTableExists($picklistTable) && is_null($db->getSchema()->getTableSchema($picklistTable, true)->getColumn('picklist_valueid'))) {
				$db->createCommand()->addColumn($picklistTable, 'picklist_valueid', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 11)->notNull()->defaultValue(0))->execute();
				$db->createCommand()->insert('vtiger_picklist', ['name' => $row['fieldname']])->execute();
				$newPicklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $row['fieldname']])->scalar();
				if (!$newPicklistId) {
					$newPicklistId = $db->getLastInsertID('vtiger_picklist_picklistid_seq');
				}

				$identifier = $row['fieldname'] . 'id';
				$query2 = (new \App\Db\Query())->select([$identifier, 'sortorderid'])->from($picklistTable);
				$dataReader2 = $query2->createCommand()->query();
				while ($picklistRow = $dataReader2->read()) {
					$newPicklistValueId = $db->getUniqueID('vtiger_picklistvalues');
					$db->createCommand()->update($picklistTable, ['picklist_valueid' => $newPicklistValueId], [$identifier => $picklistRow[$identifier]])->execute();

					$query = (new \App\Db\Query)->select('roleid')->from('vtiger_role');
					$roleIds = $query->column();
					$insertedData = [];
					foreach ($roleIds as &$value) {
						$insertedData [] = [$value, $newPicklistValueId, $newPicklistId, $picklistRow['sortorderid']];
					}
					$db->createCommand()
						->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid', 'sortid'], $insertedData)
						->execute();
				}
				$db->createCommand()->update('vtiger_field', ['uitype' => 15], ['fieldid' => $row['fieldid']])->execute();
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}
}
