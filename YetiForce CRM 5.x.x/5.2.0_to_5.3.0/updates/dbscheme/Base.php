<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_activitytype' => [
				'columns' => [
					'icon' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'columns' => [
					'handler_class' => $this->stringType(100)->after('name'),
				],
				'index' => [
					['handler_class', 'handler_class', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks' => [
				'columns' => [
					'icon' => $this->stringType(30),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists' => [
				'columns' => [
					'field_name' => $this->stringType(50),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['related_tabid', 'related_tabid'],
					['tabid_3', ['tabid', 'related_tabid', 'label']],
					['tabid_4', ['tabid', 'related_tabid', 'presence']],
				],
				'primaryKeys' => [
					['relatedlists_pk', 'relation_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview' => [
				'index' => [
					['ossmailview_date_idx', 'date'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_private' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'private_room_id' => $this->integer(10)->unsigned()->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_user' => [
				'columns' => [
					'roomid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'last_message' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_user' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'reluserid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_private' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'privateid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'roomid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_private' => [
				'columns' => [
					'private_room_id' => $this->primaryKey(10)->unsigned(),
					'name' => $this->stringType()->notNull(),
					'creatorid' => $this->integer(10)->notNull(),
					'created' => $this->dateTime(),
					'archived' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'archived' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'type' => $this->stringType(50),
					'sequence' => $this->smallInteger(4),
					'active' => $this->smallInteger(1)->defaultValue(1),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(4),
					'active' => $this->tinyInteger(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__emailtemplates' => [
				'columns' => [
					'content' => $this->mediumText(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entity_stats' => [
				'columns' => [
					'crmactivity' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol' => [
				'columns' => [
					'time_start' => $this->time(),
					'time_end' => $this->time(),
					'deleted' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone' => [
				'columns' => [
					'projectmilestonedate' => $this->date(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations' => [
				'columns' => [
					'time_start' => $this->time(),
					'time_end' => $this->time(),
					'deleted' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'reports_to_id' => $this->integer(10)->unsigned(),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
					'no_of_currency_decimals' => $this->stringType(200),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflows' => [
				'columns' => [
					'defaultworkflow' => $this->tinyInteger(1),
					'filtersavedinnew' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency' => [
				'columns' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_info' => [
				'columns' => [
					'deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'columns' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_share' => [
				'columns' => [
					'permission' => $this->tinyInteger(5)->unsigned()->notNull(),
					'editstatus' => $this->tinyInteger(5)->unsigned()->notNull(),
				],
				'index' => [
					['fk_1_def_org_share_tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fieldmodulerel' => [
				'columns' => [
					'fieldid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_maps' => [
				'columns' => [
					'has_header' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'userid' => $this->integer(10),
					'agent' => $this->stringType(500),
				],
				'index' => [
					['userid', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modentity_num' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_org_share_action_mapping' => [
				'columns' => [
					'share_action_id' => $this->tinyInteger(5)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2globalpermissions' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2tab' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'permissions' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['fk_1_profile2tab_tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2standardpermissions' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
				],
				'index' => [
					['fk_1_profile2field_tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2field' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
					'visible' => $this->tinyInteger(1)->unsigned()->notNull(),
					'readonly' => $this->tinyInteger(1)->unsigned()->notNull(),
				],
				'index' => [
					['fk_1_profile2field_fieldid', 'fieldid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2utility' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
					'activityid' => $this->smallInteger(5)->unsigned()->notNull(),
					'permission' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)
				],
				'index' => [
					['fk_1_profile2utility_activityid', 'activityid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_fields' => [
				'columns' => [
					'fieldid' => $this->integer(10)->notNull(),
				],
				'index' => [
					['fk_1_relatedlists_fields_fieldid', 'fieldid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role2profile' => [
				'columns' => [
					'profileid' => $this->smallInteger(5)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rss' => [
				'columns' => [
					'starred' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_trees_templates' => [
				'columns' => [
					'access' => $this->tinyInteger(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users_last_import' => [
				'columns' => [
					'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_currencyupdate_banks' => [
				'columns' => [
					'active' => $this->tinyInteger(1)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__relations_members_entity' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
					'status_rel' => $this->stringType(225),
					'comment_rel' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_status_rel' => [
				'columns' => [
					'status_relid' => $this->integer(),
					'status_rel' => $this->stringType(),
					'presence' => $this->smallInteger(1),
					'sortorderid' => $this->smallInteger(),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__occurrences' => [
				'columns' => [
					'occurrences_status' => $this->stringType(),
					'occurrences_type' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'lat' => $this->decimal('10,7')->notNull(),
					'lon' => $this->decimal('10,7')->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__relations_members_entity' => [
				'index' => [
					['u_yf_relations_members_entity_crmid_idx', 'crmid'],
					['u_yf_relations_members_entity_relcrmid_idx', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_vtiger_campaigncampaignid', 'vtiger_campaign', 'campaignid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['fk_1_def_org_share_tabid', 'vtiger_def_org_share', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['vtiger_fieldmodulerel_ibfk_1', 'vtiger_fieldmodulerel', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['fk_1_modentity_num_tabid', 'vtiger_modentity_num', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['fk_1_profile2field_fieldid', 'vtiger_profile2field', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['fk_2_profile2field_tabid', 'vtiger_profile2field', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['fk_1_profile2stand_profileid', 'vtiger_profile2standardpermissions', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', null],
			['fk_1_profile2stand_tabid', 'vtiger_profile2standardpermissions', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['fk_1_profile2tab_tabid', 'vtiger_profile2tab', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['fk_1_profile2utility_activityid', 'vtiger_profile2utility', 'activityid', 'vtiger_actionmapping', 'actionid', 'CASCADE', null],
			['fk_1_profile2utility_tabid', 'vtiger_profile2utility', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['fk_1_relatedlists_fields_fieldid', 'vtiger_relatedlists_fields', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['u_#__openstreetmap_ibfk_1', 'u_#__openstreetmap', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['u_#__openstreetmap_record_updater_ibfk_1', 'u_#__openstreetmap_record_updater', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['u_#__relations_members_entity_crmid_fk', 'u_#__relations_members_entity', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['u_#__relations_members_entity_relcrmid_fk', 'u_#__relations_members_entity', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['vtiger_crmentityrel_crmid_fk', 'vtiger_crmentityrel', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['vtiger_crmentityrel_relcrmid_fk', 'vtiger_crmentityrel', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
		];
	}

	public function data()
	{
		$this->data = [
			'u_yf_chat_rooms' => [
				'columns' => ['id', 'type', 'sequence', 'active'],
				'values' => [
					[1, 'crm', null, 1],
					[2, 'global', null, 1],
					[3, 'group', null, 1],
					[4, 'private', null, 1],
					[5, 'user', null, 1],
				]
			]
		];
	}
}
