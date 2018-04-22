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
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
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
				'primaryKeys' => [
					['dav_calendarinstances_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'u_#__file_upload_temp' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
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
				'primaryKeys' => [
					['file_upload_temp_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'imagename' => $this->text()
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
			'vtiger_projectmilestone' => [
				'columns' => [
					'parentid' => $this->integer(10)
				],
				'index' => [
					['projectid', 'projectid'],
					['vtiger_projectmilestone_parentid_idx', 'parentid'],
				],
				'primaryKeys' => [
					['projectmilestone_pk', 'projectmilestoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__github' => [
				'columns' => [
					'github_id' => $this->integer(10)->autoIncrement()->notNull(),
					'token' => $this->stringType(100), //
					'username' => $this->stringType(32),
				],
				'primaryKeys' => [
					['github_pk', 'github_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactdetails' => [
				'columns' => [
					'imagename' => $this->text()
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
		];
		$this->foreignKey = [
			['a_#__record_converter_fk_tab', 'a_#__record_converter', 'source_module', 'vtiger_tab', 'tabid', 'RESTRICT', 'RESTRICT'],
		];
	}
}
