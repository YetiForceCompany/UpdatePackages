<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base4 extends \DbType
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_realization_process' => [
				'columns' => [
					'module_id' => $this->integer(10)->notNull(),
					'status_indicate_closing' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurring_frequency' => [
				'columns' => [
					'recurring_frequency_id' => $this->integer(10),
					'recurring_frequency' => $this->stringType(200),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurring_frequency_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rel_mod' => [
				'columns' => [
					'rel_modid' => $this->integer(10)->autoIncrement()->notNull(),
					'rel_mod' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['rel_mod_pk', 'rel_modid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rel_mod_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists' => [
				'columns' => [
					'relation_id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'related_tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'name' => $this->stringType(50),
					'sequence' => $this->smallInteger(3)->unsigned()->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'actions' => $this->stringType(50)->notNull()->defaultValue(''),
					'favorites' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'creator_detail' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'relation_comment' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned()->notNull(),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'favorites' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'creator_detail' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'relation_comment' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['relatedlists_pk', 'relation_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_fields' => [
				'columns' => [
					'relation_id' => $this->integer(10),
					'fieldid' => $this->integer(10),
					'fieldname' => $this->stringType(30),
					'sequence' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relcriteria' => [
				'columns' => [
					'queryid' => $this->integer(10)->notNull(),
					'columnindex' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'comparator' => $this->stringType(20),
					'value' => $this->stringType(512),
					'groupid' => $this->integer(10)->defaultValue(1),
					'column_condition' => $this->stringType(256)->defaultValue('and'),
				],
				'primaryKeys' => [
					['relcriteria_pk', ['queryid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relcriteria_grouping' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'queryid' => $this->integer(10)->notNull(),
					'group_condition' => $this->stringType(256),
					'condition_expression' => $this->text(),
				],
				'primaryKeys' => [
					['relcriteria_grouping_pk', ['groupid', 'queryid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reminder_interval' => [
				'columns' => [
					'reminder_intervalid' => $this->integer(10)->autoIncrement()->notNull(),
					'reminder_interval' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull(),
					'presence' => $this->integer(1)->notNull(),
				],
				'primaryKeys' => [
					['reminder_interval_pk', 'reminder_intervalid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reminder_interval_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_report' => [
				'columns' => [
					'reportid' => $this->integer(10)->notNull(),
					'folderid' => $this->integer(10)->notNull(),
					'reportname' => $this->stringType(100)->defaultValue(''),
					'description' => $this->stringType(250)->defaultValue(''),
					'reporttype' => $this->stringType(50)->defaultValue(''),
					'queryid' => $this->integer(10)->notNull()->defaultValue(0),
					'state' => $this->stringType(50)->defaultValue('SAVED'),
					'customizable' => $this->integer(1)->defaultValue(1),
					'category' => $this->integer(10)->defaultValue(1),
					'owner' => $this->integer(10)->defaultValue(1),
					'sharingtype' => $this->stringType(200)->defaultValue('Private'),
				],
				'primaryKeys' => [
					['report_pk', 'reportid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportdatefilter' => [
				'columns' => [
					'datefilterid' => $this->integer(10)->notNull(),
					'datecolumnname' => $this->stringType(250)->defaultValue(''),
					'datefilter' => $this->stringType(250)->defaultValue(''),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
				],
				'primaryKeys' => [
					['reportdatefilter_pk', 'datefilterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportfilters' => [
				'columns' => [
					'filterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(200)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportfolder' => [
				'columns' => [
					'folderid' => $this->integer(10)->autoIncrement()->notNull(),
					'foldername' => $this->stringType(100)->notNull()->defaultValue(''),
					'description' => $this->stringType(250)->defaultValue(''),
					'state' => $this->stringType(50)->defaultValue('SAVED'),
				],
				'primaryKeys' => [
					['reportfolder_pk', 'folderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportgroupbycolumn' => [
				'columns' => [
					'reportid' => $this->integer(10),
					'sortid' => $this->integer(10),
					'sortcolname' => $this->stringType(250),
					'dategroupbycriteria' => $this->stringType(250),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportmodules' => [
				'columns' => [
					'reportmodulesid' => $this->integer(10)->notNull(),
					'primarymodule' => $this->stringType(50)->notNull()->defaultValue(''),
					'secondarymodules' => $this->stringType(250)->defaultValue(''),
				],
				'primaryKeys' => [
					['reportmodules_pk', 'reportmodulesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsharing' => [
				'columns' => [
					'reportid' => $this->integer(10)->notNull(),
					'shareid' => $this->integer(10)->notNull(),
					'setype' => $this->stringType(200)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsortcol' => [
				'columns' => [
					'sortcolid' => $this->integer(10)->notNull(),
					'reportid' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'sortorder' => $this->stringType(250)->defaultValue('Asc'),
				],
				'primaryKeys' => [
					['reportsortcol_pk', ['sortcolid', 'reportid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsummary' => [
				'columns' => [
					'reportsummaryid' => $this->integer(10)->notNull(),
					'summarytype' => $this->integer(10)->notNull(),
					'columnname' => $this->stringType(250)->notNull()->defaultValue(''),
				],
				'primaryKeys' => [
					['reportsummary_pk', ['reportsummaryid', 'summarytype', 'columnname']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations' => [
				'columns' => [
					'reservationsid' => $this->integer(10)->notNull()->defaultValue(0),
					'title' => $this->stringType(128),
					'reservations_no' => $this->stringType(),
					'reservations_status' => $this->stringType(128),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(50),
					'due_date' => $this->date(),
					'time_end' => $this->stringType(50),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'relatedida' => $this->integer(10)->defaultValue(0),
					'relatedidb' => $this->integer(10)->defaultValue(0),
					'deleted' => $this->integer(1)->defaultValue(0),
					'type' => $this->stringType(128),
				],
				'primaryKeys' => [
					['reservations_pk', 'reservationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations_status' => [
				'columns' => [
					'reservations_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'reservations_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['reservations_status_pk', 'reservations_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservationscf' => [
				'columns' => [
					'reservationsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['reservationscf_pk', 'reservationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'rolename' => $this->stringType(200),
					'parentrole' => $this->stringType(),
					'depth' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'allowassignedrecordsto' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'changeowner' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'searchunpriv' => $this->text(),
					'clendarallorecords' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'listrelatedrecord' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'previewrelatedrecord' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'editrelatedrecord' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'permissionsrelatedfield' => $this->stringType(10)->notNull()->defaultValue(0),
					'globalsearchadv' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'assignedmultiowner' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'auto_assign' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'allowassignedrecordsto' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'changeowner' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'clendarallorecords' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'listrelatedrecord' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'previewrelatedrecord' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'editrelatedrecord' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'globalsearchadv' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'assignedmultiowner' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'auto_assign' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['role_pk', 'roleid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role2picklist' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'picklistvalueid' => $this->integer(10)->notNull(),
					'picklistid' => $this->integer(10)->notNull(),
					'sortid' => $this->integer(10),
				],
				'primaryKeys' => [
					['role2picklist_pk', ['roleid', 'picklistvalueid', 'picklistid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role2profile' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'profileid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['role2profile_pk', ['roleid', 'profileid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rowheight' => [
				'columns' => [
					'rowheightid' => $this->integer(10)->autoIncrement()->notNull(),
					'rowheight' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['rowheight_pk', 'rowheightid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rowheight_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rss' => [
				'columns' => [
					'rssid' => $this->integer(10)->autoIncrement()->notNull(),
					'rssurl' => $this->stringType(200)->notNull()->defaultValue(''),
					'rsstitle' => $this->stringType(200),
					'rsstype' => $this->integer(10)->defaultValue(0),
					'starred' => $this->integer(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['rss_pk', 'rssid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salesmanattachmentsrel' => [
				'columns' => [
					'smid' => $this->integer(10)->notNull()->defaultValue(0),
					'attachmentsid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['salesmanattachmentsrel_pk', ['smid', 'attachmentsid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salesmanticketrel' => [
				'columns' => [
					'smid' => $this->integer(10)->notNull()->defaultValue(0),
					'id' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['salesmanticketrel_pk', ['smid', 'id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salutationtype' => [
				'columns' => [
					'salutationid' => $this->integer(10)->autoIncrement()->notNull(),
					'salutationtype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['salutationtype_pk', 'salutationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salutationtype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_scalculations_status' => [
				'columns' => [
					'scalculations_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'scalculations_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['scalculations_status_pk', 'scalculations_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_scheduled_reports' => [
				'columns' => [
					'reportid' => $this->integer(10)->notNull(),
					'recipients' => $this->text(),
					'schedule' => $this->text(),
					'format' => $this->stringType(10),
					'next_trigger_time' => $this->timestamp()->null(),
				],
				'primaryKeys' => [
					['scheduled_reports_pk', 'reportid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_schedulereports' => [
				'columns' => [
					'reportid' => $this->integer(10),
					'scheduleid' => $this->integer(3),
					'recipients' => $this->text(),
					'schdate' => $this->stringType(20),
					'schtime' => $this->time(),
					'schdayoftheweek' => $this->stringType(100),
					'schdayofthemonth' => $this->stringType(100),
					'schannualdates' => $this->stringType(500),
					'specificemails' => $this->stringType(500),
					'next_trigger_time' => $this->timestamp()->null(),
					'filetype' => $this->stringType(20),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seattachmentsrel' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'attachmentsid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['seattachmentsrel_pk', ['crmid', 'attachmentsid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectcolumn' => [
				'columns' => [
					'queryid' => $this->integer(10)->notNull(),
					'columnindex' => $this->integer(10)->notNull()->defaultValue(0),
					'columnname' => $this->stringType(250)->defaultValue(''),
				],
				'primaryKeys' => [
					['selectcolumn_pk', ['queryid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectquery' => [
				'columns' => [
					'queryid' => $this->integer(10)->notNull(),
					'startindex' => $this->integer(10)->defaultValue(0),
					'numofobjects' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['selectquery_pk', 'queryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectquery_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_senotesrel' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'notesid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['senotesrel_pk', ['crmid', 'notesid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seproductsrel' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'productid' => $this->integer(10)->notNull()->defaultValue(0),
					'setype' => $this->stringType(30)->notNull(),
					'rel_created_user' => $this->integer(10)->notNull(),
					'rel_created_time' => $this->dateTime()->notNull(),
					'rel_comment' => $this->stringType(),
				],
				'primaryKeys' => [
					['seproductsrel_pk', ['crmid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service' => [
				'columns' => [
					'serviceid' => $this->integer(10)->notNull(),
					'service_no' => $this->stringType(100)->notNull(),
					'servicename' => $this->stringType()->notNull(),
					'pscategory' => $this->stringType(200),
					'qty_per_unit' => $this->decimal('11,2')->defaultValue(0),
					'unit_price' => $this->decimal('25,8'),
					'sales_start_date' => $this->date(),
					'sales_end_date' => $this->date(),
					'start_date' => $this->date(),
					'expiry_date' => $this->date(),
					'discontinued' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'service_usageunit' => $this->stringType(200),
					'website' => $this->stringType(100),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
					'commissionrate' => $this->decimal('7,3'),
					'renewable' => $this->smallInteger(1)->defaultValue(0),
					'taxes' => $this->stringType(50),
				],
				'columns_mysql' => [
					'discontinued' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'renewable' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['service_pk', 'serviceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service_usageunit' => [
				'columns' => [
					'service_usageunitid' => $this->integer(10)->autoIncrement()->notNull(),
					'service_usageunit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['service_usageunit_pk', 'service_usageunitid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service_usageunit_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecf' => [
				'columns' => [
					'serviceid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['servicecf_pk', 'serviceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecontracts' => [
				'columns' => [
					'servicecontractsid' => $this->integer(10)->notNull(),
					'start_date' => $this->date(),
					'end_date' => $this->date(),
					'sc_related_to' => $this->integer(10),
					'tracking_unit' => $this->stringType(100),
					'total_units' => $this->decimal('5,2'),
					'used_units' => $this->decimal('5,2'),
					'subject' => $this->stringType(100),
					'due_date' => $this->date(),
					'planned_duration' => $this->stringType(256),
					'actual_duration' => $this->stringType(256),
					'contract_status' => $this->stringType(200),
					'priority' => $this->stringType(200),
					'contract_type' => $this->stringType(200),
					'progress' => $this->decimal('5,2'),
					'contract_no' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['servicecontracts_pk', 'servicecontractsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecontractscf' => [
				'columns' => [
					'servicecontractsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['servicecontractscf_pk', 'servicecontractsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seticketsrel' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'ticketid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['seticketsrel_pk', ['crmid', 'ticketid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_blocks' => [
				'columns' => [
					'blockid' => $this->integer(10)->notNull(),
					'label' => $this->stringType(250),
					'sequence' => $this->integer(10),
					'icon' => $this->stringType(),
					'type' => $this->smallInteger(1),
					'linkto' => $this->text(),
					'admin_access' => $this->text(),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1),
				],
				'primaryKeys' => [
					['settings_blocks_pk', 'blockid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_blocks_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_field' => [
				'columns' => [
					'fieldid' => $this->integer(10)->autoIncrement()->notNull(),
					'blockid' => $this->integer(10),
					'name' => $this->stringType(250),
					'iconpath' => $this->stringType(300),
					'description' => $this->stringType(250),
					'linkto' => $this->text(),
					'sequence' => $this->integer(10),
					'active' => $this->integer(10)->defaultValue(0),
					'pinned' => $this->integer(1)->defaultValue(0),
					'admin_access' => $this->text(),
				],
				'primaryKeys' => [
					['settings_field_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_sharedcalendar' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'sharedid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['sharedcalendar_pk', ['userid', 'sharedid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_shareduserinfo' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull()->defaultValue(0),
					'shareduserid' => $this->integer(10)->notNull()->defaultValue(0),
					'color' => $this->stringType(50),
					'visible' => $this->integer(10)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_shorturls' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'uid' => $this->stringType(50),
					'handler_path' => $this->stringType(400),
					'handler_class' => $this->stringType(100),
					'handler_function' => $this->stringType(100),
					'handler_data' => $this->stringType(),
					'onetime' => $this->integer(5),
				],
				'primaryKeys' => [
					['shorturls_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifier' => [
				'columns' => [
					'smsnotifierid' => $this->integer(10)->notNull(),
					'message' => $this->text(),
					'smsnotifier_status' => $this->stringType(),
				],
				'primaryKeys' => [
					['smsnotifier_pk', 'smsnotifierid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifiercf' => [
				'columns' => [
					'smsnotifierid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['smsnotifiercf_pk', 'smsnotifierid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_soapservice' => [
				'columns' => [
					'id' => $this->integer(10),
					'type' => $this->stringType(25),
					'sessionid' => $this->stringType(100),
					'lang' => $this->stringType(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_squoteenquiries_status' => [
				'columns' => [
					'squoteenquiries_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'squoteenquiries_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['squoteenquiries_status_pk', 'squoteenquiries_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_squotes_status' => [
				'columns' => [
					'squotes_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'squotes_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['squotes_status_pk', 'squotes_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_srecurringorders_status' => [
				'columns' => [
					'srecurringorders_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'srecurringorders_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['srecurringorders_status_pk', 'srecurringorders_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_srequirementscards_status' => [
				'columns' => [
					'srequirementscards_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'srequirementscards_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['srequirementscards_status_pk', 'srequirementscards_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_source' => [
				'columns' => [
					'ssalesprocesses_sourceid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssalesprocesses_source' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['ssalesprocesses_source_pk', 'ssalesprocesses_sourceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_status' => [
				'columns' => [
					'ssalesprocesses_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssalesprocesses_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['ssalesprocesses_status_pk', 'ssalesprocesses_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_type' => [
				'columns' => [
					'ssalesprocesses_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssalesprocesses_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['ssalesprocesses_type_pk', 'ssalesprocesses_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssingleorders_source' => [
				'columns' => [
					'ssingleorders_sourceid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssingleorders_source' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['ssingleorders_source_pk', 'ssingleorders_sourceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssingleorders_status' => [
				'columns' => [
					'ssingleorders_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssingleorders_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['ssingleorders_status_pk', 'ssingleorders_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssservicesstatus' => [
				'columns' => [
					'ssservicesstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ssservicesstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['ssservicesstatus_pk', 'ssservicesstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssservicesstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_start_hour' => [
				'columns' => [
					'start_hourid' => $this->integer(10)->autoIncrement()->notNull(),
					'start_hour' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['start_hour_pk', 'start_hourid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_start_hour_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_state' => [
				'columns' => [
					'stateid' => $this->integer(10)->autoIncrement()->notNull(),
					'state' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['state_pk', 'stateid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_state_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_status' => [
				'columns' => [
					'statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['status_pk', 'statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_storage_status' => [
				'columns' => [
					'storage_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'storage_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['storage_status_pk', 'storage_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_storage_type' => [
				'columns' => [
					'storage_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'storage_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['storage_type_pk', 'storage_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subindustry' => [
				'columns' => [
					'subindustryid' => $this->integer(10)->autoIncrement()->notNull(),
					'subindustry' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['subindustry_pk', 'subindustryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subindustry_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subunit' => [
				'columns' => [
					'subunitid' => $this->integer(10)->autoIncrement()->notNull(),
					'subunit' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['subunit_pk', 'subunitid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_support_processes' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'ticket_status_indicate_closing' => $this->stringType()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_svendorenquiries_status' => [
				'columns' => [
					'svendorenquiries_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'svendorenquiries_status' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['svendorenquiries_status_pk', 'svendorenquiries_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_systems' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'server' => $this->stringType(100),
					'server_port' => $this->integer(10),
					'server_username' => $this->stringType(100),
					'server_password' => $this->stringType(100),
					'server_type' => $this->stringType(20),
					'smtp_auth' => $this->stringType(5),
					'server_path' => $this->stringType(256),
					'from_email_field' => $this->stringType(50),
				],
				'primaryKeys' => [
					['systems_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tab' => [
				'columns' => [
					'tabid' => $this->integer(19)->notNull()->defaultValue(0),
					'name' => $this->stringType(25)->notNull(),
					'presence' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
					'tabsequence' => $this->smallInteger(5)->notNull()->defaultValue(0),
					'tablabel' => $this->stringType(25)->notNull(),
					'modifiedby' => $this->smallInteger(5),
					'modifiedtime' => $this->integer(10),
					'customized' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'ownedby' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'isentitytype' => $this->smallInteger(1)->notNull()->defaultValue(1),
					'version' => $this->stringType(10),
					'parent' => $this->stringType(30),
					'color' => $this->stringType(30),
					'coloractive' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(1),
					'customized' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'ownedby' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'isentitytype' => $this->tinyInteger(1)->notNull()->defaultValue(1),
					'coloractive' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['tab_pk', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taskpriority' => [
				'columns' => [
					'taskpriorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'taskpriority' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['taskpriority_pk', 'taskpriorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taskpriority_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketcf' => [
				'columns' => [
					'ticketid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['ticketcf_pk', 'ticketid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketpriorities' => [
				'columns' => [
					'ticketpriorities_id' => $this->integer(10)->autoIncrement()->notNull(),
					'ticketpriorities' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
					'color' => $this->stringType(25)->defaultValue('	#E6FAD8'),
				],
				'primaryKeys' => [
					['ticketpriorities_pk', 'ticketpriorities_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketpriorities_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketseverities' => [
				'columns' => [
					'ticketseverities_id' => $this->integer(10)->autoIncrement()->notNull(),
					'ticketseverities' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['ticketseverities_pk', 'ticketseverities_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketseverities_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketstatus' => [
				'columns' => [
					'ticketstatus_id' => $this->integer(10)->autoIncrement()->notNull(),
					'ticketstatus' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'primaryKeys' => [
					['ticketstatus_pk', 'ticketstatus_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_time_zone' => [
				'columns' => [
					'time_zoneid' => $this->integer(10)->autoIncrement()->notNull(),
					'time_zone' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['time_zone_pk', 'time_zoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_time_zone_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_timecontrol_type' => [
				'columns' => [
					'timecontrol_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'timecontrol_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'primaryKeys' => [
					['timecontrol_type_pk', 'timecontrol_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_timecontrol_type_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_group_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'relatedtabid' => $this->integer(10)->notNull(),
					'sharedgroupid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_read_group_rel_sharing_per_pk', ['userid', 'tabid', 'relatedtabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_group_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'sharedgroupid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_read_group_sharing_per_pk', ['userid', 'tabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_user_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'relatedtabid' => $this->integer(10)->notNull(),
					'shareduserid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_read_user_rel_sharing_per_pk', ['userid', 'tabid', 'relatedtabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_user_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'shareduserid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_read_user_sharing_per_pk', ['userid', 'tabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_group_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'relatedtabid' => $this->integer(10)->notNull(),
					'sharedgroupid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_write_group_rel_sharing_per_pk', ['userid', 'tabid', 'relatedtabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_group_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'sharedgroupid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_write_group_sharing_per_pk', ['userid', 'tabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_user_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'relatedtabid' => $this->integer(10)->notNull(),
					'shareduserid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_write_user_rel_sharing_per_pk', ['userid', 'tabid', 'relatedtabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_user_sharing_per' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'shareduserid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['tmp_write_user_sharing_per_pk', ['userid', 'tabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tracking_unit' => [
				'columns' => [
					'tracking_unitid' => $this->integer(10)->autoIncrement()->notNull(),
					'tracking_unit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['tracking_unit_pk', 'tracking_unitid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tracking_unit_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_trees_templates' => [
				'columns' => [
					'templateid' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(),
					'module' => $this->integer(10),
					'access' => $this->integer(1)->defaultValue(1),
					'share' => $this->stringType(),
				],
				'primaryKeys' => [
					['trees_templates_pk', 'templateid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_trees_templates_data' => [
				'columns' => [
					'templateid' => $this->smallInteger(5)->unsigned()->notNull(),
					'name' => $this->stringType()->notNull(),
					'tree' => $this->stringType()->notNull(),
					'parenttrre' => $this->stringType()->notNull(),
					'depth' => $this->smallInteger(3)->unsigned()->notNull(),
					'label' => $this->stringType()->notNull(),
					'state' => $this->stringType(100)->notNull()->defaultValue(''),
					'icon' => $this->stringType()->notNull()->defaultValue(''),
				],
				'columns_mysql' => [
					'depth' => $this->tinyInteger(3)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_troubletickets' => [
				'columns' => [
					'ticketid' => $this->integer(10)->notNull(),
					'ticket_no' => $this->stringType(100)->notNull(),
					'groupname' => $this->stringType(100),
					'parent_id' => $this->integer(10),
					'product_id' => $this->integer(10),
					'priority' => $this->stringType(200),
					'severity' => $this->stringType(200),
					'status' => $this->stringType(200),
					'category' => $this->stringType(200),
					'title' => $this->stringType()->notNull(),
					'solution' => $this->text(),
					'update_log' => $this->text(),
					'version_id' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'servicecontractsid' => $this->integer(10),
					'attention' => $this->text(),
					'pssold_id' => $this->integer(10),
					'ordertime' => $this->decimal('10,2'),
					'from_portal' => $this->smallInteger(1),
					'contract_type' => $this->stringType(),
					'contracts_end_date' => $this->date(),
					'report_time' => $this->integer(10),
					'response_time' => $this->dateTime(),
				],
				'index' => [
					['troubletickets_ticketid_idx', 'ticketid'],
					['troubletickets_status_idx', 'status'],
					['parent_id', 'parent_id'],
					['product_id', 'product_id'],
					['servicecontractsid', 'servicecontractsid'],
					['pssold_id', 'pssold_id'],
					['ticket_no', 'ticket_no'],
				],
				'primaryKeys' => [
					['troubletickets_pk', 'ticketid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_usageunit' => [
				'columns' => [
					'usageunitid' => $this->integer(10)->autoIncrement()->notNull(),
					'usageunit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['usageunit_pk', 'usageunitid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_usageunit_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user2mergefields' => [
				'columns' => [
					'userid' => $this->integer(10),
					'tabid' => $this->smallInteger(5),
					'fieldid' => $this->integer(10),
					'visible' => $this->integer(2),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user2role' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'roleid' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['user2role_pk', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user_module_preferences' => [
				'columns' => [
					'userid' => $this->stringType(30)->notNull(),
					'tabid' => $this->integer(19)->notNull(),
					'default_cvid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['user_module_preferences_pk', ['userid', 'tabid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'user_name' => $this->stringType(32),
					'user_password' => $this->stringType(200),
					'cal_color' => $this->stringType(25)->defaultValue('#E6FAD8'),
					'first_name' => $this->stringType(30),
					'last_name' => $this->stringType(30),
					'reports_to_id' => $this->integer(10)->unsigned(),
					'is_admin' => $this->stringType(3)->defaultValue(0),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
					'description' => $this->text(),
					'date_entered' => $this->timestamp()->null(),
					'date_modified' => $this->timestamp()->null(),
					'modified_user_id' => $this->stringType(36),
					'email1' => $this->stringType(100),
					'status' => $this->stringType(25),
					'user_preferences' => $this->text(),
					'tz' => $this->stringType(30),
					'holidays' => $this->stringType(60),
					'namedays' => $this->stringType(60),
					'workdays' => $this->stringType(30),
					'weekstart' => $this->integer(10),
					'date_format' => $this->stringType(200),
					'hour_format' => $this->stringType(30)->defaultValue('am/pm'),
					'start_hour' => $this->stringType(30)->defaultValue('10:00'),
					'end_hour' => $this->stringType(30)->defaultValue('23:00'),
					'activity_view' => $this->stringType(200)->defaultValue('Today'),
					'lead_view' => $this->stringType(200)->defaultValue('Today'),
					'imagename' => $this->stringType(250),
					'deleted' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'confirm_password' => $this->stringType(300),
					'internal_mailer' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'reminder_interval' => $this->stringType(100),
					'reminder_next_time' => $this->stringType(100),
					'crypt_type' => $this->stringType(20)->notNull()->defaultValue('MD5'),
					'accesskey' => $this->stringType(36),
					'theme' => $this->stringType(100),
					'language' => $this->stringType(36),
					'time_zone' => $this->stringType(200),
					'currency_grouping_pattern' => $this->stringType(100),
					'currency_decimal_separator' => $this->stringType(2),
					'currency_grouping_separator' => $this->stringType(2),
					'currency_symbol_placement' => $this->stringType(20),
					'phone_crm_extension' => $this->stringType(100),
					'no_of_currency_decimals' => $this->smallInteger(1)->unsigned(),
					'truncate_trailing_zeros' => $this->smallInteger(1)->unsigned(),
					'dayoftheweek' => $this->stringType(100),
					'callduration' => $this->smallInteger(3)->unsigned(),
					'othereventduration' => $this->smallInteger(3)->unsigned(),
					'calendarsharedtype' => $this->stringType(100),
					'default_record_view' => $this->stringType(10),
					'leftpanelhide' => $this->smallInteger(3)->unsigned(),
					'rowheight' => $this->stringType(10),
					'defaulteventstatus' => $this->stringType(50),
					'defaultactivitytype' => $this->stringType(50),
					'is_owner' => $this->stringType(5),
					'emailoptout' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
					'available' => $this->smallInteger(1)->defaultValue(0),
					'auto_assign' => $this->smallInteger(1)->defaultValue(0),
					'records_limit' => $this->integer(10),
				],
				'columns_mysql' => [
					'deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'internal_mailer' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'no_of_currency_decimals' => $this->tinyInteger(1)->unsigned(),
					'truncate_trailing_zeros' => $this->tinyInteger(1)->unsigned(),
					'leftpanelhide' => $this->tinyInteger(3)->unsigned(),
					'emailoptout' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(1),
					'available' => $this->tinyInteger(1)->defaultValue(0),
					'auto_assign' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users2group' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['users2group_pk', ['groupid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users_last_import' => [
				'columns' => [
					'id' => $this->integer(36)->autoIncrement()->notNull(),
					'assigned_user_id' => $this->stringType(36),
					'bean_type' => $this->stringType(36),
					'bean_id' => $this->integer(10),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['users_last_import_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_userscf' => [
				'columns' => [
					'usersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['userscf_pk', 'usersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendor' => [
				'columns' => [
					'vendorid' => $this->integer(10)->notNull()->defaultValue(0),
					'vendor_no' => $this->stringType(100)->notNull(),
					'vendorname' => $this->stringType(100),
					'phone' => $this->stringType(100),
					'email' => $this->stringType(100),
					'website' => $this->stringType(100),
					'glacct' => $this->stringType(200),
					'category' => $this->stringType(50),
					'description' => $this->text(),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['vendor_pk', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendoraddress' => [
				'columns' => [
					'vendorid' => $this->integer(10)->notNull(),
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
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
					'buildingnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
				],
				'primaryKeys' => [
					['vendoraddress_pk', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendorcf' => [
				'columns' => [
					'vendorid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['vendorcf_pk', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendorcontactrel' => [
				'columns' => [
					'vendorid' => $this->integer(10)->notNull()->defaultValue(0),
					'contactid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['vendorcontactrel_pk', ['vendorid', 'contactid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_verification' => [
				'columns' => [
					'verificationid' => $this->integer(10)->autoIncrement()->notNull(),
					'verification' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['verification_pk', 'verificationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_verification_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_version' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'old_version' => $this->stringType(30),
					'current_version' => $this->stringType(30),
				],
				'primaryKeys' => [
					['version_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_version_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_visibility' => [
				'columns' => [
					'visibilityid' => $this->integer(10)->autoIncrement()->notNull(),
					'visibility' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['visibility_pk', 'visibilityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_visibility_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_widgets' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->integer(19),
					'type' => $this->stringType(30),
					'label' => $this->stringType(100),
					'wcol' => $this->smallInteger(1)->defaultValue(1),
					'sequence' => $this->smallInteger(2),
					'nomargin' => $this->smallInteger(1)->defaultValue(0),
					'data' => $this->text(),
				],
				'columns_mysql' => [
					'wcol' => $this->tinyInteger(1)->defaultValue(1),
					'sequence' => $this->tinyInteger(2),
					'nomargin' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['widgets_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(25)->notNull(),
					'handler_path' => $this->stringType()->notNull(),
					'handler_class' => $this->stringType(64)->notNull(),
					'ismodule' => $this->integer(3)->notNull(),
				],
				'primaryKeys' => [
					['ws_entity_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_fieldtype' => [
				'columns' => [
					'fieldtypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'table_name' => $this->stringType(50)->notNull(),
					'field_name' => $this->stringType(50)->notNull(),
					'fieldtype' => $this->stringType(200)->notNull(),
				],
				'primaryKeys' => [
					['ws_entity_fieldtype_pk', 'fieldtypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_fieldtype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_name' => [
				'columns' => [
					'entity_id' => $this->integer(10)->notNull(),
					'name_fields' => $this->stringType(50)->notNull(),
					'index_field' => $this->stringType(50)->notNull(),
					'table_name' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['ws_entity_name_pk', 'entity_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_referencetype' => [
				'columns' => [
					'fieldtypeid' => $this->integer(10)->notNull(),
					'type' => $this->stringType(25)->notNull(),
				],
				'primaryKeys' => [
					['ws_entity_referencetype_pk', ['fieldtypeid', 'type']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_tables' => [
				'columns' => [
					'webservice_entity_id' => $this->integer(10)->notNull(),
					'table_name' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['ws_entity_tables_pk', ['webservice_entity_id', 'table_name']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_fieldtype' => [
				'columns' => [
					'fieldtypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'uitype' => $this->smallInteger(3)->notNull(),
					'fieldtype' => $this->stringType(200)->notNull(),
				],
				'primaryKeys' => [
					['ws_fieldtype_pk', 'fieldtypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation' => [
				'columns' => [
					'operationid' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(128)->notNull(),
					'handler_path' => $this->stringType()->notNull(),
					'handler_method' => $this->stringType(64)->notNull(),
					'type' => $this->stringType(8)->notNull(),
					'prelogin' => $this->integer(3)->notNull(),
				],
				'primaryKeys' => [
					['ws_operation_pk', 'operationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation_parameters' => [
				'columns' => [
					'operationid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(128)->notNull(),
					'type' => $this->stringType(64)->notNull(),
					'sequence' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ws_operation_parameters_pk', ['operationid', 'name']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_referencetype' => [
				'columns' => [
					'fieldtypeid' => $this->integer(10)->notNull(),
					'type' => $this->stringType(25)->notNull(),
				],
				'primaryKeys' => [
					['ws_referencetype_pk', ['fieldtypeid', 'type']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_userauthtoken' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'token' => $this->stringType(36)->notNull(),
					'expiretime' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ws_userauthtoken_pk', ['userid', 'expiretime']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_currencyupdate' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'currency_id' => $this->integer(10)->notNull(),
					'fetch_date' => $this->date()->notNull(),
					'exchange_date' => $this->date()->notNull(),
					'exchange' => $this->decimal('10,4')->notNull(),
					'bank_id' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['yetiforce_currencyupdate_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_currencyupdate_banks' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'bank_name' => $this->stringType()->notNull(),
					'active' => $this->integer(1)->notNull(),
				],
				'primaryKeys' => [
					['yetiforce_currencyupdate_banks_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mail_config' => [
				'columns' => [
					'type' => $this->stringType(50),
					'name' => $this->stringType(50),
					'value' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mail_quantities' => [
				'columns' => [
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'num' => $this->integer(10)->unsigned()->defaultValue(0),
					'status' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['yetiforce_mail_quantities_pk', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_menu' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'role' => $this->integer(10),
					'parentid' => $this->integer(10)->defaultValue(0),
					'type' => $this->smallInteger(1),
					'sequence' => $this->integer(3),
					'module' => $this->integer(19),
					'label' => $this->stringType(100),
					'newwindow' => $this->smallInteger(1)->defaultValue(0),
					'dataurl' => $this->text(),
					'showicon' => $this->smallInteger(1)->defaultValue(0),
					'icon' => $this->stringType(),
					'sizeicon' => $this->stringType(),
					'hotkey' => $this->stringType(30),
					'filters' => $this->stringType(),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1),
					'newwindow' => $this->tinyInteger(1)->defaultValue(0),
					'showicon' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['yetiforce_menu_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_updates' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'time' => $this->timestamp()->null(),
					'user' => $this->stringType(50),
					'name' => $this->stringType(100),
					'from_version' => $this->stringType(10),
					'to_version' => $this->stringType(10),
					'result' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'result' => $this->tinyInteger(1),
				],
				'primaryKeys' => [
					['yetiforce_updates_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_1_vtiger_relcriteria', 'vtiger_relcriteria', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['vtiger_relcriteria_grouping_ibfk_1', 'vtiger_relcriteria_grouping', 'queryid', 'vtiger_relcriteria', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_report', 'vtiger_report', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportdatefilter', 'vtiger_reportdatefilter', 'datefilterid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportgroupbycolumn', 'vtiger_reportgroupbycolumn', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportmodules', 'vtiger_reportmodules', 'reportmodulesid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportsortcol', 'vtiger_reportsortcol', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportsummary', 'vtiger_reportsummary', 'reportsummaryid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['vtiger_reservations', 'vtiger_reservations', 'reservationsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_reservationscf', 'vtiger_reservationscf', 'reservationsid', 'vtiger_reservations', 'reservationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_role2picklist', 'vtiger_role2picklist', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_role2picklist', 'vtiger_role2picklist', 'picklistid', 'vtiger_picklist', 'picklistid', 'CASCADE', 'RESTRICT'],
			['vtiger_role2profile_ibfk_1', 'vtiger_role2profile', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['vtiger_role2profile_ibfk_2', 'vtiger_role2profile', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_salesmanattachmentsrel', 'vtiger_salesmanattachmentsrel', 'attachmentsid', 'vtiger_attachments', 'attachmentsid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_salesmanticketrel', 'vtiger_salesmanticketrel', 'smid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['vtiger_scheduled_reports_ibfk_1', 'vtiger_scheduled_reports', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['vtiger_schedulereports_ibfk_1', 'vtiger_schedulereports', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_selectcolumn', 'vtiger_selectcolumn', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_senotesrel', 'vtiger_senotesrel', 'notesid', 'vtiger_notes', 'notesid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_seproductsrel', 'vtiger_seproductsrel', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_service', 'vtiger_service', 'serviceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecf_ibfk_1', 'vtiger_servicecf', 'serviceid', 'vtiger_service', 'serviceid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecontracts_ibfk_1', 'vtiger_servicecontracts', 'servicecontractsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecontractscf_ibfk_1', 'vtiger_servicecontractscf', 'servicecontractsid', 'vtiger_servicecontracts', 'servicecontractsid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_seticketsrel', 'vtiger_seticketsrel', 'ticketid', 'vtiger_troubletickets', 'ticketid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_settings_field', 'vtiger_settings_field', 'blockid', 'vtiger_settings_blocks', 'blockid', 'CASCADE', 'RESTRICT'],
			['vtiger_smsnotifier_ibfk_1', 'vtiger_smsnotifier', 'smsnotifierid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_smsnotifiercf_ibfk_1', 'vtiger_smsnotifiercf', 'smsnotifierid', 'vtiger_smsnotifier', 'smsnotifierid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_tab_info', 'vtiger_tab_info', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'CASCADE'],
			['fk_1_vtiger_ticketcf', 'vtiger_ticketcf', 'ticketid', 'vtiger_troubletickets', 'ticketid', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_read_user_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_write_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_troubletickets', 'vtiger_troubletickets', 'ticketid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_user2role', 'vtiger_user2role', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_user_module_preferences', 'vtiger_user_module_preferences', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'CASCADE'],
			['fk_2_vtiger_users2group', 'vtiger_users2group', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['vtiger_userscf_ibfk_1', 'vtiger_userscf', 'usersid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_vendor', 'vtiger_vendor', 'vendorid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_vendoraddress_ibfk_1', 'vtiger_vendoraddress', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_vendorcf', 'vtiger_vendorcf', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_vendorcontactrel', 'vtiger_vendorcontactrel', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['vtiger_widgets_ibfk_1', 'vtiger_widgets', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['vtiger_fk_1_actors_referencetype', 'vtiger_ws_entity_referencetype', 'fieldtypeid', 'vtiger_ws_entity_fieldtype', 'fieldtypeid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ws_actor_tables', 'vtiger_ws_entity_tables', 'webservice_entity_id', 'vtiger_ws_entity', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_referencetype', 'vtiger_ws_referencetype', 'fieldtypeid', 'vtiger_ws_fieldtype', 'fieldtypeid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osscurrencies', 'yetiforce_currencyupdate', 'currency_id', 'vtiger_currency_info', 'id', 'CASCADE', 'RESTRICT'],
			['yetiforce_mail_quantities_ibfk_1', 'yetiforce_mail_quantities', 'userid', 'roundcube_users', 'user_id', 'CASCADE', 'RESTRICT'],
			['yetiforce_menu_ibfk_1', 'yetiforce_menu', 'module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}
}
