<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base1 extends \DbType
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'com_vtiger_workflow_activatedonce' => [
				'columns' => [
					'workflow_id' => $this->integer(10)->notNull(),
					'entity_id' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['workflow_activatedonce_pk', ['workflow_id', 'entity_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'tasktypename' => $this->stringType()->notNull(),
					'label' => $this->stringType(),
					'classname' => $this->stringType(),
					'classpath' => $this->stringType(),
					'templatepath' => $this->stringType(),
					'modules' => $this->stringType(500),
					'sourcemodule' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflows' => [
				'columns' => [
					'workflow_id' => $this->integer(10)->autoIncrement()->notNull(),
					'module_name' => $this->stringType(100),
					'summary' => $this->stringType(400)->notNull(),
					'test' => $this->text(),
					'execution_condition' => $this->integer(10)->notNull(),
					'defaultworkflow' => $this->integer(1),
					'type' => $this->stringType(),
					'filtersavedinnew' => $this->integer(1),
					'schtypeid' => $this->integer(10),
					'schdayofmonth' => $this->stringType(100),
					'schdayofweek' => $this->stringType(100),
					'schannualdates' => $this->stringType(100),
					'schtime' => $this->stringType(50),
					'nexttrigger_time' => $this->dateTime(),
				],
				'primaryKeys' => [
					['workflows_pk', 'workflow_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtask_queue' => [
				'columns' => [
					'task_id' => $this->integer(10),
					'entity_id' => $this->stringType(100),
					'do_after' => $this->integer(10),
					'task_contents' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks' => [
				'columns' => [
					'task_id' => $this->integer(10)->autoIncrement()->notNull(),
					'workflow_id' => $this->integer(10),
					'summary' => $this->stringType(400)->notNull(),
					'task' => $this->text(),
				],
				'primaryKeys' => [
					['workflowtasks_pk', 'task_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => [
					'workflowtasks_entitymethod_id' => $this->integer(10)->notNull(),
					'module_name' => $this->stringType(100),
					'method_name' => $this->stringType(100),
					'function_path' => $this->stringType(400),
					'function_name' => $this->stringType(100),
				],
				'primaryKeys' => [
					['workflowtasks_entitymethod_pk', 'workflowtasks_entitymethod_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtemplates' => [
				'columns' => [
					'template_id' => $this->integer(10)->autoIncrement()->notNull(),
					'module_name' => $this->stringType(100),
					'title' => $this->stringType(400),
					'template' => $this->text(),
				],
				'primaryKeys' => [
					['workflowtemplates_pk', 'template_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_addressbookchanges' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'uri' => $this->stringType(200)->notNull(),
					'synctoken' => $this->integer(10)->unsigned()->notNull(),
					'addressbookid' => $this->integer(10)->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'operation' => $this->tinyInteger(1)->notNull(),
				],
				'primaryKeys' => [
					['dav_addressbookchanges_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_addressbooks' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'principaluri' => $this->stringType(),
					'displayname' => $this->stringType(),
					'uri' => $this->stringType(200),
					'description' => $this->text(),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(),
					'uri' => $this->varbinary(200),
				],
				'index' => [
					['principaluri', ['principaluri', 'uri'], true],
					['dav_addressbooks_idx', 'principaluri'],
				],
				'index_mysql' => [
					['principaluri', ['principaluri(100)', 'uri(100)'], true],
					['dav_addressbooks_idx', 'principaluri(100)'],
				],
				'primaryKeys' => [
					['dav_addressbooks_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarchanges' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'uri' => $this->stringType(200)->notNull(),
					'synctoken' => $this->integer(10)->unsigned()->notNull(),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'operation' => $this->tinyInteger(1)->notNull(),
				],
				'primaryKeys' => [
					['dav_calendarchanges_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarobjects' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
					'componenttype' => $this->stringType(8),
					'firstoccurence' => $this->integer(10)->unsigned(),
					'lastoccurence' => $this->integer(10)->unsigned(),
					'uid' => $this->stringType(200),
					'crmid' => $this->integer(10),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
					'componenttype' => $this->varbinary(8),
					'uid' => $this->varbinary(200),
				],
				'primaryKeys' => [
					['dav_calendarobjects_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendars' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'principaluri' => $this->stringType(100),
					'displayname' => $this->stringType(100),
					'uri' => $this->stringType(200),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'description' => $this->text(),
					'calendarorder' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'timezone' => $this->text(),
					'components' => $this->stringType(21),
					'transparent' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(100),
					'uri' => $this->varbinary(200),
					'calendarcolor' => $this->varbinary(10),
					'components' => $this->varbinary(21),
					'transparent' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['dav_calendars_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarsubscriptions' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'uri' => $this->stringType(200)->notNull(),
					'principaluri' => $this->stringType(100)->notNull(),
					'source' => $this->text(),
					'displayname' => $this->stringType(100),
					'refreshrate' => $this->stringType(10),
					'calendarorder' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'striptodos' => $this->smallInteger(1),
					'stripalarms' => $this->smallInteger(1),
					'stripattachments' => $this->smallInteger(1),
					'lastmodified' => $this->integer(10)->unsigned(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'principaluri' => $this->varbinary(100)->notNull(),
					'calendarcolor' => $this->varbinary(10),
					'striptodos' => $this->tinyInteger(1),
					'stripalarms' => $this->tinyInteger(1),
					'stripattachments' => $this->tinyInteger(1),
				],
				'primaryKeys' => [
					['dav_calendarsubscriptions_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_cards' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'addressbookid' => $this->integer(10)->unsigned()->notNull(),
					'carddata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
					'crmid' => $this->integer(10)->defaultValue(0),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
				],
				'primaryKeys' => [
					['dav_cards_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_principals' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'uri' => $this->stringType(200)->notNull(),
					'email' => $this->stringType(80),
					'displayname' => $this->stringType(80),
					'userid' => $this->integer(10),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'email' => $this->varbinary(80),
				],
				'primaryKeys' => [
					['dav_principals_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_schedulingobjects' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'principaluri' => $this->stringType(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(),
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
				],
				'primaryKeys' => [
					['dav_schedulingobjects_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_users' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50),
					'digesta1' => $this->stringType(32),
					'userid' => $this->integer(10)->unsigned(),
					'key' => $this->stringType(50),
				],
				'columns_mysql' => [
					'username' => $this->varbinary(50),
					'digesta1' => $this->varbinary(32),
				],
				'index' => [
					['username', 'username', true],
					['userid', 'userid', true],
				],
				'index_mysql' => [
					['username', 'username(50)', true],
				],
				'primaryKeys' => [
					['dav_users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'roundcube_cache_messages' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'mailbox' => $this->stringType()->notNull(),
					'uid' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
					'flags' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['roundcube_cache_messages_pk', ['user_id', 'mailbox', 'uid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(128)->notNull(),
					'mail_host' => $this->stringType(128)->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'last_login' => $this->dateTime(),
					'failed_login' => $this->dateTime(),
					'failed_login_counter' => $this->integer(10)->unsigned(),
					'language' => $this->stringType(5),
					'preferences' => $this->text(),
					'actions' => $this->text(),
					'password' => $this->stringType(200),
					'crm_user_id' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['roundcube_users_pk', 'user_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users_autologin' => [
				'columns' => [
					'rcuser_id' => $this->integer(10)->unsigned()->notNull(),
					'crmuser_id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activity_invitation' => [
				'columns' => [
					'inviteesid' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'activityid' => $this->integer(10)->notNull(),
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'email' => $this->stringType(100)->notNull()->defaultValue(''),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'time' => $this->dateTime(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['activity_invitation_pk', 'inviteesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
					'title' => $this->stringType(),
					'announcement_no' => $this->stringType(),
					'subject' => $this->stringType(),
					'announcementstatus' => $this->stringType()->notNull()->defaultValue(''),
					'interval' => $this->smallInteger(5),
					'is_mandatory' => $this->smallInteger(5),
				],
				'primaryKeys' => [
					['announcement_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement_mark' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['announcement_mark_pk', ['announcementid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcementcf' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['announcementcf_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__attachments' => [
				'columns' => [
					'attachmentid' => $this->primaryKey(10),
					'name' => $this->stringType()->notNull(),
					'type' => $this->stringType(100),
					'path' => $this->text()->notNull(),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'fieldid' => $this->integer(10),
					'crmid' => $this->integer(10),
					'createdtime' => $this->dateTime(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__browsinghistory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'userid' => $this->integer(10)->notNull(),
					'date' => $this->dateTime(),
					'title' => $this->stringType(),
					'url' => $this->text(),
				],
				'index' => [
					['browsinghistory_user_idx', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassets' => [
				'columns' => [
					'cfixedassetsid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'fixed_assets_type' => $this->stringType(),
					'fixed_assets_status' => $this->stringType(),
					'producent_designation' => $this->stringType(),
					'additional_designation' => $this->stringType(),
					'internal_designation' => $this->stringType(),
					'date_production' => $this->date(),
					'date_acquisition' => $this->date(),
					'purchase_price' => $this->decimal('25,8'),
					'actual_price' => $this->decimal('25,8'),
					'reservation' => $this->smallInteger(1),
					'pscategory' => $this->stringType(),
					'fixed_assets_fuel_type' => $this->stringType(),
					'timing_change' => $this->integer(10)->defaultValue(0),
					'oil_change' => $this->integer(10),
					'fuel_consumption' => $this->integer(10),
					'current_odometer_reading' => $this->integer(10),
					'number_repair' => $this->smallInteger(5),
					'date_last_repair' => $this->date(),
				],
				'primaryKeys' => [
					['cfixedassets_pk', 'cfixedassetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassetscf' => [
				'columns' => [
					'cfixedassetsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cfixedassetscf_pk', 'cfixedassetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'user_name' => $this->stringType(50)->notNull(),
					'created' => $this->integer(10)->unsigned(),
					'messages' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternaltickets' => [
				'columns' => [
					'cinternalticketsid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(100),
					'cinternaltickets_no' => $this->stringType(32),
					'internal_tickets_status' => $this->stringType(150),
					'resolution' => $this->text(),
				],
				'primaryKeys' => [
					['cinternaltickets_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternalticketscf' => [
				'columns' => [
					'cinternalticketsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cinternalticketscf_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbook' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'cmileage_logbook_status' => $this->stringType(150),
					'number_kilometers' => $this->decimal('13,2'),
				],
				'primaryKeys' => [
					['cmileagelogbook_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbookcf' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cmileagelogbookcf_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition' => [
				'columns' => [
					'competitionid' => $this->integer(10)->notNull()->defaultValue(0),
					'competition_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['competition_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition_address' => [
				'columns' => [
					'competitionaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['competition_address_pk', 'competitionaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competitioncf' => [
				'columns' => [
					'competitionid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['competitioncf_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'label' => $this->stringType(),
				],
				'primaryKeys' => [
					['crmentity_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_last_changes' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'user_id' => $this->integer(10)->notNull(),
					'date_updated' => $this->dateTime()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_rel_tree' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'module' => $this->integer(10)->notNull(),
					'tree' => $this->stringType(50)->notNull(),
					'relmodule' => $this->integer(10)->notNull(),
					'rel_created_user' => $this->integer(10)->notNull(),
					'rel_created_time' => $this->dateTime()->notNull(),
					'rel_comment' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'searchlabel' => $this->stringType()->notNull(),
					'setype' => $this->stringType(30)->notNull(),
					'userid' => $this->text(),
				],
				'primaryKeys' => [
					['crmentity_search_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_showners' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__dashboard_type' => [
				'columns' => [
					'dashboard_id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'name' => $this->stringType()->notNull(),
					'system' => $this->smallInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['dashboard_type_pk', 'dashboard_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__documents_emailtemplates' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__emailtemplates' => [
				'columns' => [
					'emailtemplatesid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'email_template_type' => $this->stringType(50),
					'module' => $this->stringType(50),
					'subject' => $this->stringType(),
					'content' => $this->text(),
					'sys_name' => $this->stringType(50),
					'email_template_priority' => $this->smallInteger(1)->defaultValue(1),
				],
				'columns_mysql' => [
					'email_template_priority' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['emailtemplates_pk', 'emailtemplatesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorites' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'module' => $this->stringType(30),
					'relcrmid' => $this->integer(10),
					'relmodule' => $this->stringType(30),
					'userid' => $this->integer(10),
					'data' => $this->timestamp()->null(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeeping' => [
				'columns' => [
					'fbookkeepingid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'related_to' => $this->integer(10),
				],
				'primaryKeys' => [
					['fbookkeeping_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeepingcf' => [
				'columns' => [
					'fbookkeepingid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['fbookkeepingcf_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'fcorectinginvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'fcorectinginvoice_status' => $this->stringType()->defaultValue(''),
					'finvoiceid' => $this->integer(10),
				],
				'primaryKeys' => [
					['fcorectinginvoice_pk', 'fcorectinginvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_address' => [
				'columns' => [
					'fcorectinginvoiceaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['fcorectinginvoice_address_pk', 'fcorectinginvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['fcorectinginvoice_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoicecf' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['fcorectinginvoicecf_pk', 'fcorectinginvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__featured_filter' => [
				'columns' => [
					'user' => $this->stringType(30)->notNull(),
					'cvid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['featured_filter_pk', ['user', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice' => [
				'columns' => [
					'finvoiceid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'finvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'finvoice_status' => $this->stringType()->defaultValue(''),
					'finvoice_paymentstatus' => $this->stringType(),
					'finvoice_type' => $this->stringType(),
					'pscategory' => $this->stringType(100),
				],
				'primaryKeys' => [
					['finvoice_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_address' => [
				'columns' => [
					'finvoiceaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoice_address_pk', 'finvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoice_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecf' => [
				'columns' => [
					'finvoiceid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoicecf_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost' => [
				'columns' => [
					'finvoicecostid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'finvoicecost_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'finvoicecost_status' => $this->stringType()->defaultValue(''),
					'finvoicecost_paymentstatus' => $this->stringType(),
					'pscategory' => $this->stringType(50),
				],
				'primaryKeys' => [
					['finvoicecost_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_address' => [
				'columns' => [
					'finvoicecostaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoicecost_address_pk', 'finvoicecostaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->defaultValue(0),
					'net' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->defaultValue(0),
					'taxparam' => $this->stringType(),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoicecost_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecostcf' => [
				'columns' => [
					'finvoicecostid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoicecostcf_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'finvoiceproforma_formpayment' => $this->stringType(),
					'sum_total' => $this->decimal('15,2'),
					'sum_gross' => $this->decimal('13,2'),
					'finvoiceproforma_status' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoiceproforma_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_address' => [
				'columns' => [
					'finvoiceproformaaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoiceproforma_address_pk', 'finvoiceproformaaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoiceproforma_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproformacf' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoiceproformacf_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__github' => [
				'columns' => [
					'github_id' => $this->integer(10)->autoIncrement()->notNull(),
					'client_id' => $this->stringType(20),
					'token' => $this->stringType(100),
					'username' => $this->stringType(32),
				],
				'primaryKeys' => [
					['github_pk', 'github_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn' => [
				'columns' => [
					'igdnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igdn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(10),
					'ssingleordersid' => $this->integer(10),
				],
				'primaryKeys' => [
					['igdn_pk', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['igdn_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc' => [
				'columns' => [
					'igdncid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igdnc_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(10),
					'igdnid' => $this->integer(10),
				],
				'primaryKeys' => [
					['igdnc_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['igdnc_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnccf' => [
				'columns' => [
					'igdncid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igdnccf_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdncf' => [
				'columns' => [
					'igdnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igdncf_pk', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin' => [
				'columns' => [
					'iginid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igin_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'primaryKeys' => [
					['igin_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['igin_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igincf' => [
				'columns' => [
					'iginid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igincf_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn' => [
				'columns' => [
					'igrnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igrn_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igrn_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['igrn_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc' => [
				'columns' => [
					'igrncid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igrnc_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'igrnid' => $this->integer(10),
				],
				'primaryKeys' => [
					['igrnc_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['igrnc_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnccf' => [
				'columns' => [
					'igrncid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igrnccf_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrncf' => [
				'columns' => [
					'igrnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igrncf_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn' => [
				'columns' => [
					'iidnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'iidn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'primaryKeys' => [
					['iidn_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['iidn_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidncf' => [
				'columns' => [
					'iidnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['iidncf_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder' => [
				'columns' => [
					'ipreorderid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'ipreorder_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'acceptance_date' => $this->date(),
				],
				'primaryKeys' => [
					['ipreorder_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['ipreorder_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreordercf' => [
				'columns' => [
					'ipreorderid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ipreordercf_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn' => [
				'columns' => [
					'istdnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'istdn_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'primaryKeys' => [
					['istdn_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['istdn_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdncf' => [
				'columns' => [
					'istdnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istdncf_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istn' => [
				'columns' => [
					'istnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'istn_status' => $this->stringType(),
					'estimated_date' => $this->date(),
					'istn_type' => $this->stringType(),
				],
				'primaryKeys' => [
					['istn_pk', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istncf' => [
				'columns' => [
					'istnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istncf_pk', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages' => [
				'columns' => [
					'istorageid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'storage_status' => $this->stringType()->defaultValue(''),
					'storage_type' => $this->stringType()->defaultValue(''),
					'parentid' => $this->integer(10),
				],
				'primaryKeys' => [
					['istorages_pk', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_address' => [
				'columns' => [
					'istorageaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['istorages_address_pk', 'istorageaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_products' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
					'qtyinstock' => $this->decimal('25,3'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istoragescf' => [
				'columns' => [
					'istorageid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istoragescf_pk', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn' => [
				'columns' => [
					'istrnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'istrn_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'primaryKeys' => [
					['istrn_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['istrn_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrncf' => [
				'columns' => [
					'istrnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istrncf_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebase' => [
				'columns' => [
					'knowledgebaseid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'content' => $this->text(),
					'category' => $this->stringType(200),
					'knowledgebase_view' => $this->stringType(),
					'knowledgebase_status' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['knowledgebase_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebasecf' => [
				'columns' => [
					'knowledgebaseid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['knowledgebasecf_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_address_book' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'email' => $this->stringType(100)->notNull(),
					'name' => $this->stringType()->notNull(),
					'users' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_autologin' => [
				'columns' => [
					'ruid' => $this->smallInteger(5)->unsigned()->notNull(),
					'key' => $this->stringType(50)->notNull(),
					'cuid' => $this->smallInteger(5)->unsigned()->notNull(),
					'params' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_compose_data' => [
				'columns' => [
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'key' => $this->stringType(32)->notNull(),
					'data' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__notification' => [
				'columns' => [
					'notificationid' => $this->integer(10)->notNull(),
					'title' => $this->stringType(),
					'number' => $this->stringType(50),
					'notification_status' => $this->stringType(),
					'notification_type' => $this->stringType()->defaultValue(''),
					'link' => $this->integer(10),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'primaryKeys' => [
					['notification_pk', 'notificationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->char()->notNull(),
					'lat' => $this->decimal('10,7'),
					'lon' => $this->decimal('10,7'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_cache' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'module_name' => $this->stringType(50)->notNull(),
					'crmids' => $this->integer(10)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_record_updater' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'type' => $this->char()->notNull(),
					'address' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners' => [
				'columns' => [
					'partnersid' => $this->integer(10)->notNull()->defaultValue(0),
					'partners_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'category' => $this->stringType()->defaultValue(''),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['partners_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners_address' => [
				'columns' => [
					'partneraddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['partners_address_pk', 'partneraddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partnerscf' => [
				'columns' => [
					'partnersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['partnerscf_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__recurring_info' => [
				'columns' => [
					'srecurringordersid' => $this->integer(10)->notNull()->defaultValue(0),
					'target_module' => $this->stringType(25),
					'recurring_frequency' => $this->stringType(100),
					'start_period' => $this->date(),
					'end_period' => $this->date(),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'last_recurring_date' => $this->date(),
				],
				'primaryKeys' => [
					['recurring_info_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__reviewed_queue' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5),
					'data' => $this->text(),
					'time' => $this->dateTime(),
				],
				'primaryKeys' => [
					['reviewed_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations' => [
				'columns' => [
					'scalculationsid' => $this->integer(10)->notNull()->defaultValue(0),
					'scalculations_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'srequirementscardsid' => $this->integer(10),
					'category' => $this->stringType(),
					'scalculations_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
				],
				'primaryKeys' => [
					['scalculations_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['scalculations_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculationscf' => [
				'columns' => [
					'scalculationsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['scalculationscf_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer(10)->notNull()->defaultValue(0),
					'squoteenquiries_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'category' => $this->stringType(),
					'squoteenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['squoteenquiries_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['squoteenquiries_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiriescf' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['squoteenquiriescf_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes' => [
				'columns' => [
					'squotesid' => $this->integer(10)->notNull()->defaultValue(0),
					'squotes_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'scalculationsid' => $this->integer(10),
					'category' => $this->stringType(),
					'squotes_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'sum_discount' => $this->decimal('28,8'),
					'valid_until' => $this->date(),
				],
				'primaryKeys' => [
					['squotes_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_address' => [
				'columns' => [
					'squotesaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['squotes_address_pk', 'squotesaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['squotes_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotescf' => [
				'columns' => [
					'squotesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['squotescf_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders' => [
				'columns' => [
					'srecurringordersid' => $this->integer(10)->notNull()->defaultValue(0),
					'srecurringorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'squotesid' => $this->integer(10),
					'category' => $this->stringType(),
					'srecurringorders_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['srecurringorders_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_address' => [
				'columns' => [
					'srecurringordersaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['srecurringorders_address_pk', 'srecurringordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['srecurringorders_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorderscf' => [
				'columns' => [
					'srecurringordersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['srecurringorderscf_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards' => [
				'columns' => [
					'srequirementscardsid' => $this->integer(10)->notNull()->defaultValue(0),
					'srequirementscards_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'quoteenquiryid' => $this->integer(10),
					'category' => $this->stringType(),
					'srequirementscards_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['srequirementscards_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['srequirementscards_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscardscf' => [
				'columns' => [
					'srequirementscardsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['srequirementscardscf_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocesses' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer(10)->notNull()->defaultValue(0),
					'ssalesprocesses_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'category' => $this->stringType(),
					'related_to' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'estimated' => $this->decimal('25,8'),
					'actual_sale' => $this->decimal('25,8'),
					'estimated_date' => $this->date(),
					'actual_date' => $this->date(),
					'probability' => $this->decimal('5,2'),
					'ssalesprocesses_source' => $this->stringType(),
					'ssalesprocesses_type' => $this->stringType(),
					'ssalesprocesses_status' => $this->stringType(),
					'campaignid' => $this->integer(10),
					'parentid' => $this->integer(10)->defaultValue(0),
					'startdate' => $this->date(),
				],
				'primaryKeys' => [
					['ssalesprocesses_pk', 'ssalesprocessesid']
				],
				'index' => [
					['related_to', 'related_to'],
					['campaignid', 'campaignid'],
					['parentid', 'parentid'],
					['ssalesprocesses_no', 'ssalesprocesses_no'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocessescf' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ssalesprocessescf_pk', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders' => [
				'columns' => [
					'ssingleordersid' => $this->integer(10)->notNull()->defaultValue(0),
					'ssingleorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'squotesid' => $this->integer(10),
					'category' => $this->stringType(),
					'ssingleorders_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'sum_discount' => $this->decimal('28,8'),
					'ssingleorders_source' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['ssingleorders_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_address' => [
				'columns' => [
					'ssingleordersaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['ssingleorders_address_pk', 'ssingleordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'qtyparam' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['ssingleorders_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorderscf' => [
				'columns' => [
					'ssingleordersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ssingleorderscf_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries' => [
				'columns' => [
					'svendorenquiriesid' => $this->integer(10)->notNull()->defaultValue(0),
					'svendorenquiries_no' => $this->stringType(50)->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'category' => $this->stringType(30),
					'svendorenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'vendorid' => $this->integer(10),
					'scalculationsid' => $this->integer(10),
				],
				'primaryKeys' => [
					['svendorenquiries_pk', 'svendorenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_inventory' => [
				'columns' => [
					'id' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'purchase' => $this->decimal('28,8')->defaultValue(0),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_invfield' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['svendorenquiries_invfield_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiriescf' => [
				'columns' => [
					'svendorenquiriesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['svendorenquiriescf_pk', 'svendorenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__timeline' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'type' => $this->stringType(50),
					'userid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_module' => [
				'columns' => [
					'member' => $this->stringType(50)->notNull(),
					'module' => $this->integer(10)->unsigned()->notNull(),
					'lock' => $this->smallInteger(1)->defaultValue(0),
					'exceptions' => $this->text(),
				],
				'columns_mysql' => [
					'lock' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['watchdog_module_pk', ['member', 'module']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_record' => [
				'columns' => [
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'record' => $this->integer(10)->notNull(),
					'state' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'state' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['watchdog_record_pk', ['userid', 'record']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_schedule' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'frequency' => $this->smallInteger(5)->notNull(),
					'last_execution' => $this->dateTime(),
					'modules' => $this->text(),
				],
				'primaryKeys' => [
					['watchdog_schedule_pk', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['dav_addressbooks_ibfk_1', 'dav_addressbooks', 'principaluri', 'dav_principals', 'uri', 'CASCADE', 'RESTRICT'],
			['dav_calendarobjects_ibfk_1', 'dav_calendarobjects', 'calendarid', 'dav_calendars', 'id', 'CASCADE', 'RESTRICT'],
			['dav_cards_ibfk_1', 'dav_cards', 'addressbookid', 'dav_addressbooks', 'id', 'CASCADE', 'RESTRICT'],
			['roundcube_user_id_fk_cache', 'roundcube_cache', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_index', 'roundcube_cache_index', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_messages', 'roundcube_cache_messages', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_thread', 'roundcube_cache_thread', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_contact_id_fk_contacts', 'roundcube_contactgroupmembers', 'contact_id', 'roundcube_contacts', 'contact_id', 'CASCADE', 'CASCADE'],
			['roundcube_contactgroup_id_fk_contactgroups', 'roundcube_contactgroupmembers', 'contactgroup_id', 'roundcube_contactgroups', 'contactgroup_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_contactgroups', 'roundcube_contactgroups', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_contacts', 'roundcube_contacts', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_dictionary', 'roundcube_dictionary', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_identities', 'roundcube_identities', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_searches', 'roundcube_searches', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_users_autologin_ibfk_1', 'roundcube_users_autologin', 'rcuser_id', 'roundcube_users', 'user_id', 'CASCADE', 'RESTRICT'],
			['u_#__activity_invitation_ibfk_1', 'u_#__activity_invitation', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__announcement', 'u_#__announcement', 'announcementid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__announcement_mark_ibfk_1', 'u_#__announcement_mark', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__announcementcf', 'u_#__announcementcf', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cfixedassetscfixedassetsid', 'u_#__cfixedassets', 'cfixedassetsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cfixedassetscfcfixedassetsid', 'u_#__cfixedassetscf', 'cfixedassetsid', 'u_#__cfixedassets', 'cfixedassetsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cinternalticketscinternalticketsid', 'u_#__cinternaltickets', 'cinternalticketsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cinternalticketscfcinternalticketsid', 'u_#__cinternalticketscf', 'cinternalticketsid', 'u_#__cinternaltickets', 'cinternalticketsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cmileagelogbookcmileagelogbookid', 'u_#__cmileagelogbook', 'cmileagelogbookid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cmileagelogbookcfcmileagelogbookid', 'u_#__cmileagelogbookcf', 'cmileagelogbookid', 'u_#__cmileagelogbook', 'cmileagelogbookid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competition', 'u_#__competition', 'competitionid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__competition_address_ibfk_1', 'u_#__competition_address', 'competitionaddressid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competitioncf', 'u_#__competitioncf', 'competitionid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['u_#__crmentity_last_changes_ibfk_1', 'u_#__crmentity_last_changes', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__crmentity_showners', 'u_#__crmentity_showners', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__documents_emailtemplates', 'u_#__documents_emailtemplates', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_u_#__documents_emailtemplates', 'u_#__documents_emailtemplates', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_emailtemplatesemailtemplatesid', 'u_#__emailtemplates', 'emailtemplatesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__favorites', 'u_#__favorites', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__favorites', 'u_#__favorites', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeeping_ibfk_1', 'u_#__fbookkeeping', 'fbookkeepingid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeepingcf_ibfk_1', 'u_#__fbookkeepingcf', 'fbookkeepingid', 'u_#__fbookkeeping', 'fbookkeepingid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_fcorectinginvoice', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fcorectinginvoice_address_ibfk_1', 'u_#__fcorectinginvoice_address', 'fcorectinginvoiceaddressid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoice_inventory', 'u_#__fcorectinginvoice_inventory', 'id', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoicecf', 'u_#__fcorectinginvoicecf', 'fcorectinginvoiceid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['u_#__featured_filter_ibfk_1', 'u_#__featured_filter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoice', 'u_#__finvoice', 'finvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__finvoice_address_ibfk_1', 'u_#__finvoice_address', 'finvoiceaddressid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoice_inventory', 'u_#__finvoice_inventory', 'id', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecf', 'u_#__finvoicecf', 'finvoiceid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoicecost', 'u_#__finvoicecost', 'finvoicecostid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__finvoicecost_address_ibfk_1', 'u_#__finvoicecost_address', 'finvoicecostaddressid', 'u_#__finvoicecost', 'finvoicecostid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecostcf', 'u_#__finvoicecostcf', 'finvoicecostid', 'u_#__finvoicecost', 'finvoicecostid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproforma', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoiceproforma_inventory', 'u_#__finvoiceproforma_inventory', 'id', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproformacf', 'u_#__finvoiceproformacf', 'finvoiceproformaid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['u_#__igdn_ibfk_1', 'u_#__igdn', 'igdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdn_inventory', 'u_#__igdn_inventory', 'id', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnc_ibfk_1', 'u_#__igdnc', 'igdncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdnc_inventory', 'u_#__igdnc_inventory', 'id', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnccf_ibfk_1', 'u_#__igdnccf', 'igdncid', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdncf_ibfk_1', 'u_#__igdncf', 'igdnid', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igin_ibfk_1', 'u_#__igin', 'iginid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igin_inventory', 'u_#__igin_inventory', 'id', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igincf_ibfk_1', 'u_#__igincf', 'iginid', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igrn_ibfk_1', 'u_#__igrn', 'igrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrn_inventory', 'u_#__igrn_inventory', 'id', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnc_ibfk_1', 'u_#__igrnc', 'igrncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrnc_inventory', 'u_#__igrnc_inventory', 'id', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnccf_ibfk_1', 'u_#__igrnccf', 'igrncid', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrncf_ibfk_1', 'u_#__igrncf', 'igrnid', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidn_ibfk_1', 'u_#__iidn', 'iidnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__iidn_inventory', 'u_#__iidn_inventory', 'id', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidncf_ibfk_1', 'u_#__iidncf', 'iidnid', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreorder_ibfk_1', 'u_#__ipreorder', 'ipreorderid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ipreorder_inventory', 'u_#__ipreorder_inventory', 'id', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreordercf_ibfk_1', 'u_#__ipreordercf', 'ipreorderid', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__istdn_ibfk_1', 'u_#__istdn', 'istdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istdn_inventory', 'u_#__istdn_inventory', 'id', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istdncf_ibfk_1', 'u_#__istdncf', 'istdnid', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istn_ibfk_1', 'u_#__istn', 'istnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istncf_ibfk_1', 'u_#__istncf', 'istnid', 'u_#__istn', 'istnid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_ibfk_1', 'u_#__istorages', 'istorageid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_address_ibfk_1', 'u_#__istorages_address', 'istorageaddressid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_1', 'u_#__istorages_products', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_2', 'u_#__istorages_products', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istoragescf_ibfk_1', 'u_#__istoragescf', 'istorageid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istrn_ibfk_1', 'u_#__istrn', 'istrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istrn_inventory', 'u_#__istrn_inventory', 'id', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['u_#__istrncf_ibfk_1', 'u_#__istrncf', 'istrnid', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebase', 'u_#__knowledgebase', 'knowledgebaseid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebasecf', 'u_#__knowledgebasecf', 'knowledgebaseid', 'u_#__knowledgebase', 'knowledgebaseid', 'CASCADE', 'RESTRICT'],
			['u_#__mail_address_book_ibfk_1', 'u_#__mail_address_book', 'id', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_notification', 'u_#__notification', 'notificationid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partners', 'u_#__partners', 'partnersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__partners_address_ibfk_1', 'u_#__partners_address', 'partneraddressid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partnerscf', 'u_#__partnerscf', 'partnersid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__reviewed_queue', 'u_#__reviewed_queue', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations', 'u_#__scalculations', 'scalculationsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations_inventory', 'u_#__scalculations_inventory', 'id', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculationscf', 'u_#__scalculationscf', 'scalculationsid', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries', 'u_#__squoteenquiries', 'squoteenquiriesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries_inventory', 'u_#__squoteenquiries_inventory', 'id', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiriescf', 'u_#__squoteenquiriescf', 'squoteenquiriesid', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes', 'u_#__squotes', 'squotesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__squotes_address_ibfk_1', 'u_#__squotes_address', 'squotesaddressid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes_inventory', 'u_#__squotes_inventory', 'id', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotescf', 'u_#__squotescf', 'squotesid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders', 'u_#__srecurringorders', 'srecurringordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__srecurringorders_address_ibfk_1', 'u_#__srecurringorders_address', 'srecurringordersaddressid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders_inventory', 'u_#__srecurringorders_inventory', 'id', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorderscf', 'u_#__srecurringorderscf', 'srecurringordersid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards', 'u_#__srequirementscards', 'srequirementscardsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards_inventory', 'u_#__srequirementscards_inventory', 'id', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscardscf', 'u_#__srequirementscardscf', 'srequirementscardsid', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocesses', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocessescf', 'u_#__ssalesprocessescf', 'ssalesprocessesid', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders', 'u_#__ssingleorders', 'ssingleordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__ssingleorders_address_ibfk_1', 'u_#__ssingleorders_address', 'ssingleordersaddressid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders_inventory', 'u_#__ssingleorders_inventory', 'id', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorderscf', 'u_#__ssingleorderscf', 'ssingleordersid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__svendorenquiries', 'u_#__svendorenquiries', 'svendorenquiriesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__svendorenquiriescf', 'u_#__svendorenquiriescf', 'svendorenquiriesid', 'u_#__svendorenquiries', 'svendorenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__timeline', 'u_#__timeline', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_record_ibfk_1', 'u_#__watchdog_record', 'record', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_schedule_ibfk_1', 'u_#__watchdog_schedule', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
		];
	}
}
