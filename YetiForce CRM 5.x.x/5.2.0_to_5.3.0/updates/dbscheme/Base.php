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
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'reports_to_id' => $this->integer(10)->unsigned(),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
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
