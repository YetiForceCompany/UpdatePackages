<?php
/**
 * Basic database structure file.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Importers;

/**
 * Basic database structure class.
 */
class Base extends \App\Db\Importers\Base
{
	/** {@inheritdoc} */
	public $dbType = 'base';

	/**
	 * Scheme.
	 *
	 * @return void
	 */
	public function scheme(): void
	{
		$this->tables = [
			'a_#__pdf' => [
				'columns' => [
					'generator' => $this->stringType(50)->notNull()->defaultValue('YetiForcePDF')->after('pdfid'),
					'styles' => $this->text()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__smsnotifier_servers' => [
				'columns' => [
					'name' => $this->stringType(50)->notNull()->defaultValue('')->after('id'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__picklist_dependency' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'tabid' => $this->smallInteger(5)->notNull(),
					'source_field' => $this->integer(10),
				],
				'index' => [
					['s_yf_picklist_dependency_source_field_fk', 'source_field'],
					['s_yf_picklist_dependency_tabid_source_field_fk', ['tabid', 'source_field']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__picklist_dependency_data' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'source_id' => $this->integer(10)->notNull(),
					'conditions' => $this->text(),
				],
				'index' => [
					['s_yf_picklist_dependency_data_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflows' => [
				'columns' => [
					'sequence' => $this->smallInteger(5)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks' => [
				'columns' => [
					'sequence' => $this->smallInteger(5)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'i_#__wapro' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType(50)->notNull(),
					'server' => $this->stringType(),
					'port' => $this->smallInteger(5)->unsigned(),
					'database' => $this->stringType(),
					'username' => $this->stringType(),
					'password' => $this->stringType(500),
					'synchronizer' => $this->stringType(500),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__file_upload' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'name' => $this->stringType()->notNull(),
					'type' => $this->stringType(100)->notNull(),
					'ext' => $this->stringType(50)->notNull(),
					'path' => $this->text()->notNull(),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'fieldname' => $this->stringType(50)->defaultValue(''),
					'createdtime' => $this->dateTime(),
					'key' => $this->stringType(100)->notNull(),
					'user' => $this->integer(10)->notNull(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['u_yf_file_upload_key_uidx', 'key', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__relations_members_entity' => [
				'columns' => [
					'rel_created_user' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_cache' => [
				'columns' => [
					'user_id' => $this->integer(10)->notNull(),
					'module_name' => $this->stringType(25)->notNull(),
					'crmids' => $this->integer(10)->notNull(),
				],
				'index' => [
					['u_yf_openstreetmap_cache_crmids_idx', 'crmids'],
					['u_yf_openstreetmap_cache_module_name_idx', 'module_name']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentity' => [
				'index' => [
					['vtiger_crmentity_deleted_private_smownerid_idx', ['deleted', 'private', 'smownerid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'columns' => [
					'advanced_conditions' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvcolumnlist' => [
				'columns' => [
					'label' => $this->stringType(50)->defaultValue(''),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'fieldparams' => $this->stringType(500)->defaultValue('')
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2grouprel' => [
				'columns' => [
					'containsgroupid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2rs' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_groups' => [
				'columns' => [
					'parentid' => $this->integer(10)->unsigned()->defaultValue(0)
				],
				'index' => [
					['vtiger_groups_parentid_idx', 'parentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_links' => [
				'columns' => [
					'params' => $this->stringType(500),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview' => [
				'index' => [
					['ossmailview_cid_idx', 'cid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'user_password' => $this->stringType(),
					'records_limit' => $this->integer(10)->unsigned(),
					'confirm_password' => $this->stringType(),
					'calendar_all_users_by_default' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'calendar_all_users_by_default' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_menu' => [
				'columns' => [
					'countentries' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'countentries' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__users_pinned' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'user_id' => ['type' => $this->integer(10)->notNull(), 'renameFrom' => 'owner_id'],
					'tabid' => $this->smallInteger(5)->notNull()->defaultValue(\App\Module::getModuleId('Calendar')),
					'fav_id' => ['type' => $this->integer(10)->unsigned()->notNull()->after('tabid'), 'renameFrom' => 'fav_element_id'],
				],
				'index' => [
					['u_yf_users_pinned_user_id_idx', 'user_id'],
					['u_yf_users_pinned_tabid_idx', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassets' => [
				'columns' => [
					'purchase_price' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'actual_price' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'timing_change' => ['type' => $this->integer(10)->unsigned()->defaultValue(0), 'mode' => 1],
					'oil_change' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'fuel_consumption' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'current_odometer_reading' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'number_repair' => ['type' => $this->smallInteger(5)->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__incidentregister' => [
				'columns' => [
					'peoplne_number' => ['type' => $this->integer(9)->unsigned()->defaultValue(0), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locations' => [
				'columns' => [
					'capacity' => ['type' => $this->integer(8)->unsigned()->defaultValue(0), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_account' => [
				'columns' => [
					'employees' => ['type' => $this->integer(10)->unsigned()->defaultValue(0), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistory' => [
				'columns' => [
					'duration' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproducts' => [
				'columns' => [
					'prodcount' => $this->integer(10)->unsigned()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaign' => [
				'columns' => [
					'expectedrevenue' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'budgetcost' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'actualcost' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'numsent' => ['type' => $this->decimal('11,0')->unsigned(), 'mode' => 1],
					'targetsize' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'expectedresponsecount' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'expectedsalescount' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'expectedroi' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
					'actualresponsecount' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'actualsalescount' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'actualroi' => ['type' => $this->decimal('28,8')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__occurrences' => [
				'columns' => [
					'participants' => ['type' => $this->integer(8)->unsigned()->defaultValue(0), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_leaddetails' => [
				'columns' => [
					'noofemployees' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_products' => [
				'columns' => [
					'weight' => ['type' => $this->decimal('11,3')->unsigned(), 'mode' => 1],
					'commissionrate' => ['type' => $this->decimal('8,2'), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_project' => [
				'columns' => [
					// 'targetbudget' => ['type' => $this->integer(10)->unsigned(), 'mode' => 1],
					'estimated_work_time' => ['type' => $this->decimal('15,2')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_projectmilestone' => [
				'columns' => [
					'estimated_work_time' => ['type' => $this->decimal('15,2')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_projecttask' => [
				'columns' => [
					'estimated_work_time' => ['type' => $this->decimal('8,2')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_servicecontracts' => [
				'columns' => [
					'total_units' => ['type' => $this->decimal('5,2')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_service' => [
				'columns' => [
					'commissionrate' => ['type' => $this->decimal('8,2'), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__cmileagelogbook' => [
				'columns' => [
					'number_kilometers' => ['type' => $this->decimal('13,2')->unsigned(), 'mode' => 1],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_trees_templates' => [
				'columns' => [
					'templateid' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'tabid' => ['type' => $this->smallInteger(5)->notNull(), 'renameFrom' => 'module']
				],
				'index' => [
					['module', 'tabid'],
				],
				'primaryKeys' => [
					['trees_templates_pk', 'templateid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__mail' => [
				'columns' => [
					'content' => $this->mediumText(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__servers' => [
				'index' => [
					['w_yf_servers_api_key_idx', 'api_key'],
					['w_yf_servers_name_type_idx', ['type', 'name']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__sms_user' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'token' => $this->char(64)->notNull(),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'language' => $this->stringType(10),
					'user_id' => $this->integer(10),
					'custom_params' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['w_yf_sms_user_server_id_token_uidx', ['server_id', 'token']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['s_#__picklist_dependency_source_field_fk', 's_#__picklist_dependency', 'source_field', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['s_#__picklist_dependency_tabid_fk', 's_#__picklist_dependency', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['s_#__picklist_dependency_data_id_fk', 's_#__picklist_dependency_data', 'id', 's_#__picklist_dependency', 'id', 'CASCADE', null],
			['u_#__openstreetmap_cache_ibfk_1', 'u_#__openstreetmap_cache', 'user_id', 'vtiger_users', 'id', 'CASCADE', null],
			['u_#__openstreetmap_cache_ibfk_2', 'u_#__openstreetmap_cache', 'crmids', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['u_#__openstreetmap_cache_ibfk_3', 'u_#__openstreetmap_cache', 'module_name', 'vtiger_tab', 'name', 'CASCADE', null],
			['vtiger_group2grouprel_containsgroupid_fk', 'vtiger_group2grouprel', 'containsgroupid', 'vtiger_groups', 'groupid', 'CASCADE', null],
			['vtiger_group2role_groupid_fk', 'vtiger_group2role', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', null],
			['vtiger_group2rs_groupid_fk', 'vtiger_group2rs', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', null],
			['vtiger_trees_templates_tabid_fk', 'vtiger_trees_templates', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['vtiger_trees_templates_data_templateid', 'vtiger_trees_templates_data', 'templateid', 'vtiger_trees_templates', 'templateid', 'CASCADE', null],
			['vtiger_users2group_groupid_fk', 'vtiger_users2group', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', null],
			['u_#__users_pinned_ibfk_1', 'u_#__users_pinned', 'user_id', 'vtiger_users', 'id', 'CASCADE', null],
			['u_#__users_pinned_ibfk_2', 'u_#__users_pinned', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['w_#__sms_user_server_id_fk', 'w_#__sms_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', null],
		];
	}
}
