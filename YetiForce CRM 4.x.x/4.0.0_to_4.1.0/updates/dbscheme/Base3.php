<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base3 extends \DbType
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_glacct' => [
				'columns' => [
					'glacctid' => $this->integer(10)->autoIncrement()->notNull(),
					'glacct' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['glacct_pk', 'glacctid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_glacct_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2grouprel' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'containsgroupid' => $this->integer(10)->unsigned()->notNull(),
				],
				'primaryKeys' => [
					['group2grouprel_pk', ['groupid', 'containsgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2modules' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'tabid' => $this->integer(19)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2role' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'roleid' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['group2role_pk', ['groupid', 'roleid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2rs' => [
				'columns' => [
					'groupid' => $this->integer(10)->unsigned()->notNull(),
					'roleandsubid' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['group2rs_pk', ['groupid', 'roleandsubid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_groups' => [
				'columns' => [
					'groupid' => $this->integer(10)->notNull(),
					'groupname' => $this->stringType(100),
					'description' => $this->text(),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
					'modules' => $this->stringType(),
				],
				'primaryKeys' => [
					['groups_pk', 'groupid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement' => [
				'columns' => [
					'holidaysentitlementid' => $this->integer(10)->notNull()->defaultValue(0),
					'holidaysentitlement_no' => $this->stringType(),
					'holidaysentitlement_year' => $this->stringType(50),
					'days' => $this->integer(3)->defaultValue(0),
					'ossemployeesid' => $this->integer(10),
				],
				'primaryKeys' => [
					['holidaysentitlement_pk', 'holidaysentitlementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement_year' => [
				'columns' => [
					'holidaysentitlement_yearid' => $this->integer(10)->autoIncrement()->notNull(),
					'holidaysentitlement_year' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['holidaysentitlement_year_pk', 'holidaysentitlement_yearid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement_year_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlementcf' => [
				'columns' => [
					'holidaysentitlementid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['holidaysentitlementcf_pk', 'holidaysentitlementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_hour_format' => [
				'columns' => [
					'hour_formatid' => $this->integer(10)->autoIncrement()->notNull(),
					'hour_format' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['hour_format_pk', 'hour_formatid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_hour_format_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideas' => [
				'columns' => [
					'ideasid' => $this->integer(10)->notNull()->defaultValue(0),
					'ideas_no' => $this->stringType(),
					'subject' => $this->stringType(),
					'ideasstatus' => $this->stringType()->defaultValue(''),
					'extent_description' => $this->text(),
				],
				'primaryKeys' => [
					['ideas_pk', 'ideasid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideascf' => [
				'columns' => [
					'ideasid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ideascf_pk', 'ideasid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideasstatus' => [
				'columns' => [
					'ideasstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ideasstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['ideasstatus_pk', 'ideasstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideasstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igdn_status' => [
				'columns' => [
					'igdn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'igdn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igdn_status_pk', 'igdn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igdnc_status' => [
				'columns' => [
					'igdnc_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'igdnc_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igdnc_status_pk', 'igdnc_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igin_status' => [
				'columns' => [
					'igin_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'igin_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igin_status_pk', 'igin_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igrn_status' => [
				'columns' => [
					'igrn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'igrn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igrn_status_pk', 'igrn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igrnc_status' => [
				'columns' => [
					'igrnc_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'igrnc_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['igrnc_status_pk', 'igrnc_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_iidn_status' => [
				'columns' => [
					'iidn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'iidn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['iidn_status_pk', 'iidn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_locks' => [
				'columns' => [
					'vtiger_import_lock_id' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'importid' => $this->integer(10)->notNull(),
					'locked_since' => $this->dateTime(),
				],
				'primaryKeys' => [
					['import_locks_pk', 'vtiger_import_lock_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_maps' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(36)->notNull(),
					'module' => $this->stringType(36)->notNull(),
					'content' => $this->binary(),
					'has_header' => $this->integer(1)->notNull()->defaultValue(1),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
					'date_entered' => $this->timestamp()->null(),
					'date_modified' => $this->timestamp()->null(),
					'assigned_user_id' => $this->stringType(36),
					'is_published' => $this->stringType(3)->notNull()->defaultValue('no'),
				],
				'primaryKeys' => [
					['import_maps_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_queue' => [
				'columns' => [
					'importid' => $this->integer(10)->autoIncrement()->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'field_mapping' => $this->text(),
					'default_values' => $this->text(),
					'merge_type' => $this->integer(10),
					'merge_fields' => $this->text(),
					'temp_status' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'temp_status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['import_queue_pk', 'importid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_industry' => [
				'columns' => [
					'industryid' => $this->integer(10)->autoIncrement()->notNull(),
					'industry' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['industry_pk', 'industryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_industry_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_internal_tickets_status' => [
				'columns' => [
					'internal_tickets_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'internal_tickets_status' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'picklist_valueid' => $this->smallInteger(5)->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['internal_tickets_status_pk', 'internal_tickets_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventory_tandc' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'type' => $this->stringType(30)->notNull(),
					'tandc' => $this->text(),
				],
				'primaryKeys' => [
					['inventory_tandc_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventoryproductrel' => [
				'columns' => [
					'id' => $this->integer(10),
					'productid' => $this->integer(10),
					'sequence_no' => $this->integer(4),
					'quantity' => $this->decimal('25,3'),
					'listprice' => $this->decimal('28,8'),
					'discount_percent' => $this->decimal('7,3'),
					'discount_amount' => $this->decimal('28,8'),
					'comment' => $this->stringType(500),
					'description' => $this->text(),
					'incrementondel' => $this->integer(10)->notNull()->defaultValue(0),
					'lineitem_id' => $this->integer(10)->autoIncrement()->notNull(),
					'tax' => $this->stringType(10),
					'tax1' => $this->decimal('7,3'),
					'tax2' => $this->decimal('7,3'),
					'tax3' => $this->decimal('7,3'),
					'purchase' => $this->decimal('10,2'),
					'margin' => $this->decimal('10,2'),
					'marginp' => $this->decimal('10,2'),
				],
				'primaryKeys' => [
					['inventoryproductrel_pk', 'lineitem_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventoryproductrel_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventorysubproductrel' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'sequence_no' => $this->integer(10)->notNull(),
					'productid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ipreorder_status' => [
				'columns' => [
					'ipreorder_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ipreorder_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['ipreorder_status_pk', 'ipreorder_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istdn_status' => [
				'columns' => [
					'istdn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'istdn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['istdn_status_pk', 'istdn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istn_status' => [
				'columns' => [
					'istn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'istn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['istn_status_pk', 'istn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istn_type' => [
				'columns' => [
					'istn_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'istn_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['istn_type_pk', 'istn_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istrn_status' => [
				'columns' => [
					'istrn_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'istrn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['istrn_status_pk', 'istrn_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_knowledgebase_status' => [
				'columns' => [
					'knowledgebase_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'knowledgebase_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['knowledgebase_status_pk', 'knowledgebase_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_knowledgebase_view' => [
				'columns' => [
					'knowledgebase_viewid' => $this->integer(10)->autoIncrement()->notNull(),
					'knowledgebase_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['knowledgebase_view_pk', 'knowledgebase_viewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_language' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'prefix' => $this->stringType(10)->notNull(),
					'label' => $this->stringType(30)->notNull(),
					'lastupdated' => $this->dateTime(),
					'sequence' => $this->integer(10),
					'isdefault' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'active' => $this->smallInteger(1)->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'isdefault' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['language_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_language_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_layout' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'name' => $this->stringType(50),
					'label' => $this->stringType(30),
					'lastupdated' => $this->dateTime(),
					'isdefault' => $this->smallInteger(1),
					'active' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'isdefault' => $this->tinyInteger(1),
					'active' => $this->tinyInteger(1),
				],
				'primaryKeys' => [
					['layout_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lead_view' => [
				'columns' => [
					'lead_viewid' => $this->integer(10)->autoIncrement()->notNull(),
					'lead_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['lead_view_pk', 'lead_viewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lead_view_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadaddress' => [
				'columns' => [
					'leadaddressid' => $this->integer(10)->notNull()->defaultValue(0),
					'phone' => $this->stringType(50),
					'mobile' => $this->stringType(50),
					'fax' => $this->stringType(50),
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
					['leadaddress_pk', 'leadaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leaddetails' => [
				'columns' => [
					'leadid' => $this->integer(10)->notNull(),
					'lead_no' => $this->stringType(100)->notNull(),
					'email' => $this->stringType(100),
					'interest' => $this->stringType(50),
					'firstname' => $this->stringType(40),
					'salutation' => $this->stringType(200),
					'lastname' => $this->stringType(80),
					'company' => $this->stringType(100)->notNull(),
					'annualrevenue' => $this->decimal('25,8'),
					'industry' => $this->stringType(200),
					'campaign' => $this->stringType(30),
					'leadstatus' => $this->stringType(50),
					'leadsource' => $this->stringType(200),
					'converted' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'licencekeystatus' => $this->stringType(50),
					'space' => $this->stringType(250),
					'comments' => $this->text(),
					'priority' => $this->stringType(50),
					'demorequest' => $this->stringType(50),
					'partnercontact' => $this->stringType(50),
					'productversion' => $this->stringType(20),
					'product' => $this->stringType(50),
					'maildate' => $this->date(),
					'nextstepdate' => $this->date(),
					'fundingsituation' => $this->stringType(50),
					'purpose' => $this->stringType(50),
					'evaluationstatus' => $this->stringType(50),
					'transferdate' => $this->date(),
					'revenuetype' => $this->stringType(50),
					'noofemployees' => $this->integer(50),
					'secondaryemail' => $this->stringType(100),
					'assignleadchk' => $this->integer(1)->defaultValue(0),
					'noapprovalcalls' => $this->smallInteger(1),
					'noapprovalemails' => $this->smallInteger(1),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'subindustry' => $this->stringType()->defaultValue(''),
					'atenttion' => $this->text(),
					'leads_relation' => $this->stringType(),
					'legal_form' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'converted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['leaddetails_pk', 'leadid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leads_relation' => [
				'columns' => [
					'leads_relationid' => $this->integer(10)->autoIncrement()->notNull(),
					'leads_relation' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['leads_relation_pk', 'leads_relationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leads_relation_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadscf' => [
				'columns' => [
					'leadid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['leadscf_pk', 'leadid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsource' => [
				'columns' => [
					'leadsourceid' => $this->integer(10)->autoIncrement()->notNull(),
					'leadsource' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['leadsource_pk', 'leadsourceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsource_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstage' => [
				'columns' => [
					'leadstageid' => $this->integer(10)->autoIncrement()->notNull(),
					'stage' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['leadstage_pk', 'leadstageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstatus' => [
				'columns' => [
					'leadstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'leadstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'primaryKeys' => [
					['leadstatus_pk', 'leadstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsubdetails' => [
				'columns' => [
					'leadsubscriptionid' => $this->integer(10)->notNull()->defaultValue(0),
					'website' => $this->stringType(),
					'callornot' => $this->integer(1)->defaultValue(0),
					'readornot' => $this->integer(1)->defaultValue(0),
					'empct' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['leadsubdetails_pk', 'leadsubscriptionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_legal_form' => [
				'columns' => [
					'legal_formid' => $this->integer(10)->autoIncrement()->notNull(),
					'legal_form' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['legal_form_pk', 'legal_formid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_legal_form_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersin' => [
				'columns' => [
					'lettersinid' => $this->integer(10)->notNull()->defaultValue(0),
					'number' => $this->stringType(),
					'title' => $this->stringType(),
					'relatedid' => $this->integer(10),
					'person_receiving' => $this->integer(10),
					'parentid' => $this->integer(10),
					'date_adoption' => $this->date(),
					'lin_type_ship' => $this->stringType()->defaultValue(''),
					'lin_type_doc' => $this->text(),
					'lin_status' => $this->stringType()->defaultValue(''),
					'deadline_reply' => $this->date(),
					'cocument_no' => $this->stringType(100)->defaultValue(''),
					'no_internal' => $this->stringType(100)->defaultValue(''),
					'lin_dimensions' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['lettersin_pk', 'lettersinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersincf' => [
				'columns' => [
					'lettersinid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['lettersincf_pk', 'lettersinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersout' => [
				'columns' => [
					'lettersoutid' => $this->integer(10)->notNull()->defaultValue(0),
					'number' => $this->stringType(),
					'title' => $this->stringType(),
					'relatedid' => $this->integer(10),
					'person_receiving' => $this->integer(10),
					'parentid' => $this->integer(10),
					'date_adoption' => $this->date(),
					'lout_type_ship' => $this->stringType()->defaultValue(''),
					'lout_type_doc' => $this->text(),
					'lout_status' => $this->stringType()->defaultValue(''),
					'deadline_reply' => $this->date(),
					'cocument_no' => $this->stringType(100)->defaultValue(''),
					'no_internal' => $this->stringType(100)->defaultValue(''),
					'lout_dimensions' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['lettersout_pk', 'lettersoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersoutcf' => [
				'columns' => [
					'lettersoutid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['lettersoutcf_pk', 'lettersoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_dimensions' => [
				'columns' => [
					'lin_dimensionsid' => $this->integer(10)->autoIncrement()->notNull(),
					'lin_dimensions' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['lin_dimensions_pk', 'lin_dimensionsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_dimensions_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_status' => [
				'columns' => [
					'lin_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'lin_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['lin_status_pk', 'lin_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_doc' => [
				'columns' => [
					'lin_type_docid' => $this->integer(10)->autoIncrement()->notNull(),
					'lin_type_doc' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['lin_type_doc_pk', 'lin_type_docid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_doc_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_ship' => [
				'columns' => [
					'lin_type_shipid' => $this->integer(10)->autoIncrement()->notNull(),
					'lin_type_ship' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['lin_type_ship_pk', 'lin_type_shipid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_ship_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_links' => [
				'columns' => [
					'linkid' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5),
					'linktype' => $this->stringType(50),
					'linklabel' => $this->stringType(50),
					'linkurl' => $this->stringType(),
					'linkicon' => $this->stringType(100),
					'sequence' => $this->integer(10),
					'handler_path' => $this->stringType(128),
					'handler_class' => $this->stringType(50),
					'handler' => $this->stringType(50),
					'params' => $this->stringType(),
				],
				'primaryKeys' => [
					['links_pk', 'linkid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'login_id' => $this->integer(10)->autoIncrement()->notNull(),
					'user_name' => $this->stringType(32),
					'user_ip' => $this->stringType(50)->notNull(),
					'logout_time' => $this->timestamp()->null(),
					'login_time' => $this->timestamp()->null(),
					'status' => $this->stringType(25),
					'browser' => $this->stringType(25),
				],
				'primaryKeys' => [
					['loginhistory_pk', 'login_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_dimensions' => [
				'columns' => [
					'lout_dimensionsid' => $this->integer(10)->autoIncrement()->notNull(),
					'lout_dimensions' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['lout_dimensions_pk', 'lout_dimensionsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_dimensions_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_status' => [
				'columns' => [
					'lout_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'lout_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['lout_status_pk', 'lout_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_doc' => [
				'columns' => [
					'lout_type_docid' => $this->integer(10)->autoIncrement()->notNull(),
					'lout_type_doc' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['lout_type_doc_pk', 'lout_type_docid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_doc_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_ship' => [
				'columns' => [
					'lout_type_shipid' => $this->integer(10)->autoIncrement()->notNull(),
					'lout_type_ship' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['lout_type_ship_pk', 'lout_type_shipid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_ship_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_mail_accounts' => [
				'columns' => [
					'account_id' => $this->integer(10)->notNull(),
					'user_id' => $this->integer(10)->notNull(),
					'display_name' => $this->stringType(50),
					'mail_id' => $this->stringType(50),
					'account_name' => $this->stringType(50),
					'mail_protocol' => $this->stringType(20),
					'mail_username' => $this->stringType(50)->notNull(),
					'mail_password' => $this->stringType(250)->notNull(),
					'mail_servername' => $this->stringType(50),
					'box_refresh' => $this->integer(10),
					'mails_per_page' => $this->integer(10),
					'ssltype' => $this->stringType(50),
					'sslmeth' => $this->stringType(50),
					'int_mailer' => $this->integer(1)->defaultValue(0),
					'status' => $this->stringType(10),
					'set_default' => $this->integer(2),
					'sent_folder' => $this->stringType(50),
				],
				'primaryKeys' => [
					['mail_accounts_pk', 'account_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_manufacturer' => [
				'columns' => [
					'manufacturerid' => $this->integer(10)->autoIncrement()->notNull(),
					'manufacturer' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10),
				],
				'primaryKeys' => [
					['manufacturer_pk', 'manufacturerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_manufacturer_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modcomments' => [
				'columns' => [
					'modcommentsid' => $this->integer(10)->notNull(),
					'commentcontent' => $this->text(),
					'related_to' => $this->integer(10),
					'parent_comments' => $this->integer(10),
					'customer' => $this->stringType(100),
					'userid' => $this->integer(10),
					'reasontoedit' => $this->stringType(100),
				],
				'primaryKeys' => [
					['modcomments_pk', 'modcommentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modcommentscf' => [
				'columns' => [
					'modcommentsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['modcommentscf_pk', 'modcommentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modentity_num' => [
				'columns' => [
					'id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'prefix' => $this->stringType(50)->notNull()->defaultValue(''),
					'postfix' => $this->stringType(50)->notNull()->defaultValue(''),
					'start_id' => $this->integer(10)->unsigned()->notNull(),
					'cur_id' => $this->integer(10)->unsigned()->notNull(),
				],
				'primaryKeys' => [
					['modentity_num_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_basic' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'crmid' => $this->integer(10),
					'module' => $this->stringType(50),
					'whodid' => $this->integer(10),
					'changedon' => $this->dateTime(),
					'status' => $this->integer(1)->defaultValue(0),
					'last_reviewed_users' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['modtracker_basic_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_detail' => [
				'columns' => [
					'id' => $this->integer(10),
					'fieldname' => $this->stringType(100),
					'prevalue' => $this->text(),
					'postvalue' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_relations' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'targetmodule' => $this->stringType(100)->notNull(),
					'targetid' => $this->integer(10)->notNull(),
					'changedon' => $this->dateTime(),
				],
				'primaryKeys' => [
					['modtracker_relations_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_tabs' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'visible' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'visible' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['modtracker_tabs_pk', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'blockid' => $this->integer(10)->notNull(),
					'linkid' => $this->integer(10),
					'filterid' => $this->stringType(100),
					'title' => $this->stringType(100),
					'data' => $this->text(),
					'size' => $this->stringType(50),
					'limit' => $this->smallInteger(2),
					'isdefault' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'owners' => $this->stringType(100),
					'cache' => $this->smallInteger(1)->defaultValue(0),
					'date' => $this->stringType(20),
				],
				'columns_mysql' => [
					'limit' => $this->tinyInteger(2),
					'isdefault' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'cache' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['module_dashboard_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard_blocks' => [
				'columns' => [
					'id' => $this->integer(100)->unsigned()->autoIncrement()->notNull(),
					'authorized' => $this->stringType(10)->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'dashboard_id' => $this->integer(10),
				],
				'primaryKeys' => [
					['module_dashboard_blocks_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard_widgets' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'linkid' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10),
					'templateid' => $this->integer(10)->notNull(),
					'filterid' => $this->stringType(100),
					'title' => $this->stringType(100),
					'data' => $this->text(),
					'size' => $this->stringType(50),
					'limit' => $this->smallInteger(2),
					'position' => $this->stringType(50),
					'isdefault' => $this->smallInteger(1)->defaultValue(0),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'owners' => $this->stringType(100),
					'module' => $this->integer(10)->defaultValue(0),
					'cache' => $this->smallInteger(1)->defaultValue(0),
					'date' => $this->stringType(20),
					'dashboardid' => $this->integer(10),
				],
				'columns_mysql' => [
					'limit' => $this->tinyInteger(2),
					'isdefault' => $this->tinyInteger(1)->defaultValue(0),
					'active' => $this->tinyInteger(1)->defaultValue(0),
					'cache' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['module_dashboard_widgets_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_no_of_currency_decimals' => [
				'columns' => [
					'no_of_currency_decimalsid' => $this->integer(10)->autoIncrement()->notNull(),
					'no_of_currency_decimals' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['no_of_currency_decimals_pk', 'no_of_currency_decimalsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_no_of_currency_decimals_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notebook_contents' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'notebookid' => $this->integer(10)->notNull(),
					'contents' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notes' => [
				'columns' => [
					'notesid' => $this->integer(10)->notNull()->defaultValue(0),
					'note_no' => $this->stringType(100)->notNull(),
					'title' => $this->stringType(200)->notNull(),
					'filename' => $this->stringType(200),
					'notecontent' => $this->text(),
					'folderid' => $this->stringType(),
					'filetype' => $this->stringType(100),
					'filelocationtype' => $this->stringType(5),
					'filedownloadcount' => $this->integer(10),
					'filestatus' => $this->smallInteger(1),
					'filesize' => $this->integer(10)->notNull()->defaultValue(0),
					'fileversion' => $this->stringType(50),
					'ossdc_status' => $this->stringType(),
				],
				'primaryKeys' => [
					['notes_pk', 'notesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notescf' => [
				'columns' => [
					'notesid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['notescf_pk', 'notesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_status' => [
				'columns' => [
					'notification_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'notification_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['notification_status_pk', 'notification_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_type' => [
				'columns' => [
					'notification_typeid' => $this->integer(10)->autoIncrement()->notNull(),
					'notification_type' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['notification_type_pk', 'notification_typeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_opportunitystage' => [
				'columns' => [
					'potstageid' => $this->integer(10)->autoIncrement()->notNull(),
					'stage' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'probability' => $this->decimal('3,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['opportunitystage_pk', 'potstageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_oproductstatus' => [
				'columns' => [
					'oproductstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'oproductstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['oproductstatus_pk', 'oproductstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_oproductstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_org_share_action2tab' => [
				'columns' => [
					'share_action_id' => $this->integer(10)->notNull(),
					'tabid' => $this->integer(19)->notNull(),
				],
				'primaryKeys' => [
					['org_share_action2tab_pk', ['share_action_id', 'tabid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_org_share_action_mapping' => [
				'columns' => [
					'share_action_id' => $this->integer(10)->notNull(),
					'share_action_name' => $this->stringType(200),
				],
				'primaryKeys' => [
					['org_share_action_mapping_pk', 'share_action_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdc_status' => [
				'columns' => [
					'ossdc_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'ossdc_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['ossdc_status_pk', 'ossdc_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdc_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossemployees' => [
				'columns' => [
					'ossemployeesid' => $this->integer(10)->notNull()->defaultValue(0),
					'ossemployees_no' => $this->stringType(),
					'parentid' => $this->integer(10)->defaultValue(0),
					'employee_status' => $this->stringType(200),
					'name' => $this->stringType(200),
					'last_name' => $this->stringType(200),
					'pesel' => $this->stringType(20),
					'id_card' => $this->stringType(200),
					'employee_education' => $this->stringType(200),
					'birth_date' => $this->date(),
					'business_phone' => $this->stringType(20),
					'private_phone' => $this->stringType(25),
					'business_mail' => $this->stringType(100),
					'private_mail' => $this->stringType(100),
					'street' => $this->stringType(200),
					'code' => $this->stringType(200),
					'city' => $this->stringType(200),
					'state' => $this->stringType(200),
					'country' => $this->stringType(200),
					'ship_street' => $this->stringType(200),
					'ship_code' => $this->stringType(200),
					'ship_city' => $this->stringType(200),
					'ship_state' => $this->stringType(200),
					'ship_country' => $this->stringType(200),
					'dav_status' => $this->smallInteger(1)->defaultValue(1),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'secondary_phone' => $this->stringType(25),
					'position' => $this->stringType(),
					'rbh' => $this->decimal('25,8'),
				],
				'columns_mysql' => [
					'dav_status' => $this->tinyInteger(1)->defaultValue(1),
				],
				'primaryKeys' => [
					['ossemployees_pk', 'ossemployeesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossemployeescf' => [
				'columns' => [
					'ossemployeesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ossemployeescf_pk', 'ossemployeesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osservicesstatus' => [
				'columns' => [
					'osservicesstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'osservicesstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['osservicesstatus_pk', 'osservicesstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osservicesstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmails_logs' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'start_time' => $this->timestamp()->null(),
					'end_time' => $this->timestamp()->null(),
					'action' => $this->stringType(100),
					'status' => $this->smallInteger(3),
					'user' => $this->stringType(100),
					'count' => $this->integer(10),
					'stop_user' => $this->stringType(100),
					'info' => $this->stringType(100),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(3),
				],
				'primaryKeys' => [
					['ossmails_logs_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailscanner_folders_uid' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned(),
					'type' => $this->stringType(50),
					'folder' => $this->stringType(100),
					'uid' => $this->integer(10)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailscanner_log_cron' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'created_time' => $this->timestamp()->null(),
					'laststart' => $this->integer(10)->unsigned(),
					'status' => $this->stringType(50),
				],
				'primaryKeys' => [
					['ossmailscanner_log_cron_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview' => [
				'columns' => [
					'ossmailviewid' => $this->integer(10)->notNull(),
					'ossmailview_no' => $this->stringType(50),
					'from_email' => $this->text(),
					'to_email' => $this->text(),
					'subject' => $this->text(),
					'content' => $this->text(),
					'cc_email' => $this->text(),
					'bcc_email' => $this->text(),
					'id' => $this->integer(10),
					'mbox' => $this->stringType(100),
					'uid' => $this->stringType(150),
					'cid' => $this->char(40),
					'rc_user' => $this->stringType(3),
					'reply_to_email' => $this->text(),
					'ossmailview_sendtype' => $this->stringType(30),
					'attachments_exist' => $this->smallInteger(1)->defaultValue(0),
					'type' => $this->smallInteger(1),
					'from_id' => $this->stringType(50)->notNull(),
					'to_id' => $this->stringType(100)->notNull(),
					'orginal_mail' => $this->text(),
					'verify' => $this->smallInteger(1)->defaultValue(0),
					'rel_mod' => $this->stringType(128),
					'date' => $this->dateTime(),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1),
				],
				'index' => [
					['id', 'id'],
					['verify', 'verify'],
					['message_id', ['uid', 'rc_user']],
					['mbox', 'mbox'],
					['ossmailview_cid_idx', 'cid'],
				],
				'primaryKeys' => [
					['ossmailview_pk', 'ossmailviewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_files' => [
				'columns' => [
					'ossmailviewid' => $this->integer(10)->notNull(),
					'documentsid' => $this->integer(10)->notNull(),
					'attachmentsid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_relation' => [
				'columns' => [
					'ossmailviewid' => $this->integer(10)->notNull(),
					'crmid' => $this->integer(10)->notNull(),
					'date' => $this->dateTime(),
					'deleted' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'deleted' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_sendtype' => [
				'columns' => [
					'ossmailview_sendtypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'ossmailview_sendtype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['ossmailview_sendtype_pk', 'ossmailview_sendtypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_sendtype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailviewcf' => [
				'columns' => [
					'ossmailviewid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ossmailviewcf_pk', 'ossmailviewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossoutsourcedservices' => [
				'columns' => [
					'ossoutsourcedservicesid' => $this->integer(10)->notNull()->defaultValue(0),
					'ossoutsourcedservices_no' => $this->stringType(),
					'productname' => $this->stringType(100)->defaultValue(''),
					'osservicesstatus' => $this->stringType(50),
					'pscategory' => $this->stringType(),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'wherebought' => $this->stringType(100)->defaultValue(''),
					'parent_id' => $this->integer(10),
					'ssalesprocessesid' => $this->integer(10),
				],
				'primaryKeys' => [
					['ossoutsourcedservices_pk', 'ossoutsourcedservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossoutsourcedservicescf' => [
				'columns' => [
					'ossoutsourcedservicesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ossoutsourcedservicescf_pk', 'ossoutsourcedservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osspasswords' => [
				'columns' => [
					'osspasswordsid' => $this->integer(10)->notNull(),
					'osspassword_no' => $this->stringType(100)->notNull(),
					'passwordname' => $this->stringType(100)->notNull(),
					'username' => $this->stringType(100)->notNull(),
					'password' => $this->stringType(200)->notNull(),
					'link_adres' => $this->stringType(),
					'linkto' => $this->integer(10),
				],
				'columns_mysql' => [
					'password' => $this->varbinary(200)->notNull(),
				],
				'primaryKeys' => [
					['osspasswords_pk', 'osspasswordsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osspasswordscf' => [
				'columns' => [
					'osspasswordsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['osspasswordscf_pk', 'osspasswordsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservices' => [
				'columns' => [
					'osssoldservicesid' => $this->integer(10)->notNull()->defaultValue(0),
					'osssoldservices_no' => $this->stringType(),
					'productname' => $this->stringType()->defaultValue(''),
					'ssservicesstatus' => $this->stringType(),
					'pscategory' => $this->stringType()->defaultValue(''),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'invoice' => $this->stringType()->defaultValue(''),
					'parent_id' => $this->integer(10),
					'serviceid' => $this->integer(10),
					'ordertime' => $this->decimal('10,2'),
					'ssalesprocessesid' => $this->integer(10),
					'osssoldservices_renew' => $this->stringType(),
					'renewalinvoice' => $this->integer(10),
				],
				'primaryKeys' => [
					['osssoldservices_pk', 'osssoldservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservices_renew' => [
				'columns' => [
					'osssoldservices_renewid' => $this->integer(10)->autoIncrement()->notNull(),
					'osssoldservices_renew' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['osssoldservices_renew_pk', 'osssoldservices_renewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservicescf' => [
				'columns' => [
					'osssoldservicesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['osssoldservicescf_pk', 'osssoldservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol' => [
				'columns' => [
					'osstimecontrolid' => $this->integer(10)->notNull()->defaultValue(0),
					'name' => $this->stringType(128),
					'osstimecontrol_no' => $this->stringType(),
					'osstimecontrol_status' => $this->stringType(128),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(50),
					'due_date' => $this->date(),
					'time_end' => $this->stringType(50),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'deleted' => $this->integer(1)->defaultValue(0),
					'timecontrol_type' => $this->stringType(),
					'process' => $this->integer(10),
					'link' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'primaryKeys' => [
					['osstimecontrol_pk', 'osstimecontrolid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol_status' => [
				'columns' => [
					'osstimecontrol_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'osstimecontrol_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['osstimecontrol_status_pk', 'osstimecontrol_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrolcf' => [
				'columns' => [
					'osstimecontrolid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['osstimecontrolcf_pk', 'osstimecontrolid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_othereventduration' => [
				'columns' => [
					'othereventdurationid' => $this->integer(10)->autoIncrement()->notNull(),
					'othereventduration' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10),
					'presence' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['othereventduration_pk', 'othereventdurationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_othereventduration_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproducts' => [
				'columns' => [
					'outsourcedproductsid' => $this->integer(10)->notNull()->defaultValue(0),
					'asset_no' => $this->stringType(32),
					'productname' => $this->stringType(),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'oproductstatus' => $this->stringType(),
					'pscategory' => $this->stringType()->defaultValue(''),
					'wherebought' => $this->stringType()->defaultValue(''),
					'prodcount' => $this->stringType()->defaultValue(''),
					'parent_id' => $this->integer(10),
					'ssalesprocessesid' => $this->integer(10),
				],
				'primaryKeys' => [
					['outsourcedproducts_pk', 'outsourcedproductsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproductscf' => [
				'columns' => [
					'outsourcedproductsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['outsourcedproductscf_pk', 'outsourcedproductsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin' => [
				'columns' => [
					'paymentsinid' => $this->integer(10)->notNull()->defaultValue(0),
					'paymentsvalue' => $this->decimal('25,3'),
					'paymentsno' => $this->stringType(32),
					'paymentsname' => $this->stringType(128),
					'paymentstitle' => $this->text(),
					'paymentscurrency' => $this->stringType(32),
					'bank_account' => $this->stringType(128),
					'paymentsin_status' => $this->stringType(128),
					'relatedid' => $this->integer(10),
				],
				'primaryKeys' => [
					['paymentsin_pk', 'paymentsinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin_status' => [
				'columns' => [
					'paymentsin_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'paymentsin_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['paymentsin_status_pk', 'paymentsin_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsincf' => [
				'columns' => [
					'paymentsinid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['paymentsincf_pk', 'paymentsinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout' => [
				'columns' => [
					'paymentsoutid' => $this->integer(10)->notNull()->defaultValue(0),
					'paymentsvalue' => $this->decimal('25,3'),
					'paymentsno' => $this->stringType(32),
					'paymentsname' => $this->stringType(128),
					'paymentstitle' => $this->stringType(128),
					'paymentscurrency' => $this->stringType(32),
					'bank_account' => $this->stringType(128),
					'paymentsout_status' => $this->stringType(128),
					'relatedid' => $this->integer(10),
					'parentid' => $this->integer(10),
				],
				'primaryKeys' => [
					['paymentsout_pk', 'paymentsoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout_status' => [
				'columns' => [
					'paymentsout_statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'paymentsout_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['paymentsout_status_pk', 'paymentsout_statusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout_status_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsoutcf' => [
				'columns' => [
					'paymentsoutid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['paymentsoutcf_pk', 'paymentsoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager' => [
				'columns' => [
					'pbxmanagerid' => $this->integer(10)->autoIncrement()->notNull(),
					'direction' => $this->stringType(10),
					'callstatus' => $this->stringType(20),
					'starttime' => $this->dateTime(),
					'endtime' => $this->dateTime(),
					'totalduration' => $this->integer(10),
					'billduration' => $this->integer(10),
					'recordingurl' => $this->stringType(200),
					'sourceuuid' => $this->stringType(100),
					'gateway' => $this->stringType(20),
					'customer' => $this->integer(10),
					'user' => $this->stringType(100),
					'customernumber' => $this->stringType(100),
					'customertype' => $this->stringType(100),
				],
				'primaryKeys' => [
					['pbxmanager_pk', 'pbxmanagerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager_gateway' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'gateway' => $this->stringType(20),
					'parameters' => $this->text(),
				],
				'primaryKeys' => [
					['pbxmanager_gateway_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager_phonelookup' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'setype' => $this->stringType(30),
					'fnumber' => $this->stringType(100),
					'rnumber' => $this->stringType(100),
					'fieldname' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanagercf' => [
				'columns' => [
					'pbxmanagerid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['pbxmanagercf_pk', 'pbxmanagerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist' => [
				'columns' => [
					'picklistid' => $this->integer(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(200)->notNull(),
				],
				'primaryKeys' => [
					['picklist_pk', 'picklistid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist_dependency' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'sourcefield' => $this->stringType(),
					'targetfield' => $this->stringType(),
					'sourcevalue' => $this->stringType(100),
					'targetvalues' => $this->text(),
					'criteria' => $this->text(),
				],
				'primaryKeys' => [
					['picklist_dependency_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist_dependency_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklistvalues_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_portal' => [
				'columns' => [
					'portalid' => $this->integer(10)->autoIncrement()->notNull(),
					'portalname' => $this->stringType(200)->notNull(),
					'portalurl' => $this->stringType()->notNull(),
					'sequence' => $this->integer(3)->notNull(),
					'setdefault' => $this->integer(3)->notNull()->defaultValue(0),
					'createdtime' => $this->dateTime(),
				],
				'primaryKeys' => [
					['portal_pk', 'portalid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebook' => [
				'columns' => [
					'pricebookid' => $this->integer(10)->notNull()->defaultValue(0),
					'pricebook_no' => $this->stringType(100)->notNull(),
					'bookname' => $this->stringType(100),
					'active' => $this->smallInteger(1),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['pricebook_pk', 'pricebookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebookcf' => [
				'columns' => [
					'pricebookid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['pricebookcf_pk', 'pricebookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebookproductrel' => [
				'columns' => [
					'pricebookid' => $this->integer(10)->notNull(),
					'productid' => $this->integer(10)->notNull(),
					'listprice' => $this->decimal('28,8'),
					'usedcurrency' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['pricebookproductrel_pk', ['pricebookid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_priority' => [
				'columns' => [
					'priorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'priority' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(10)->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['priority_pk', 'priorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_productcf' => [
				'columns' => [
					'productid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['productcf_pk', 'productid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_productcurrencyrel' => [
				'columns' => [
					'productid' => $this->integer(10)->notNull(),
					'currencyid' => $this->integer(10)->notNull(),
					'converted_price' => $this->decimal('28,8'),
					'actual_price' => $this->decimal('28,8'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_products' => [
				'columns' => [
					'productid' => $this->integer(10)->notNull(),
					'product_no' => $this->stringType(100)->notNull(),
					'productname' => $this->stringType(100),
					'productcode' => $this->stringType(40),
					'pscategory' => $this->stringType(200),
					'manufacturer' => $this->stringType(200),
					'qty_per_unit' => $this->decimal('11,2')->defaultValue(0),
					'unit_price' => $this->decimal('25,8'),
					'weight' => $this->decimal('11,3'),
					'pack_size' => $this->integer(10),
					'sales_start_date' => $this->date(),
					'sales_end_date' => $this->date(),
					'start_date' => $this->date(),
					'expiry_date' => $this->date(),
					'cost_factor' => $this->integer(10),
					'commissionrate' => $this->decimal('7,3'),
					'commissionmethod' => $this->stringType(50),
					'discontinued' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'usageunit' => $this->stringType(200),
					'reorderlevel' => $this->integer(10),
					'website' => $this->stringType(100),
					'mfr_part_no' => $this->stringType(200),
					'vendor_part_no' => $this->stringType(200),
					'serialno' => $this->stringType(200),
					'qtyinstock' => $this->decimal('25,3'),
					'productsheet' => $this->stringType(200),
					'qtyindemand' => $this->integer(10),
					'glacct' => $this->stringType(200),
					'vendor_id' => $this->integer(10),
					'imagename' => $this->text(),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
					'taxes' => $this->stringType(50),
					'ean' => $this->stringType(30),
					'subunit' => $this->stringType()->defaultValue(''),
					'renewable' => $this->smallInteger(1)->defaultValue(0),
					'category_multipicklist' => $this->text(),
				],
				'columns_mysql' => [
					'discontinued' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'renewable' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['products_pk', 'productid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2field' => [
				'columns' => [
					'profileid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5),
					'fieldid' => $this->integer(10)->notNull(),
					'visible' => $this->integer(10),
					'readonly' => $this->integer(10),
				],
				'primaryKeys' => [
					['profile2field_pk', ['profileid', 'fieldid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2globalpermissions' => [
				'columns' => [
					'profileid' => $this->integer(10)->notNull(),
					'globalactionid' => $this->integer(10)->notNull(),
					'globalactionpermission' => $this->integer(10),
				],
				'primaryKeys' => [
					['profile2globalpermissions_pk', ['profileid', 'globalactionid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2standardpermissions' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'operation' => $this->smallInteger(5)->unsigned()->notNull(),
					'permissions' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'permissions' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['profile2standardpermissions_pk', ['profileid', 'tabid', 'operation']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2tab' => [
				'columns' => [
					'profileid' => $this->integer(10),
					'tabid' => $this->smallInteger(5),
					'permissions' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2utility' => [
				'columns' => [
					'profileid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'activityid' => $this->integer(10)->notNull(),
					'permission' => $this->integer(1),
				],
				'primaryKeys' => [
					['profile2utility_pk', ['profileid', 'tabid', 'activityid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_progress' => [
				'columns' => [
					'progressid' => $this->integer(10)->autoIncrement()->notNull(),
					'progress' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['progress_pk', 'progressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_progress_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_project' => [
				'columns' => [
					'projectid' => $this->integer(10)->notNull(),
					'projectname' => $this->stringType(),
					'project_no' => $this->stringType(100),
					'startdate' => $this->date(),
					'targetenddate' => $this->date(),
					'actualenddate' => $this->date(),
					'targetbudget' => $this->stringType(),
					'projecturl' => $this->stringType(),
					'projectstatus' => $this->stringType(100),
					'projectpriority' => $this->stringType(100),
					'projecttype' => $this->stringType(100),
					'progress' => $this->stringType(100),
					'linktoaccountscontacts' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'servicecontractsid' => $this->integer(10),
					'ssalesprocessesid' => $this->integer(10),
					'parentid' => $this->integer(10),
				],
				'index' => [
					['servicecontractsid', 'servicecontractsid'],
					['linktoaccountscontacts', 'linktoaccountscontacts'],
					['projectname', 'projectname'],
					['ssalesprocessesid', 'ssalesprocessesid'],
					['project_parentid_idx', 'parentid'],
					['project_no', 'project_no'],
				],
				'primaryKeys' => [
					['project_pk', 'projectid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectcf' => [
				'columns' => [
					'projectid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['projectcf_pk', 'projectid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone' => [
				'columns' => [
					'projectmilestoneid' => $this->integer(10)->notNull(),
					'projectmilestonename' => $this->stringType(),
					'projectmilestone_no' => $this->stringType(100),
					'projectmilestonedate' => $this->stringType(),
					'projectid' => $this->integer(10),
					'projectmilestonetype' => $this->stringType(100),
					'projectmilestone_priority' => $this->stringType(),
					'projectmilestone_progress' => $this->stringType(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['projectmilestone_pk', 'projectmilestoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone_priority' => [
				'columns' => [
					'projectmilestone_priorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'projectmilestone_priority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projectmilestone_priority_pk', 'projectmilestone_priorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone_priority_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonecf' => [
				'columns' => [
					'projectmilestoneid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['projectmilestonecf_pk', 'projectmilestoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonetype' => [
				'columns' => [
					'projectmilestonetypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'projectmilestonetype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projectmilestonetype_pk', 'projectmilestonetypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonetype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectpriority' => [
				'columns' => [
					'projectpriorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'projectpriority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projectpriority_pk', 'projectpriorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectpriority_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectstatus' => [
				'columns' => [
					'projectstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'projectstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'primaryKeys' => [
					['projectstatus_pk', 'projectstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttask' => [
				'columns' => [
					'projecttaskid' => $this->integer(10)->notNull(),
					'projecttaskname' => $this->stringType(),
					'projecttask_no' => $this->stringType(100),
					'projecttasktype' => $this->stringType(100),
					'projecttaskpriority' => $this->stringType(100),
					'projecttaskprogress' => $this->stringType(100),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
					'projectid' => $this->integer(10),
					'projecttasknumber' => $this->integer(10),
					'projecttaskstatus' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'parentid' => $this->integer(10),
					'projectmilestoneid' => $this->integer(10),
					'targetenddate' => $this->date(),
					'estimated_work_time' => $this->decimal('8,2'),
				],
				'primaryKeys' => [
					['projecttask_pk', 'projecttaskid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskcf' => [
				'columns' => [
					'projecttaskid' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttaskcf_pk', 'projecttaskid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskpriority' => [
				'columns' => [
					'projecttaskpriorityid' => $this->integer(10)->autoIncrement()->notNull(),
					'projecttaskpriority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttaskpriority_pk', 'projecttaskpriorityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskpriority_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskprogress' => [
				'columns' => [
					'projecttaskprogressid' => $this->integer(10)->autoIncrement()->notNull(),
					'projecttaskprogress' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttaskprogress_pk', 'projecttaskprogressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskprogress_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskstatus' => [
				'columns' => [
					'projecttaskstatusid' => $this->integer(10)->autoIncrement()->notNull(),
					'projecttaskstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttaskstatus_pk', 'projecttaskstatusid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskstatus_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttasktype' => [
				'columns' => [
					'projecttasktypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'projecttasktype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttasktype_pk', 'projecttasktypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttasktype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttype' => [
				'columns' => [
					'projecttypeid' => $this->integer(10)->autoIncrement()->notNull(),
					'projecttype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
				],
				'primaryKeys' => [
					['projecttype_pk', 'projecttypeid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttype_seq' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_publicholiday' => [
				'columns' => [
					'publicholidayid' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'holidaydate' => $this->date()->notNull(),
					'holidayname' => $this->stringType()->notNull(),
					'holidaytype' => $this->stringType(25),
				],
				'primaryKeys' => [
					['publicholiday_pk', 'publicholidayid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_2_vtiger_group2grouprel', 'vtiger_group2grouprel', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'CASCADE'],
			['vtiger_group2modules_ibfk_1', 'vtiger_group2modules', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['vtiger_group2modules_ibfk_2', 'vtiger_group2modules', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_group2role', 'vtiger_group2role', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_group2rs', 'vtiger_group2rs', 'roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_holidaysentitlement', 'vtiger_holidaysentitlement', 'holidaysentitlementid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_holidaysentitlementcf', 'vtiger_holidaysentitlementcf', 'holidaysentitlementid', 'vtiger_holidaysentitlement', 'holidaysentitlementid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ideas', 'vtiger_ideas', 'ideasid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ideascf', 'vtiger_ideascf', 'ideasid', 'vtiger_ideas', 'ideasid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadaddress', 'vtiger_leadaddress', 'leadaddressid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leaddetails', 'vtiger_leaddetails', 'leadid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadscf', 'vtiger_leadscf', 'leadid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadsubdetails', 'vtiger_leadsubdetails', 'leadsubscriptionid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersin', 'vtiger_lettersin', 'lettersinid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersincf', 'vtiger_lettersincf', 'lettersinid', 'vtiger_lettersin', 'lettersinid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersout', 'vtiger_lettersout', 'lettersoutid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersoutcf', 'vtiger_lettersoutcf', 'lettersoutid', 'vtiger_lettersout', 'lettersoutid', 'CASCADE', 'RESTRICT'],
			['vtiger_modcomments_ibfk_1', 'vtiger_modcomments', 'related_to', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_modcommentscf_ibfk_1', 'vtiger_modcommentscf', 'modcommentsid', 'vtiger_modcomments', 'modcommentsid', 'CASCADE', 'RESTRICT'],
			['vtiger_module_dashboard_widgets_ibfk_1', 'vtiger_module_dashboard_widgets', 'templateid', 'vtiger_module_dashboard', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_notes', 'vtiger_notes', 'notesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_notescf_ibfk_1', 'vtiger_notescf', 'notesid', 'vtiger_notes', 'notesid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_org_share_action2tab', 'vtiger_org_share_action2tab', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossemployees', 'vtiger_ossemployees', 'ossemployeesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossemployeescf', 'vtiger_ossemployeescf', 'ossemployeesid', 'vtiger_ossemployees', 'ossemployeesid', 'CASCADE', 'RESTRICT'],
			['vtiger_ossmailscanner_folders_uid_ibfk_1', 'vtiger_ossmailscanner_folders_uid', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailview', 'vtiger_ossmailview', 'ossmailviewid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailview_files', 'vtiger_ossmailview_files', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['vtiger_ossmailview_relation_ibfk_1', 'vtiger_ossmailview_relation', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailviewcf', 'vtiger_ossmailviewcf', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossoutsourcedservices', 'vtiger_ossoutsourcedservices', 'ossoutsourcedservicesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossoutsourcedservicescf', 'vtiger_ossoutsourcedservicescf', 'ossoutsourcedservicesid', 'vtiger_ossoutsourcedservices', 'ossoutsourcedservicesid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osspasswords', 'vtiger_osspasswords', 'osspasswordsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osspasswordscf', 'vtiger_osspasswordscf', 'osspasswordsid', 'vtiger_osspasswords', 'osspasswordsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osssoldservices', 'vtiger_osssoldservices', 'osssoldservicesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osssoldservicescf', 'vtiger_osssoldservicescf', 'osssoldservicesid', 'vtiger_osssoldservices', 'osssoldservicesid', 'CASCADE', 'RESTRICT'],
			['vtiger_osstimecontrol', 'vtiger_osstimecontrol', 'osstimecontrolid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_osstimecontrolcf', 'vtiger_osstimecontrolcf', 'osstimecontrolid', 'vtiger_osstimecontrol', 'osstimecontrolid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_outsourcedproducts', 'vtiger_outsourcedproducts', 'outsourcedproductsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_outsourcedproductscf', 'vtiger_outsourcedproductscf', 'outsourcedproductsid', 'vtiger_outsourcedproducts', 'outsourcedproductsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsin', 'vtiger_paymentsin', 'paymentsinid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsincf', 'vtiger_paymentsincf', 'paymentsinid', 'vtiger_paymentsin', 'paymentsinid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsout', 'vtiger_paymentsout', 'paymentsoutid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsoutcf', 'vtiger_paymentsoutcf', 'paymentsoutid', 'vtiger_paymentsout', 'paymentsoutid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanager_ibfk_1', 'vtiger_pbxmanager', 'pbxmanagerid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanager_phonelookup_ibfk_1', 'vtiger_pbxmanager_phonelookup', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanagercf_ibfk_1', 'vtiger_pbxmanagercf', 'pbxmanagerid', 'vtiger_pbxmanager', 'pbxmanagerid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebook', 'vtiger_pricebook', 'pricebookid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebookcf', 'vtiger_pricebookcf', 'pricebookid', 'vtiger_pricebook', 'pricebookid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebookproductrel', 'vtiger_pricebookproductrel', 'pricebookid', 'vtiger_pricebook', 'pricebookid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_productcf', 'vtiger_productcf', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_products', 'vtiger_products', 'productid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2field_ibfk_1', 'vtiger_profile2field', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_profile2globalpermissions', 'vtiger_profile2globalpermissions', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2tab_ibfk_1', 'vtiger_profile2tab', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2utility_ibfk_1', 'vtiger_profile2utility', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_project_ibfk_1', 'vtiger_project', 'projectid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectcf_ibfk_1', 'vtiger_projectcf', 'projectid', 'vtiger_project', 'projectid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectmilestone_ibfk_1', 'vtiger_projectmilestone', 'projectmilestoneid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectmilestonecf_ibfk_1', 'vtiger_projectmilestonecf', 'projectmilestoneid', 'vtiger_projectmilestone', 'projectmilestoneid', 'CASCADE', 'RESTRICT'],
			['vtiger_projecttask_ibfk_1', 'vtiger_projecttask', 'projecttaskid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projecttaskcf_ibfk_1', 'vtiger_projecttaskcf', 'projecttaskid', 'vtiger_projecttask', 'projecttaskid', 'CASCADE', 'RESTRICT'],
		];
	}
}
