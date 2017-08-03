<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base2 extends \DbType
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_account' => [
				'columns' => [
					'accountid' => $this->integer(10)->notNull()->defaultValue(0),
					'account_no' => $this->stringType(100)->notNull(),
					'accountname' => $this->stringType(100)->notNull(),
					'parentid' => $this->integer(10)->defaultValue(0),
					'account_type' => $this->stringType(200),
					'industry' => $this->stringType(200),
					'annualrevenue' => $this->decimal('25,8'),
					'ownership' => $this->stringType(50),
					'siccode' => $this->stringType(50),
					'phone' => $this->stringType(30),
					'otherphone' => $this->stringType(30),
					'email1' => $this->stringType(100),
					'email2' => $this->stringType(100),
					'website' => $this->stringType(100),
					'fax' => $this->stringType(30),
					'employees' => $this->integer(10)->defaultValue(0),
					'emailoptout' => $this->smallInteger(1)->defaultValue(0),
					'isconvertedfromlead' => $this->smallInteger(3)->defaultValue(0),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'no_approval' => $this->smallInteger(1)->defaultValue(0),
					'balance' => $this->decimal('25,8'),
					'payment_balance' => $this->decimal('25,8'),
					'legal_form' => $this->stringType(),
					'sum_time' => $this->decimal('10,2'),
					'inventorybalance' => $this->decimal('25,8')->defaultValue(0),
					'discount' => $this->decimal('5,2')->defaultValue(0),
					'creditlimit' => $this->integer(10),
					'products' => $this->text(),
					'services' => $this->text(),
					'last_invoice_date' => $this->date(),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'accounts_status' => $this->stringType(),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['account_pk', 'accountid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accountaddress' => [
				'columns' => [
					'accountaddressid' => $this->integer(10)->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8b' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['accountaddress_pk', 'accountaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounts_status' => [
				'columns' => [
					'accounts_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'accounts_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['accounts_status_pk', 'accounts_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accountscf' => [
				'columns' => [
					'accountid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['accountscf_pk', 'accountid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounttype' => [
				'columns' => [
					'accounttypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'accounttype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['accounttype_pk', 'accounttypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounttype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_actionmapping' => [
				'columns' => [
					'actionid' => $this->integer(10)->notNull(),
					'actionname' => $this->stringType(200)->notNull(),
					'securitycheck' => $this->integer(10),
				],
				'primaryKeys' => [
					['actionmapping_pk', ['actionid', 'actionname']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity' => [
				'columns' => [
					'activityid' => $this->integer(10)->notNull()->defaultValue(0),
					'subject' => $this->stringType(100)->notNull(),
					'activitytype' => $this->stringType(200)->notNull(),
					'date_start' => $this->date()->notNull(),
					'due_date' => $this->date(),
					'time_start' => $this->time(),
					'time_end' => $this->time(),
					'sendnotification' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'duration_hours' => $this->smallInteger(5),
					'duration_minutes' => $this->smallInteger(3),
					'status' => $this->stringType(200),
					'priority' => $this->stringType(200),
					'location' => $this->stringType(150),
					'notime' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'visibility' => $this->stringType(50)->notNull()->defaultValue('all'),
					'deleted' => $this->smallInteger(1)->defaultValue(0),
					'smownerid' => $this->smallInteger(5)->unsigned(),
					'allday' => $this->smallInteger(1),
					'dav_status' => $this->smallInteger(1)->defaultValue(1),
					'state' => $this->stringType(),
					'link' => $this->integer(10),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
					'followup' => $this->integer(10),
					'reapeat' => $this->smallInteger(1),
					'recurrence' => $this->text(),
				],
				'columns_mysql' => [
					'deleted' => $this->tinyInteger(1)->defaultValue(0),
					'allday' => $this->tinyInteger(1),
					'dav_status' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['activity_pk', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_reminder' => [
				'columns' => [
					'activity_id' => $this->integer(10)->notNull(),
					'reminder_time' => $this->integer(10)->notNull(),
					'reminder_sent' => $this->integer(2)->notNull(),
				],
				'primaryKeys' => [
					['activity_reminder_pk', 'activity_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_reminder_popup' => [
				'columns' => [
					'reminderid' => $this->integer(10)->autoIncrement()->notNull(),
					'recordid' => $this->integer(10)->notNull(),
					'datetime' => $this->dateTime()->notNull(),
					'status' => $this->integer(2)->notNull(),
				],
				'primaryKeys' => [
					['activity_reminder_popup_pk', 'reminderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_update_dates' => [
				'columns' => [
					'activityid' => $this->integer(10)->notNull(),
					'parent' => $this->integer(10)->notNull(),
					'task_id' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['activity_update_dates_pk', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_view' => [
				'columns' => [
					'activity_viewid' => $this->integer(10)->autoIncrement()->notNull(),
					'activity_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['activity_view_pk', 'activity_viewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_view_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitycf' => [
				'columns' => [
					'activityid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['activitycf_pk', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activityproductrel' => [
				'columns' => [
					'activityid' => $this->integer(10)->notNull()->defaultValue(0),
					'productid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['activityproductrel_pk', ['activityid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitystatus' => [
				'columns' => [
					'activitystatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'activitystatus' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['activitystatus_pk', 'activitystatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitystatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitytype' => [
				'columns' => [
					'activitytypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'activitytype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
					'color' => $this->stringType(25),
				],
				'primaryKeys' => [
					['activitytype_pk', 'activitytypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitytype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_announcementstatus' => [
				'columns' => [
					'announcementstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'announcementstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['announcementstatus_pk', 'announcementstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_apiaddress' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'name' => $this->stringType()->notNull(),
					'val' => $this->stringType()->notNull(),
					'type' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['apiaddress_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assets' => [
				'columns' => [
					'assetsid' => $this->integer(10)->notNull(),
					'asset_no' => $this->stringType(30)->notNull(),
					'product' => $this->integer(10)->notNull(),
					'serialnumber' => $this->stringType(200),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'assetstatus' => $this->stringType(200)->defaultValue('PLL_DRAFT'),
					'assetname' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'parent_id' => $this->integer(10),
					'ordertime' => $this->decimal('10,2'),
					'pscategory' => $this->stringType()->defaultValue(''),
					'ssalesprocessesid' => $this->integer(10),
					'assets_renew' => $this->stringType(),
					'renewalinvoice' => $this->integer(10),
				],
				'primaryKeys' => [
					['assets_pk', 'assetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assets_renew' => [
				'columns' => [
					'assets_renewid' => $this->integer(10)->autoIncrement()->notNull(),
					'assets_renew' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['assets_renew_pk', 'assets_renewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetscf' => [
				'columns' => [
					'assetsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['assetscf_pk', 'assetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetstatus' => [
				'columns' => [
					'assetstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'assetstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['assetstatus_pk', 'assetstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskextensions' => [
				'columns' => [
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'asterisk_extension' => $this->stringType(50),
					'use_asterisk' => $this->stringType(3),
				],
				'primaryKeys' => [
					['asteriskextensions_pk', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskincomingcalls' => [
				'columns' => [
					'from_number' => $this->stringType(50),
					'from_name' => $this->stringType(50),
					'to_number' => $this->stringType(50),
					'callertype' => $this->stringType(30),
					'flag' => $this->integer(10),
					'timer' => $this->integer(10),
					'refuid' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskincomingevents' => [
				'columns' => [
					'uid' => $this->stringType()->notNull(),
					'channel' => $this->stringType(100),
					'from_number' => $this->bigInteger(10),
					'from_name' => $this->stringType(100),
					'to_number' => $this->bigInteger(10),
					'callertype' => $this->stringType(100),
					'timer' => $this->integer(10),
					'flag' => $this->stringType(3),
					'pbxrecordid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
				],
				'primaryKeys' => [
					['asteriskincomingevents_pk', 'uid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_attachments' => [
				'columns' => [
					'attachmentsid' => $this->integer(10)->notNull(),
					'name' => $this->stringType()->notNull(),
					'description' => $this->text(),
					'type' => $this->stringType(100),
					'path' => $this->text(),
					'subject' => $this->stringType(),
				],
				'primaryKeys' => [
					['attachments_pk', 'attachmentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_audit_trial' => [
				'columns' => [
					'auditid' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10),
					'module' => $this->stringType(),
					'action' => $this->stringType(),
					'recordid' => $this->stringType(20),
					'actiondate' => $this->dateTime(),
				],
				'primaryKeys' => [
					['audit_trial_pk', 'auditid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks' => [
				'columns' => [
					'blockid' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->integer(19)->notNull(),
					'blocklabel' => $this->stringType(100)->notNull(),
					'sequence' => $this->integer(10),
					'show_title' => $this->integer(2),
					'visible' => $this->integer(2)->notNull()->defaultValue(0),
					'create_view' => $this->integer(2)->notNull()->defaultValue(0),
					'edit_view' => $this->integer(2)->notNull()->defaultValue(0),
					'detail_view' => $this->integer(2)->notNull()->defaultValue(0),
					'display_status' => $this->integer(1)->notNull()->defaultValue(1),
					'iscustom' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['blocks_pk', 'blockid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks_hide' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'blockid' => $this->integer(10)->unsigned(),
					'conditions' => $this->text(),
					'enabled' => $this->smallInteger(1)->unsigned(),
					'view' => $this->stringType(100),
				],
				'columns_mysql' => [
					'enabled' => $this->tinyInteger(1)->unsigned(),
				],
				'primaryKeys' => [
					['blocks_hide_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_default_activitytypes' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'module' => $this->stringType(50),
					'fieldname' => $this->stringType(50),
					'defaultcolor' => $this->stringType(50),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['calendar_default_activitytypes_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_default_activitytypes_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_user_activitytypes' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'defaultid' => $this->integer(10),
					'userid' => $this->integer(10),
					'color' => $this->stringType(50),
					'visible' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['calendar_user_activitytypes_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendarsharedtype' => [
				'columns' => [
					'calendarsharedtypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'calendarsharedtype' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['calendarsharedtype_pk', 'calendarsharedtypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendarsharedtype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callduration' => [
				'columns' => [
					'calldurationid' => $this->integer(10)->autoIncrement()->notNull(),
					'callduration' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['callduration_pk', 'calldurationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callduration_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistory' => [
				'columns' => [
					'callhistoryid' => $this->integer(10)->notNull(),
					'callhistorytype' => $this->stringType(),
					'from_number' => $this->stringType(30),
					'to_number' => $this->stringType(30),
					'location' => $this->stringType(200),
					'phonecallid' => $this->stringType(100),
					'duration' => $this->integer(10),
					'start_time' => $this->dateTime(),
					'end_time' => $this->dateTime(),
					'country' => $this->stringType(100),
					'imei' => $this->stringType(100),
					'ipaddress' => $this->stringType(100),
					'simserial' => $this->stringType(100),
					'subscriberid' => $this->stringType(100),
					'destination' => $this->integer(10),
					'source' => $this->integer(10),
				],
				'primaryKeys' => [
					['callhistory_pk', 'callhistoryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorycf' => [
				'columns' => [
					'callhistoryid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['callhistorycf_pk', 'callhistoryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorytype' => [
				'columns' => [
					'callhistorytypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'callhistorytype' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['callhistorytype_pk', 'callhistorytypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorytype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaign' => [
				'columns' => [
					'campaign_no' => $this->stringType(100)->notNull(),
					'campaignname' => $this->stringType(),
					'campaigntype' => $this->stringType(200),
					'campaignstatus' => $this->stringType(200),
					'expectedrevenue' => $this->decimal('25,8'),
					'budgetcost' => $this->decimal('25,8'),
					'actualcost' => $this->decimal('25,8'),
					'expectedresponse' => $this->stringType(200),
					'numsent' => $this->decimal('11,0'),
					'product_id' => $this->integer(10),
					'sponsor' => $this->stringType(),
					'targetaudience' => $this->stringType(),
					'targetsize' => $this->integer(10),
					'expectedresponsecount' => $this->integer(10),
					'expectedsalescount' => $this->integer(10),
					'expectedroi' => $this->decimal('25,8'),
					'actualresponsecount' => $this->integer(10),
					'actualsalescount' => $this->integer(10),
					'actualroi' => $this->decimal('25,8'),
					'campaignid' => $this->integer(10)->notNull(),
					'closingdate' => $this->date(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['campaign_pk', 'campaignid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaign_records' => [
				'columns' => [
					'campaignid' => $this->integer(10)->notNull()->defaultValue(0),
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'campaignrelstatusid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['campaign_records_pk', ['campaignid', 'crmid', 'campaignrelstatusid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignscf' => [
				'columns' => [
					'campaignid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['campaignscf_pk', 'campaignid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignstatus' => [
				'columns' => [
					'campaignstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'campaignstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['campaignstatus_pk', 'campaignstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaigntype' => [
				'columns' => [
					'campaigntypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'campaigntype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['campaigntype_pk', 'campaigntypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaigntype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cmileage_logbook_status' => [
				'columns' => [
					'cmileage_logbook_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'cmileage_logbook_status' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['cmileage_logbook_status_pk', 'cmileage_logbook_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactaddress' => [
				'columns' => [
					'contactaddressid' => $this->integer(10)->notNull()->defaultValue(0),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8b' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
				],
				'primaryKeys' => [
					['contactaddress_pk', 'contactaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactdetails' => [
				'columns' => [
					'contactid' => $this->integer(10)->notNull()->defaultValue(0),
					'contact_no' => $this->stringType(100)->notNull(),
					'parentid' => $this->integer(10),
					'salutation' => $this->stringType(200),
					'firstname' => $this->stringType(40),
					'lastname' => $this->stringType(80)->notNull(),
					'email' => $this->stringType(100),
					'phone' => $this->stringType(50),
					'mobile' => $this->stringType(50),
					'reportsto' => $this->stringType(30),
					'training' => $this->stringType(50),
					'usertype' => $this->stringType(50),
					'contacttype' => $this->stringType(50),
					'otheremail' => $this->stringType(100),
					'donotcall' => $this->smallInteger(1),
					'emailoptout' => $this->smallInteger(1)->defaultValue(0),
					'imagename' => $this->stringType(150),
					'isconvertedfromlead' => $this->smallInteger(1)->defaultValue(0),
					'verification' => $this->text(),
					'secondary_email' => $this->stringType(100)->defaultValue(''),
					'notifilanguage' => $this->stringType(100)->defaultValue(''),
					'contactstatus' => $this->stringType()->defaultValue(''),
					'dav_status' => $this->smallInteger(1)->defaultValue(1),
					'jobtitle' => $this->stringType(100)->defaultValue(''),
					'decision_maker' => $this->smallInteger(1)->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'dav_status' => $this->tinyInteger(1)->defaultValue(1),
					'decision_maker' => $this->tinyInteger(1)->defaultValue(0),
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['contactdetails_pk', 'contactid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactscf' => [
				'columns' => [
					'contactid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['contactscf_pk', 'contactid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactstatus' => [
				'columns' => [
					'contactstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'contactstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['contactstatus_pk', 'contactstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactsubdetails' => [
				'columns' => [
					'contactsubscriptionid' => $this->integer(10)->notNull()->defaultValue(0),
					'birthday' => $this->date(),
					'laststayintouchrequest' => $this->integer(10)->defaultValue(0),
					'laststayintouchsavedate' => $this->integer(10)->defaultValue(0),
					'leadsource' => $this->stringType(200),
				],
				'primaryKeys' => [
					['contactsubdetails_pk', 'contactsubscriptionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_priority' => [
				'columns' => [
					'contract_priorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'contract_priority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['contract_priority_pk', 'contract_priorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_priority_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_status' => [
				'columns' => [
					'contract_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'contract_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['contract_status_pk', 'contract_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_type' => [
				'columns' => [
					'contract_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'contract_type' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['contract_type_pk', 'contract_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_type_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_convertleadmapping' => [
				'columns' => [
					'cfmid' => $this->integer(10)->autoIncrement()->notNull(),
					'leadfid' => $this->integer(10)->notNull(),
					'accountfid' => $this->integer(10),
					'editable' => $this->integer(10)->defaultValue(1),
				],
				'primaryKeys' => [
					['convertleadmapping_pk', 'cfmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentity' => [
				'columns' => [
					'crmid' => $this->integer(10)->autoIncrement()->notNull(),
					'smcreatorid' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'smownerid' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'shownerid' => $this->smallInteger(1),
					'modifiedby' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'setype' => $this->stringType(30)->notNull(),
					'description' => $this->text(),
					'attention' => $this->text(),
					'createdtime' => $this->dateTime()->notNull(),
					'modifiedtime' => $this->dateTime()->notNull(),
					'viewedtime' => $this->dateTime(),
					'closedtime' => $this->dateTime(),
					'status' => $this->stringType(50),
					'version' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'deleted' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'was_read' => $this->smallInteger(1)->defaultValue(0),
					'private' => $this->smallInteger(1)->defaultValue(0),
					'users' => $this->text(),
				],
				'columns_mysql' => [
					'shownerid' => $this->tinyInteger(1),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'was_read' => $this->tinyInteger(1)->defaultValue(0),
					'private' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['crmentity_pk', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentityrel' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'module' => $this->stringType(25)->notNull(),
					'relcrmid' => $this->integer(10)->notNull(),
					'relmodule' => $this->stringType(25)->notNull(),
					'rel_created_user' => $this->integer(10),
					'rel_created_time' => $this->dateTime(),
					'rel_comment' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(100),
					'handler_file' => $this->stringType(100),
					'frequency' => $this->integer(10),
					'laststart' => $this->integer(10)->unsigned(),
					'lastend' => $this->integer(10)->unsigned(),
					'status' => $this->integer(10),
					'module' => $this->stringType(100),
					'sequence' => $this->integer(10),
					'description' => $this->text(),
				],
				'primaryKeys' => [
					['cron_task_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currencies' => [
				'columns' => [
					'currencyid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_name' => $this->stringType(200),
					'currency_code' => $this->stringType(50),
					'currency_symbol' => $this->stringType(11),
				],
				'primaryKeys' => [
					['currencies_pk', 'currencyid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currencies_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency' => [
				'columns' => [
					'currencyid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['currency_pk', 'currencyid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_decimal_separator' => [
				'columns' => [
					'currency_decimal_separatorid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_decimal_separator' => $this->stringType(2)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['currency_decimal_separator_pk', 'currency_decimal_separatorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_decimal_separator_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_pattern' => [
				'columns' => [
					'currency_grouping_patternid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_grouping_pattern' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['currency_grouping_pattern_pk', 'currency_grouping_patternid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_pattern_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_separator' => [
				'columns' => [
					'currency_grouping_separatorid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_grouping_separator' => $this->stringType(2)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['currency_grouping_separator_pk', 'currency_grouping_separatorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_separator_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_info' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_name' => $this->stringType(100),
					'currency_code' => $this->stringType(100),
					'currency_symbol' => $this->stringType(30),
					'conversion_rate' => $this->decimal('12,5'),
					'currency_status' => $this->stringType(25),
					'defaultid' => $this->smallInteger(3)->notNull()->defaultValue(0),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'defaultid' => $this->tinyInteger(3)->notNull()->defaultValue(0),
				],
				'index' => [
					['deleted', 'deleted'],
				],
				'primaryKeys' => [
					['currency_info_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_info_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_symbol_placement' => [
				'columns' => [
					'currency_symbol_placementid' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_symbol_placement' => $this->stringType(30)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['currency_symbol_placement_pk', 'currency_symbol_placementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_symbol_placement_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customaction' => [
				'columns' => [
					'cvid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(250)->notNull(),
					'module' => $this->stringType(50)->notNull(),
					'content' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customerdetails' => [
				'columns' => [
					'customerid' => $this->integer(10)->notNull(),
					'portal' => $this->smallInteger(1),
					'support_start_date' => $this->date(),
					'support_end_date' => $this->date(),
				],
				'primaryKeys' => [
					['customerdetails_pk', 'customerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'columns' => [
					'cvid' => $this->integer(10)->autoIncrement()->notNull(),
					'viewname' => $this->stringType(100)->notNull(),
					'setdefault' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'setmetrics' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'entitytype' => $this->stringType(25)->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(1),
					'userid' => $this->integer(10)->defaultValue(1),
					'privileges' => $this->smallInteger(2)->defaultValue(1),
					'featured' => $this->smallInteger(1)->defaultValue(0),
					'sequence' => $this->integer(10),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'description' => $this->text(),
					'sort' => $this->stringType(30)->defaultValue(''),
					'color' => $this->stringType(10)->defaultValue(''),
				],
				'columns_mysql' => [
					'setdefault' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'setmetrics' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
					'privileges' => $this->tinyInteger(2)->defaultValue(1),
					'featured' => $this->tinyInteger(1)->defaultValue(0),
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['customview_pk', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvadvfilter' => [
				'columns' => [
					'cvid' => $this->integer(10)->notNull(),
					'columnindex' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'comparator' => $this->stringType(20),
					'value' => $this->stringType(512),
					'groupid' => $this->integer(10)->defaultValue(1),
					'column_condition' => $this->stringType()->defaultValue('and'),
				],
				'primaryKeys' => [
					['cvadvfilter_pk', ['cvid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvadvfilter_grouping' => [
				'columns' => [
					'groupid' => $this->integer(10)->unsigned()->notNull(),
					'cvid' => $this->integer(10)->unsigned()->notNull(),
					'group_condition' => $this->stringType(),
					'condition_expression' => $this->text(),
				],
				'primaryKeys' => [
					['cvadvfilter_grouping_pk', ['groupid', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvcolumnlist' => [
				'columns' => [
					'cvid' => $this->integer(10)->notNull(),
					'columnindex' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
				],
				'primaryKeys' => [
					['cvcolumnlist_pk', ['cvid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvstdfilter' => [
				'columns' => [
					'cvid' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'stdfilter' => $this->stringType(250)->defaultValue(''),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
				],
				'primaryKeys' => [
					['cvstdfilter_pk', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dataaccess' => [
				'columns' => [
					'dataaccessid' => $this->smallInteger(5)->autoIncrement()->notNull(),
					'module_name' => $this->stringType(25),
					'summary' => $this->stringType()->notNull(),
					'data' => $this->text(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['dataaccess_pk', 'dataaccessid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dataaccess_cnd' => [
				'columns' => [
					'dataaccess_cndid' => $this->integer(10)->autoIncrement()->notNull(),
					'dataaccessid' => $this->integer(10)->notNull(),
					'fieldname' => $this->stringType()->notNull(),
					'comparator' => $this->stringType()->notNull(),
					'val' => $this->stringType(),
					'required' => $this->smallInteger(3)->notNull(),
					'field_type' => $this->stringType(100)->notNull(),
				],
				'columns_mysql' => [
					'required' => $this->tinyInteger(3)->notNull(),
				],
				'primaryKeys' => [
					['dataaccess_cnd_pk', 'dataaccess_cndid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2grp' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_groupid' => $this->integer(10),
					'to_groupid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_grp2grp_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2role' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_groupid' => $this->integer(10),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_grp2role_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2rs' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_groupid' => $this->integer(10),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_grp2rs_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2us' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_groupid' => $this->integer(10),
					'to_userid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_grp2us_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_module_rel' => [
				'columns' => [
					'shareid' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->integer(19)->notNull(),
					'relationtype' => $this->stringType(200),
				],
				'primaryKeys' => [
					['datashare_module_rel_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodule_permission' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'datashare_relatedmodule_id' => $this->integer(10)->notNull(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_relatedmodule_permission_pk', ['shareid', 'datashare_relatedmodule_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodules' => [
				'columns' => [
					'datashare_relatedmodule_id' => $this->integer(10)->notNull(),
					'tabid' => $this->integer(19),
					'relatedto_tabid' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_relatedmodules_pk', 'datashare_relatedmodule_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodules_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2group' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleid' => $this->stringType(),
					'to_groupid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_role2group_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2role' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleid' => $this->stringType(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_role2role_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2rs' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleid' => $this->stringType(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_role2rs_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2us' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleid' => $this->stringType(),
					'to_userid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_role2us_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2grp' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_groupid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_rs2grp_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2role' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_rs2role_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2rs' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_rs2rs_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2us' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_userid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_rs2us_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2grp' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_userid' => $this->integer(10),
					'to_groupid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_us2grp_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2role' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_userid' => $this->integer(10),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_us2role_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2rs' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_userid' => $this->integer(10),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_us2rs_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2us' => [
				'columns' => [
					'shareid' => $this->integer(10)->notNull(),
					'share_userid' => $this->integer(10),
					'to_userid' => $this->integer(10),
					'permission' => $this->integer(10),
				],
				'primaryKeys' => [
					['datashare_us2us_pk', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_date_format' => [
				'columns' => [
					'date_formatid' => $this->integer(10)->autoIncrement()->notNull(),
					'date_format' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['date_format_pk', 'date_formatid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_date_format_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dayoftheweek' => [
				'columns' => [
					'dayoftheweekid' => $this->integer(10)->autoIncrement()->notNull(),
					'dayoftheweek' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['dayoftheweek_pk', 'dayoftheweekid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dayoftheweek_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_field' => [
				'columns' => [
					'tabid' => $this->smallInteger(5),
					'fieldid' => $this->integer(10)->notNull(),
					'visible' => $this->integer(10),
					'readonly' => $this->integer(10),
				],
				'primaryKeys' => [
					['def_org_field_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_share' => [
				'columns' => [
					'ruleid' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'permission' => $this->integer(10),
					'editstatus' => $this->integer(10),
				],
				'primaryKeys' => [
					['def_org_share_pk', 'ruleid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_default_record_view' => [
				'columns' => [
					'default_record_viewid' => $this->integer(10)->autoIncrement()->notNull(),
					'default_record_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['default_record_view_pk', 'default_record_viewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_default_record_view_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaultactivitytype' => [
				'columns' => [
					'defaultactivitytypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'defaultactivitytype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['defaultactivitytype_pk', 'defaultactivitytypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaultactivitytype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaulteventstatus' => [
				'columns' => [
					'defaulteventstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'defaulteventstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['defaulteventstatus_pk', 'defaulteventstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaulteventstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_duration_minutes' => [
				'columns' => [
					'minutesid' => $this->integer(10)->autoIncrement()->notNull(),
					'duration_minutes' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['duration_minutes_pk', 'minutesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_duration_minutes_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_durationhrs' => [
				'columns' => [
					'hrsid' => $this->integer(10)->autoIncrement()->notNull(),
					'hrs' => $this->stringType(50),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['durationhrs_pk', 'hrsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_durationmins' => [
				'columns' => [
					'minsid' => $this->integer(10)->autoIncrement()->notNull(),
					'mins' => $this->stringType(50),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['durationmins_pk', 'minsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_email_template_priority' => [
				'columns' => [
					'email_template_priorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'email_template_priority' => $this->smallInteger(1),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'email_template_priority' => $this->tinyInteger(1),
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['email_template_priority_pk', 'email_template_priorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_email_template_type' => [
				'columns' => [
					'email_template_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'email_template_type' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['email_template_type_pk', 'email_template_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_education' => [
				'columns' => [
					'employee_educationid' => $this->integer(10)->autoIncrement()->notNull(),
					'employee_education' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['employee_education_pk', 'employee_educationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_education_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_status' => [
				'columns' => [
					'employee_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'employee_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['employee_status_pk', 'employee_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_end_hour' => [
				'columns' => [
					'end_hourid' => $this->integer(10)->autoIncrement()->notNull(),
					'end_hour' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['end_hour_pk', 'end_hourid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_end_hour_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entity_stats' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'crmactivity' => $this->smallInteger(5),
				],
				'primaryKeys' => [
					['entity_stats_pk', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandlers' => [
				'columns' => [
					'eventhandler_id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'event_name' => $this->stringType(50)->notNull(),
					'handler_class' => $this->stringType(100)->notNull(),
					'is_active' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'include_modules' => $this->stringType()->notNull()->defaultValue(''),
					'exclude_modules' => $this->stringType()->notNull()->defaultValue(''),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(5),
					'owner_id' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'is_active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'priority' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(5),
				],
				'index' => [
					['event_name_class', ['event_name', 'handler_class']],
				],
				'primaryKeys' => [
					['eventhandlers_pk', 'eventhandler_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventstatus' => [
				'columns' => [
					'eventstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'eventstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['eventstatus_pk', 'eventstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_expectedresponse' => [
				'columns' => [
					'expectedresponseid' => $this->integer(10)->autoIncrement()->notNull(),
					'expectedresponse' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['expectedresponse_pk', 'expectedresponseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_expectedresponse_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faq' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'faq_no' => $this->stringType(100)->notNull(),
					'product_id' => $this->stringType(100),
					'question' => $this->text(),
					'answer' => $this->text(),
					'category' => $this->stringType(200)->notNull(),
					'status' => $this->stringType(200)->notNull(),
				],
				'primaryKeys' => [
					['faq_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcategories' => [
				'columns' => [
					'faqcategories_id' => $this->integer(10)->autoIncrement()->notNull(),
					'faqcategories' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['faqcategories_pk', 'faqcategories_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcategories_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcf' => [
				'columns' => [
					'faqid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['faqcf_pk', 'faqid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcomments' => [
				'columns' => [
					'commentid' => $this->integer(10)->autoIncrement()->notNull(),
					'faqid' => $this->integer(10),
					'comments' => $this->text(),
					'createdtime' => $this->dateTime()->notNull(),
				],
				'primaryKeys' => [
					['faqcomments_pk', 'commentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqstatus' => [
				'columns' => [
					'faqstatus_id' => $this->integer(10)->autoIncrement()->notNull(),
					'faqstatus' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['faqstatus_pk', 'faqstatus_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fcorectinginvoice_formpayment' => [
				'columns' => [
					'fcorectinginvoice_formpaymentid' => $this->integer(10)->autoIncrement()->notNull(),
					'fcorectinginvoice_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['fcorectinginvoice_formpayment_pk', 'fcorectinginvoice_formpaymentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fcorectinginvoice_status' => [
				'columns' => [
					'fcorectinginvoice_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'fcorectinginvoice_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['fcorectinginvoice_status_pk', 'fcorectinginvoice_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_feedback' => [
				'columns' => [
					'userid' => $this->integer(10),
					'dontshow' => $this->stringType(19)->defaultValue('false'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'tabid' => $this->integer(19)->notNull(),
					'fieldid' => $this->integer(10)->autoIncrement()->notNull(),
					'columnname' => $this->stringType(30)->notNull(),
					'tablename' => $this->stringType(50)->notNull(),
					'generatedtype' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
					'uitype' => $this->smallInteger(5)->unsigned()->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'fieldlabel' => $this->stringType(50)->notNull(),
					'readonly' => $this->smallInteger(1)->unsigned()->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'defaultvalue' => $this->text(),
					'maximumlength' => $this->smallInteger(5)->unsigned()->notNull(),
					'sequence' => $this->smallInteger(5)->unsigned()->notNull(),
					'block' => $this->integer(10),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull(),
					'typeofdata' => $this->stringType(100),
					'quickcreate' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'quickcreatesequence' => $this->integer(10),
					'info_type' => $this->stringType(20),
					'masseditable' => $this->integer(10)->notNull()->defaultValue(1),
					'helpinfo' => $this->stringType(30)->defaultValue(''),
					'summaryfield' => $this->integer(10)->notNull()->defaultValue(0),
					'fieldparams' => $this->stringType()->defaultValue(''),
					'header_field' => $this->stringType(15),
					'maxlengthtext' => $this->smallInteger(3)->unsigned()->defaultValue(0),
					'maxwidthcolumn' => $this->smallInteger(3)->unsigned()->defaultValue(0),
				],
				'columns_mysql' => [
					'generatedtype' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0),
					'readonly' => $this->tinyInteger(1)->unsigned()->notNull(),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull(),
					'quickcreate' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['field_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fieldmodulerel' => [
				'columns' => [
					'fieldid' => $this->smallInteger(5)->unsigned()->notNull(),
					'module' => $this->stringType(25)->notNull(),
					'relmodule' => $this->stringType(25)->notNull(),
					'status' => $this->stringType(10),
					'sequence' => $this->smallInteger(1)->unsigned()->defaultValue(0),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_formpayment' => [
				'columns' => [
					'finvoice_formpaymentid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoice_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoice_formpayment_pk', 'finvoice_formpaymentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_paymentstatus' => [
				'columns' => [
					'finvoice_paymentstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoice_paymentstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoice_paymentstatus_pk', 'finvoice_paymentstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_status' => [
				'columns' => [
					'finvoice_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoice_status' => $this->stringType(200)->notNull(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoice_status_pk', 'finvoice_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_type' => [
				'columns' => [
					'finvoice_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoice_type' => $this->stringType(200)->notNull(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoice_type_pk', 'finvoice_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoicecost_formpayment' => [
				'columns' => [
					'finvoicecost_formpaymentid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoicecost_formpayment' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoicecost_formpayment_pk', 'finvoicecost_formpaymentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoicecost_paymentstatus' => [
				'columns' => [
					'finvoicecost_paymentstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoicecost_paymentstatus' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoicecost_paymentstatus_pk', 'finvoicecost_paymentstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoicecost_status' => [
				'columns' => [
					'finvoicecost_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoicecost_status' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['finvoicecost_status_pk', 'finvoicecost_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoiceproforma_formpayment' => [
				'columns' => [
					'finvoiceproforma_formpaymentid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoiceproforma_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['finvoiceproforma_formpayment_pk', 'finvoiceproforma_formpaymentid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoiceproforma_status' => [
				'columns' => [
					'finvoiceproforma_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'finvoiceproforma_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['finvoiceproforma_status_pk', 'finvoiceproforma_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fixed_assets_fuel_type' => [
				'columns' => [
					'fixed_assets_fuel_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'fixed_assets_fuel_type' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['fixed_assets_fuel_type_pk', 'fixed_assets_fuel_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fixed_assets_status' => [
				'columns' => [
					'fixed_assets_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'fixed_assets_status' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['fixed_assets_status_pk', 'fixed_assets_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fixed_assets_type' => [
				'columns' => [
					'fixed_assets_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'fixed_assets_type' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['fixed_assets_type_pk', 'fixed_assets_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_1_vtiger_account', 'vtiger_account', 'accountid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_accountaddress_ibfk_1', 'vtiger_accountaddress', 'accountaddressid', 'vtiger_account', 'accountid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_accountscf', 'vtiger_accountscf', 'accountid', 'vtiger_account', 'accountid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_activity', 'vtiger_activity', 'activityid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_reminder_ibfk_1', 'vtiger_activity_reminder', 'activity_id', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_reminder_popup_ibfk_1', 'vtiger_activity_reminder_popup', 'recordid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_1', 'vtiger_activity_update_dates', 'task_id', 'com_vtiger_workflowtasks', 'task_id', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_2', 'vtiger_activity_update_dates', 'parent', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_3', 'vtiger_activity_update_dates', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activitycf_ibfk_1', 'vtiger_activitycf', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_activityproductrel', 'vtiger_activityproductrel', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_assets', 'vtiger_assets', 'assetsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_assetscf_ibfk_1', 'vtiger_assetscf', 'assetsid', 'vtiger_assets', 'assetsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_attachments', 'vtiger_attachments', 'attachmentsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_blocks', 'vtiger_blocks', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['vtiger_callhistory_ibfk_1', 'vtiger_callhistory', 'callhistoryid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_callhistorycf_ibfk_1', 'vtiger_callhistorycf', 'callhistoryid', 'vtiger_callhistory', 'callhistoryid', 'CASCADE', 'RESTRICT'],
			['fk_vtiger_crmentity', 'vtiger_campaign_records', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_campaignscf', 'vtiger_campaignscf', 'campaignid', 'vtiger_campaign', 'campaignid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactaddress', 'vtiger_contactaddress', 'contactaddressid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactdetails', 'vtiger_contactdetails', 'contactid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactscf', 'vtiger_contactscf', 'contactid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactsubdetails', 'vtiger_contactsubdetails', 'contactsubscriptionid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customaction', 'vtiger_customaction', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customerdetails', 'vtiger_customerdetails', 'customerid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customview', 'vtiger_customview', 'entitytype', 'vtiger_tab', 'name', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvadvfilter', 'vtiger_cvadvfilter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvcolumnlist', 'vtiger_cvcolumnlist', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvstdfilter', 'vtiger_cvstdfilter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2grp', 'vtiger_datashare_grp2grp', 'to_groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2role', 'vtiger_datashare_grp2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2rs', 'vtiger_datashare_grp2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_datashare_module_rel', 'vtiger_datashare_module_rel', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_datashare_relatedmodules', 'vtiger_datashare_relatedmodules', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2group', 'vtiger_datashare_role2group', 'share_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2role', 'vtiger_datashare_role2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2rs', 'vtiger_datashare_role2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2grp', 'vtiger_datashare_rs2grp', 'share_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2role', 'vtiger_datashare_rs2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2rs', 'vtiger_datashare_rs2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_def_org_share', 'vtiger_def_org_share', 'permission', 'vtiger_org_share_action_mapping', 'share_action_id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_defaultcv', 'vtiger_defaultcv', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_entity_stats', 'vtiger_entity_stats', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_entityname', 'vtiger_entityname', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faq', 'vtiger_faq', 'id', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faqcf', 'vtiger_faqcf', 'faqid', 'vtiger_faq', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faqcomments', 'vtiger_faqcomments', 'faqid', 'vtiger_faq', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_field', 'vtiger_field', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}
}
