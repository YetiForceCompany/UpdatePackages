<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
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
	 *
	 * @param object $modulenode ,
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
		$this->updateDbSchema();
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
	 * Add tables
	 */
	public function renameTables()
	{
		$tables = [
			['u_#__mail_address_boock', 'u_#__mail_address_book']
		];
		$db = \App\Db::getInstance()->createCommand();
		foreach ($tables as $table) {
			$db->renameTable($table[0], $table[1])->execute();
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
		$db = \App\Db::getInstance()->createCommand();
		foreach ($tables as $key => $name) {
			$db->dropTable($name)->execute();
		}
	}

	/**
	 * Add columns
	 */
	public function addColumns()
	{
		$columns = [
			['u_#__announcement', 'is_mandatory', 'smallint'],
			['vtiger_igdn_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_igdnc_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_igin_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_igrnc_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_iidn_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_ipreorder_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_istdn_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_istn_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_istrn_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_knowledgebase_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_lin_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_lout_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_osstimecontrol_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_project', 'parentid', 'int(19) DEFAULT NULL'],
			['vtiger_reservations_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_scalculations_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_squoteenquiries_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_squotes_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_srecurringorders_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_srequirementscards_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_ssalesprocesses_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_ssingleorders_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_storage_status', 'picklist_valueid', 'integer NOT NULL DEFAULT "0"'],
			['vtiger_trees_templates', 'share', 'string DEFAULT NULL'],
		];
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->addColumn($column[0], $column[1], $column[2])->execute();
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
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->renameColumn($column[0], $column[1], $column[2])->execute();
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
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->alterColumn($column[0], $column[1], $column[2])->execute();
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
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->dropColumn($column[0], $column[1])->execute();
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
			['vtiger_troubletickets', 'ticket_no', 'ticket_no', false]
		];
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->createIndex($column[2], $column[0], $column[1], $column[3])->execute();
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
		$db = \App\Db::getInstance()->createCommand();
		foreach ($columns as $column) {
			$db->addForeignKey($column[2], $column[1], $column[1], $column[3], $column[4], $column[5], $column[6])->execute();
		}
	}
}
