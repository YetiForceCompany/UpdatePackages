<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'dav_calendarinstances' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10)->notNull(),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'principaluri' => $this->stringType(100),
					'access' => $this->smallInteger(1)->notNull()->defaultValue(1),
					'displayname' => $this->stringType(100),
					'uri' => $this->stringType(200),
					'description' => $this->text(),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'timezone' => $this->text(),
					'transparent' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'share_href' => $this->stringType(100),
					'share_displayname' => $this->stringType(100),
					'share_invitestatus' => $this->smallInteger(1)->notNull()->defaultValue(2),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(100),
					'access' => $this->tinyInteger(1)->notNull()->defaultValue(1),
					'uri' => $this->varbinary(200),
					'calendarcolor' => $this->varbinary(10),
					'transparent' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'share_href' => $this->varbinary(100),
					'share_invitestatus' => $this->tinyInteger(1)->notNull()->defaultValue(2),
				],
				'index' => [
					['principaluri', ['principaluri', 'uri'], true],
					['calendarid', ['calendarid', 'principaluri'], true],
					['calendarid_2', ['calendarid', 'share_href'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'u_#__file_upload_temp' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10)->notNull(),
					'name' => $this->stringType()->notNull(),
					'type' => $this->stringType(100),
					'path' => $this->text()->notNull(),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'fieldname' => $this->stringType(50),
					'crmid' => $this->integer(10),
					'createdtime' => $this->dateTime(),
					'key' => $this->stringType(100),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['key', 'key', true],
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'imagename' => $this->text(),
					'no_of_currency_decimals' => $this->stringType(1),
					'callduration' => $this->stringType(3),
					'othereventduration' => $this->stringType(3),
					'authy_methods' => $this->stringType(255)
				],
				'index' => [
					['email1', 'email1', true],
					['user_user_name_idx', 'user_name'],
					['user_user_password_idx', 'user_password'],
					['status', 'status'],
				],
				'primaryKeys' => [
					['users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__github' => [
				'columns' => [
					'github_id' => $this->primaryKey(10)->notNull(),
					'token' => $this->stringType(100), //
					'username' => $this->stringType(32),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactdetails' => [
				'columns' => [
					'imagename' => $this->text(),
					'reportsto' => $this->integer(11)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'index' => [
					['crmentity_label', 'label']
				],
				'primaryKeys' => [
					['crmentity_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'index' => [
					['crmentity_searchlabel_setype', ['searchlabel', 'setype']]
				],
				'primaryKeys' => [
					['crmentity_search_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'vtiger_blocks' => [
				'index' => [
					['block_tabid_idx', 'tabid'],
					['block_sequence_idx', 'sequence'],
				],
				'primaryKeys' => [
					['blocks_pk', 'blockid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'index' => [
					['name', 'name', true],
					['handler_file', 'handler_file', true],
					['vtiger_cron_task_status_idx', 'status'],
					['vtiger_cron_task_sequence_idx', 'sequence'],
				],
				'primaryKeys' => [
					['cron_task_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'index' => [
					['customview_entitytype_idx', 'entitytype'],
					['setdefault', ['setdefault', 'entitytype']],
					['customview_userid_idx', 'userid'],
				],
				'primaryKeys' => [
					['customview_pk', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvadvfilter' => [
				'index' => [
					['cvadvfilter_cvid_idx', 'cvid'],
					['cvadvfilter_groupid_idx', 'groupid'],
					['cvadvfilter_columnindex_idx', 'columnindex'],
				],
				'primaryKeys' => [
					['cvadvfilter_pk', ['cvid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'maximumlength' => $this->stringType(30)
				],
				'index' => [
					['field_tabid_idx', 'tabid'],
					['field_fieldname_idx', 'fieldname'],
					['field_block_idx', 'block'],
					['field_displaytype_idx', 'displaytype'],
					['tabid', ['tabid', 'tablename']],
					['quickcreate', 'quickcreate'],
					['presence', 'presence'],
					['tabid_2', ['tabid', 'fieldname']],
					['tabid_3', ['tabid', 'block']],
					['field_sequence_idx', 'sequence'], //
					['field_uitype_idx', 'uitype'], //
				],
				'primaryKeys' => [
					['field_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard_widgets' => [
				'columns' => [
					'position' => $this->text()
				],
				'index' => [
					['vtiger_module_dashboard_widgets_ibfk_1', 'templateid'],
					['userid', ['userid', 'active', 'module']],
					['vtiger_module_dashboard_widgets_linkid_idx', 'linkid'], //
					['vtiger_module_dashboard_widgets_dashboardid_idx', 'dashboardid'], //
					['vtiger_module_dashboard_widgets_module_idx', 'module'], //
				],
				'primaryKeys' => [
					['module_dashboard_widgets_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2field' => [
				'index' => [
					['profile2field_profileid_tabid_fieldname_idx', ['profileid', 'tabid']],
					['profile2field_tabid_profileid_idx', ['tabid', 'profileid']],
					['profile2field_visible_profileid_idx', ['visible', 'profileid']],
					['profile2field_readonly_idx', 'readonly'], //
				],
				'primaryKeys' => [
					['profile2field_pk', ['profileid', 'fieldid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activityregister' => [
				'columns' => [
					'activityregisterid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'activityregister_status' => $this->stringType()->defaultValue(''),
					'datasetregisterid' => $this->integer(11)->unsigned()->defaultValue(0),
					'start_date' => $this->date(),
					'end_date' => $this->date(),
					'activity_type' => $this->text(),
					'parent_id' => $this->integer(10),
				],
				'primaryKeys' => [
					['activityregister_pk', 'activityregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activityregistercf' => [
				'columns' => [
					'activityregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['activityregistercf_pk', 'activityregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__auditregister' => [
				'columns' => [
					'auditregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'locationregisterid' => $this->integer(11)->unsigned()->defaultValue(0),
					'datasetregisterid' => $this->integer(11)->unsigned()->defaultValue(0),
					'auditregister_status' => $this->stringType()->defaultValue(''),
					'auditregister_type' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['auditregister_pk', 'auditregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__auditregistercf' => [
				'columns' => [
					'auditregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['auditregistercf_pk', 'auditregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__datasetregister' => [
				'columns' => [
					'datasetregisterid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'datasetregister_status' => $this->stringType()->defaultValue(''),
					'legal_basis' => $this->text(),
					'scope_data' => $this->text(),
					'registered_dpo' => $this->smallInteger(1)->defaultValue(0),
					'data_submitted' => $this->smallInteger(1)->defaultValue(0),
					'internal_register' => $this->smallInteger(1)->defaultValue(0),
					'data_set_shared' => $this->smallInteger(1)->defaultValue(0),
					'added_to_register' => $this->date(),
					'removed_from_register' => $this->date(),
					'parent_id' => $this->integer(10)->notNull(),
				],
				'columns_mysql' => [
					'registered_dpo' => $this->tinyInteger(1)->defaultValue(0),
					'data_submitted' => $this->tinyInteger(1)->defaultValue(0),
					'internal_register' => $this->tinyInteger(1)->defaultValue(0),
					'data_set_shared' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['datasetregister_pk', 'datasetregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__datasetregistercf' => [
				'columns' => [
					'datasetregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['datasetregistercf_pk', 'datasetregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__incidentregister' => [
				'columns' => [
					'incidentregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'locationregisterid' => $this->integer(11)->unsigned()->defaultValue(0),
					'datasetregisterid' => $this->integer(11)->unsigned()->defaultValue(0),
					'incidentregister_status' => $this->stringType()->defaultValue(''),
					'incidentregister_type' => $this->stringType()->defaultValue(''),
					'incident_date' => $this->date(),
					'discovery_date' => $this->date(),
					'incident_report_date' => $this->date(),
					'incident_publication_date' => $this->date(),
					'peoplne_number' => $this->integer(9)->defaultValue(0),
					'breach_circumstances' => $this->text(),
					'breach_nature' => $this->text(),
					'possible_consequences' => $this->text(),
					'security_measures' => $this->text(),
				],
				'primaryKeys' => [
					['incidentregister_pk', 'incidentregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__incidentregistercf' => [
				'columns' => [
					'incidentregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['incidentregistercf_pk', 'incidentregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locationregister' => [
				'columns' => [
					'locationregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'parent_id' => $this->integer(11)->unsigned()->defaultValue(0),
					'locationregister_status' => $this->stringType()->defaultValue(''),
					'security_type' => $this->text(),
					'building_number' => $this->stringType(10)->defaultValue(''),
					'street' => $this->stringType()->defaultValue(''),
					'district' => $this->stringType()->defaultValue(''),
					'township' => $this->stringType()->defaultValue(''),
					'state' => $this->stringType()->defaultValue(''),
					'pobox' => $this->stringType(100)->defaultValue(''),
					'local_number' => $this->stringType(20)->defaultValue(''),
					'post_code' => $this->stringType(20)->defaultValue(''),
					'city' => $this->stringType(150)->defaultValue(''),
					'county' => $this->stringType(150)->defaultValue(''),
					'country' => $this->stringType(150)->defaultValue(''),
				],
				'primaryKeys' => [
					['locationregister_pk', 'locationregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locationregistercf' => [
				'columns' => [
					'locationregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['locationregistercf_pk', 'locationregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_actionmapping' => [
				'columns' => [
					'actionid' => $this->smallInteger(5)->unsigned()->notNull(),
					'actionname' => $this->stringType(200)->notNull(),
					'securitycheck' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'securitycheck' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['actionname', 'actionname'],
				],
				'primaryKeys' => [
					['actionmapping_pk', ['actionid', 'actionname']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_account' => [
				'columns' => [
					'siccode' => $this->stringType(255)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__emailtemplates' => [
				'columns' => [
					'email_template_priority' => $this->stringType(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity' => [
				'columns' => [
					'duration_minutes' => $this->stringType(3)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faq' => [
				'columns' => [
					'product_id' => $this->integer(11)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modcomments' => [
				'columns' => [
					'customer' => $this->integer(11)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproducts' => [
				'columns' => [
					'prodcount' => $this->integer(11)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager' => [
				'columns' => [
					'user' => $this->smallInteger(6)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entity_stats' => [
				'columns' => [
					'crmactivity' => $this->integer(8),
				],
				'columns_mysql' => [
					'presence' => 'mediumint(8)',
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendor' => [
				'columns' => [
					'vendorname' => $this->stringType(255),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['a_#__record_converter_fk_tab', 'a_#__record_converter', 'source_module', 'vtiger_tab', 'tabid', 'RESTRICT', 'RESTRICT'],
			['fk_1_u_#__activityregisteractivityregisterid', 'u_#__activityregister', 'activityregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__activityregistercfactivityregisterid', 'u_#__activityregistercf', 'activityregisterid', 'u_#__activityregister', 'activityregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__auditregisterauditregisterid', 'u_#__auditregister', 'auditregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__auditregistercfauditregisterid', 'u_#__auditregistercf', 'auditregisterid', 'u_#__auditregister', 'auditregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__datasetregisterdatasetregisterid', 'u_#__datasetregister', 'datasetregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__datasetregistercfdatasetregisterid', 'u_#__datasetregistercf', 'datasetregisterid', 'u_#__datasetregister', 'datasetregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__incidentregisterincidentregisterid', 'u_#__incidentregister', 'incidentregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__incidentregistercfincidentregisterid', 'u_#__incidentregistercf', 'incidentregisterid', 'u_#__incidentregister', 'incidentregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__locationregisterlocationregisterid', 'u_#__locationregister', 'locationregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__locationregistercflocationregisterid', 'u_#__locationregistercf', 'locationregisterid', 'u_#__locationregister', 'locationregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__multicompanymulticompanyid', 'u_#__multicompany', 'multicompanyid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__multicompanycfmulticompanyid', 'u_#__multicompanycf', 'multicompanyid', 'u_#__multicompany', 'multicompanyid', 'CASCADE', 'RESTRICT'],
		];
	}
}
