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
			'b_#__social_media_twitter' => [
				'columns' => [
					'id' => $this->bigPrimaryKey(),
					'twitter_login' => $this->stringType(20)->notNull(),
					'id_twitter' => $this->stringType(32),
					'message' => $this->text(),
					'created' => $this->dateTime(),
				],
				'index' => [
					['twitter_login', 'twitter_login'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'smtp_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'date' => $this->dateTime()->notNull(),
					'owner' => $this->integer(10)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'from' => $this->text(),
					'subject' => $this->text(),
					'to' => $this->text(),
					'content' => $this->text(),
					'cc' => $this->text(),
					'bcc' => $this->text(),
					'attachments' => $this->text(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'priority' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['smtp_id', 'smtp_id'],
				],
				'primaryKeys' => [
					['mail_queue_pk', 'id']
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
					'parent_id' => $this->integer()->unsigned()->defaultValue(0),
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
			'u_#__social_media_config' => [
				'columns' => [
					'id' => $this->bigPrimaryKey(),
					'name' => $this->stringType(100)->notNull(),
					'value' => $this->text(),
					'type' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['name_type_unique', ['name', 'type'], true],
					['type', 'type'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__social_media_twitter' => [
				'columns' => [
					'id' => $this->bigPrimaryKey(),
					'twitter_login' => $this->stringType(20)->notNull(),
					'id_twitter' => $this->stringType(32),
					'message' => $this->text(),
					'created' => $this->dateTime(),
				],
				'index' => [
					['twitter_login', 'twitter_login'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
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
					'maximumlength' => $this->stringType(30),
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
					'header_field' => $this->stringType(255),
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
					['field_sequence_idx', 'sequence'],
					['field_uitype_idx', 'uitype'],
				],
				'primaryKeys' => [
					['field_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'login_id' => $this->integer(10)->autoIncrement()->notNull(),
					'user_name' => $this->stringType(32),
					'user_ip' => $this->stringType(100),
					'logout_time' => $this->timestamp()->null(),
					'login_time' => $this->timestamp()->null(),
					'status' => $this->stringType(25),
					'browser' => $this->stringType(25),
				],
				'index' => [
					['user_name', 'user_name'],
					['user_ip', ['user_ip', 'login_time', 'status']],
				],
				'primaryKeys' => [
					['loginhistory_pk', 'login_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modentity_num' => [
				'columns' => [
					'id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'prefix' => $this->stringType(50)->notNull()->defaultValue(''),
					'leading_zeros' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'postfix' => $this->stringType(50)->notNull()->defaultValue(''),
					'start_id' => $this->integer(10)->unsigned()->notNull(),
					'cur_id' => $this->integer(10)->unsigned()->notNull(),
					'reset_sequence' => $this->char(),
					'cur_sequence' => $this->stringType(10)->defaultValue(''),
				],
				'columns_mysql' => [
					'leading_zeros' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['semodule', 'cur_id'],
					['prefix', ['prefix', 'postfix', 'cur_id']],
					['tabid', 'tabid'],
					['tabid_2', ['tabid', 'cur_id']],
				],
				'primaryKeys' => [
					['modentity_num_pk', 'id']
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
					'from_id' => $this->text()->notNull(),
					'to_id' => $this->text()->notNull(),
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
			'l_#__mail' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'date' => $this->dateTime()->notNull(),
					'error_code' => $this->integer(10)->unsigned()->notNull(),
					'smtp_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'owner' => $this->integer(10)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'from' => $this->text(),
					'subject' => $this->text(),
					'to' => $this->text(),
					'content' => $this->text(),
					'cc' => $this->text(),
					'bcc' => $this->text(),
					'attachments' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['smtp_id', 'smtp_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'smtp_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'date' => $this->dateTime()->notNull(),
					'owner' => $this->integer(10)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'from' => $this->text(),
					'subject' => $this->text(),
					'to' => $this->text(),
					'content' => $this->text(),
					'cc' => $this->text(),
					'bcc' => $this->text(),
					'attachments' => $this->text(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'priority' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['smtp_id', 'smtp_id'],
					['status', 'status'],
				],
				'primaryKeys' => [
					['mail_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_1_a_#__record_converter', 'a_#__record_converter', 'source_module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}
}
