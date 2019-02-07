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
			'a_#__pdf' => [
				'columns' => [
					'pdfid' => $this->primaryKey(10)->unsigned(),
					'module_name' => $this->stringType(25)->notNull(),
					'header_content' => $this->text(),
					'body_content' => $this->text(),
					'footer_content' => $this->text(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'primary_name' => $this->stringType()->notNull(),
					'secondary_name' => $this->stringType()->notNull(),
					'meta_author' => $this->stringType()->notNull(),
					'meta_creator' => $this->stringType()->notNull(),
					'meta_keywords' => $this->stringType()->notNull(),
					'metatags_status' => $this->smallInteger(1)->notNull(),
					'meta_subject' => $this->stringType()->notNull(),
					'meta_title' => $this->stringType()->notNull(),
					'page_format' => $this->stringType()->notNull(),
					'margin_chkbox' => $this->smallInteger(1),
					'margin_top' => $this->smallInteger(2)->unsigned(),
					'margin_bottom' => $this->smallInteger(2)->unsigned(),
					'margin_left' => $this->smallInteger(2)->unsigned(),
					'margin_right' => $this->smallInteger(2)->unsigned(),
					'header_height' => $this->smallInteger(2)->unsigned(),
					'footer_height' => $this->smallInteger(2)->unsigned(),
					'page_orientation' => $this->stringType(30)->notNull(),
					'language' => $this->stringType(7)->notNull(),
					'filename' => $this->stringType()->notNull(),
					'visibility' => $this->stringType(200)->notNull(),
					'default' => $this->smallInteger(1),
					'conditions' => $this->text(),
					'watermark_type' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'watermark_text' => $this->stringType()->notNull(),
					'watermark_angle' => $this->smallInteger(3)->unsigned()->notNull(),
					'watermark_image' => $this->stringType()->notNull(),
					'template_members' => $this->text()->notNull(),
					'one_pdf' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'metatags_status' => $this->tinyInteger(1)->notNull(),
					'margin_chkbox' => $this->tinyInteger(1),
					'default' => $this->tinyInteger(1),
					'watermark_type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'one_pdf' => $this->tinyInteger(1),
				],
				'index' => [
					['module_name', ['module_name', 'status']],
					['module_name_2', 'module_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'b_#__social_media_twitter' => [
				'columns' => [
					'id' => $this->bigPrimaryKey(),
					'twitter_login' => $this->stringType(15)->notNull(),
					'id_twitter' => $this->stringType(32),
					'message' => $this->text(),
					'created' => $this->dateTime(),
					'twitter_name' => $this->stringType(50),
					'reply' => $this->integer(11),
					'retweet' => $this->integer(11),
					'favorite' => $this->integer(11)
				],
				'index' => [
					['twitter_login', 'twitter_login'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_calendarobjects' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
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
				'index' => [
					['calendarid', ['calendarid', 'uri'], true],
					['uri', 'uri'],
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_cards' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
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
				'index' => [
					['addressbookid', 'addressbookid'],
					['uri', 'uri'],
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'l_#__settings_tracker_detail' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'prev_value' => $this->text()->notNull()->defaultValue(''),
					'post_value' => $this->text()->notNull()->defaultValue(''),
					'field' => $this->stringType()->notNull(),
				],
				'index' => [
					['id', 'id'],
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
					'error' => $this->text()
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
			'vtiger_blocks' => [
				'columns' => [
					'blockid' => $this->primaryKey(10),
					'tabid' => $this->smallInteger(5)->notNull(),
					'blocklabel' => $this->stringType(100)->notNull(),
					'sequence' => $this->smallInteger(3)->unsigned(),
					'show_title' => $this->smallInteger(1)->unsigned(),
					'visible' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'create_view' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'edit_view' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'detail_view' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'display_status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'iscustom' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned(),
					'show_title' => $this->tinyInteger(1)->unsigned(),
					'visible' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'create_view' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'edit_view' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'detail_view' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'display_status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'iscustom' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['block_tabid_idx', 'tabid'],
					['block_sequence_idx', 'sequence'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_global' => [
				'columns' => [
					'global_room_id' => $this->primaryKeyUnsigned(10),
					'name' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['chat_global_pk', 'global_room_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_crm' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'crmid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['room_crmid', 'crmid'],
				],
				'primaryKeys' => [
					['chat_messages_crm_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_global' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'globalid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['globalid', 'globalid'],
				],
				'primaryKeys' => [
					['chat_messages_global_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_group' => [
				'columns' => [
					'id' => $this->bigPrimaryKeyUnsigned(10),
					'groupid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['room_groupid', 'groupid'],
				],
				'primaryKeys' => [
					['chat_messages_group_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_crm' => [
				'columns' => [
					'roomid' => $this->primaryKeyUnsigned(10),
					'userid' => $this->integer(10)->notNull(),
					'crmid' => $this->integer(10),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['u_yf_chat_rooms_crm_userid_idx', 'userid'],
					['u_yf_chat_rooms_crm_crmid_idx', 'crmid'],
					['u_yf_chat_rooms_crm_last_message_idx', 'last_message'],
				],
				'primaryKeys' => [
					['chat_rooms_crm_pk', 'roomid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_global' => [
				'columns' => [
					'roomid' => $this->primaryKeyUnsigned(10),
					'userid' => $this->integer(10)->notNull(),
					'global_room_id' => $this->integer(10)->unsigned()->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['global_room_id', 'global_room_id'],
					['userid', 'userid'],
					['last_message', 'last_message'],
				],
				'primaryKeys' => [
					['chat_rooms_global_pk', 'roomid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_group' => [
				'columns' => [
					'roomid' => $this->primaryKeyUnsigned(10),
					'userid' => $this->integer(10)->notNull(),
					'groupid' => $this->integer(10)->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['u_yf_chat_rooms_group_groupid_idx', 'groupid'],
					['userid', 'userid'],
				],
				'primaryKeys' => [
					['chat_rooms_group_pk', 'roomid']
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
			'u_#__modentity_sequences' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'value' => $this->stringType(),
					'cur_id' => $this->integer(10)->unsigned()->defaultValue(0)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__picklist_close_state' => [
				'columns' => [
					'valueid' => $this->integer()->notNull(),
					'fieldid' => $this->integer()->notNull(),
					'value' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['valueid_pk', 'valueid']
				],
				'index' => [
					['fieldid', 'fieldid'],
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
					'twitter_login' => $this->stringType(15)->notNull(),
					'id_twitter' => $this->stringType(32),
					'message' => $this->text(),
					'created' => $this->dateTime(),
					'twitter_name' => $this->stringType(50),
					'reply' => $this->integer(11),
					'retweet' => $this->integer(11),
					'favorite' => $this->integer(11),
				],
				'index' => [
					['twitter_login', 'twitter_login'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvcolumnlist' => [
				'columns' => [
					'cvid' => $this->integer(10)->notNull(),
					'columnindex' => $this->smallInteger(3)->unsigned()->notNull(),
					'field_name' => $this->stringType(50),
					'module_name' => $this->stringType(25),
					'source_field_name' => $this->stringType(50),
				],
				'index' => [
					['cvcolumnlist_columnindex_idx', 'columnindex'],
					['cvcolumnlist_cvid_idx', 'cvid'],
				],
				'primaryKeys' => [
					['cvcolumnlist_pk', ['cvid', 'columnindex']]
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
					'readonly' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'defaultvalue' => $this->text(),
					'maximumlength' => $this->stringType(30),
					'sequence' => $this->smallInteger(5)->unsigned()->notNull(),
					'block' => $this->integer(10),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull(),
					'typeofdata' => $this->stringType(100),
					'quickcreate' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'quickcreatesequence' => $this->smallInteger(3)->unsigned(),
					'info_type' => $this->char(3),
					'masseditable' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'helpinfo' => $this->stringType(30)->defaultValue(''),
					'summaryfield' => $this->smallInteger(1)->notNull()->unsigned()->defaultValue(0),
					'fieldparams' => $this->stringType()->defaultValue(''),
					'header_field' => $this->stringType(255),
					'maxlengthtext' => $this->smallInteger(3)->unsigned()->defaultValue(0),
					'maxwidthcolumn' => $this->smallInteger(3)->unsigned()->defaultValue(0),
					'visible' => $this->smallInteger(1)->notNull()->unsigned()->defaultValue(0),
				],
				'columns_mysql' => [
					'quickcreatesequence' => $this->tinyInteger(3)->unsigned(),
					'generatedtype' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0),
					'masseditable' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'readonly' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull(),
					'quickcreate' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'summaryfield' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0),
					'visible' => $this->tinyInteger(1)->notNull()->unsigned()->defaultValue(0),
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
			'vtiger_language' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(6),
					'name' => $this->stringType(50)->notNull(),
					'prefix' => $this->stringType(10)->notNull(),
					'lastupdated' => $this->dateTime(),
					'sequence' => $this->smallInteger(6)->unsigned(),
					'isdefault' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'active' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1)
				],
				'columns_mysql' => [
					'id' => $this->smallInteger()->unsigned()->autoIncrement()->notNull(),
					'isdefault' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1)
				],
				'index' => [
					['prefix', 'prefix'],
					['isdefault', 'isdefault'],
				],
				'primaryKeys' => [
					['language_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_links' => [
				'columns' => [
					'linkid' => $this->primaryKey(10),
					'tabid' => $this->smallInteger(5),
					'linktype' => $this->stringType(50),
					'linklabel' => $this->stringType(50),
					'linkurl' => $this->stringType(),
					'linkicon' => $this->stringType(100),
					'sequence' => $this->smallInteger(3)->unsigned(),
					'handler_path' => $this->stringType(128),
					'handler_class' => $this->stringType(50),
					'handler' => $this->stringType(50),
					'params' => $this->stringType(),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned(),
				],
				'index' => [
					['link_tabidtype_idx', ['tabid', 'linktype']],
					['linklabel', 'linklabel'],
					['linkid', ['linkid', 'tabid', 'linktype', 'linklabel']],
					['linktype', 'linktype'],
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
			'vtiger_profile' => [
				'columns' => [
					'profileid' => $this->primaryKey(10),
					'profilename' => $this->stringType(50)->notNull(),
					'description' => $this->text(),
					'directly_related_to_role' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'directly_related_to_role' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2field' => [
				'columns' => [
					'profileid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5),
					'fieldid' => $this->integer(10)->notNull(),
					'visible' => $this->smallInteger(1),
					'readonly' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'visible' => $this->tinyInteger(1),
					'readonly' => $this->tinyInteger(1),
				],
				'index' => [
					['profile2field_profileid_tabid_fieldname_idx', ['profileid', 'tabid']],
					['profile2field_tabid_profileid_idx', ['tabid', 'profileid']],
					['profile2field_visible_profileid_idx', ['visible', 'profileid']],
					['profile2field_readonly_idx', 'readonly'],
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
					'globalactionid' => $this->smallInteger(5)->notNull(),
					'globalactionpermission' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'globalactionpermission' => $this->tinyInteger(1),
				],
				'index' => [
					['idx_profile2globalpermissions', ['profileid', 'globalactionid']],
				],
				'primaryKeys' => [
					['profile2globalpermissions_pk', ['profileid', 'globalactionid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2tab' => [
				'columns' => [
					'profileid' => $this->integer(10),
					'tabid' => $this->smallInteger(5),
					'permissions' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'permissions' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['profile2tab_profileid_tabid_idx', ['profileid', 'tabid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2utility' => [
				'columns' => [
					'profileid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'activityid' => $this->smallInteger(5)->notNull(),
					'permission' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'permission' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['profile2utility_tabid_activityid_idx', ['tabid', 'activityid']],
					['profile2utility_profileid', 'profileid'],
				],
				'primaryKeys' => [
					['profile2utility_pk', ['profileid', 'tabid', 'activityid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_fields' => [
				'columns' => [
					'relation_id' => $this->integer(10),
					'fieldid' => $this->integer(10),
					'fieldname' => $this->stringType(30),
					'sequence' => $this->smallInteger(3),
				],
				'index' => [
					['relation_id', 'relation_id'],
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
					'company' => $this->integer(10)->unsigned()->defaultValue(0),
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
				'index' => [
					['parentrole', 'parentrole'],
					['parentrole_2', ['parentrole', 'depth']],
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
					'sortid' => $this->smallInteger(5),
				],
				'index' => [
					['role2picklist_roleid_picklistid_idx', ['roleid', 'picklistid', 'picklistvalueid']],
					['fk_2_vtiger_role2picklist', 'picklistid'],
				],
				'primaryKeys' => [
					['role2picklist_pk', ['roleid', 'picklistvalueid', 'picklistid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_field' => [
				'columns' => [
					'fieldid' => $this->primaryKey(10),
					'blockid' => $this->integer(10),
					'name' => $this->stringType(250),
					'iconpath' => $this->stringType(300),
					'description' => $this->stringType(250),
					'linkto' => $this->text(),
					'sequence' => $this->smallInteger(3)->unsigned(),
					'active' => $this->smallInteger(1)->unsigned()->defaultValue(0),
					'pinned' => $this->smallInteger(1)->unsigned()->defaultValue(0),
					'admin_access' => $this->text(),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned(),
					'active' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
					'pinned' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
				],
				'index' => [
					['fk_1_vtiger_settings_field', 'blockid'],
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
					'error' => $this->text()
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
			's_#__companies' => [
				'columns' => [
					'id' => $this->primaryKey(5)->unsigned(),
					'name' => $this->stringType(100)->notNull(),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'industry' => $this->stringType(50),
					'city' => $this->stringType(100),
					'country' => $this->stringType(100),
					'website' => $this->stringType(100),
					'email' => $this->stringType(100),
					'logo' => $this->stringType(50),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__users_pinned' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'owner_id' => $this->integer(11)->notNull(),
					'fav_element_id' => $this->integer(11)->notNull(),
				],
				'index' => [
					['u_yf_users_pinned', 'owner_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__social_media_logs' => [
				'columns' => [
					'id' => $this->bigPrimaryKey(),
					'date' => $this->dateTime()->notNull(),
					'type' => $this->stringType(16)->notNull(),
					'name' => $this->stringType(16)->notNull(),
					'message' => $this->text()->notNull(),
				],
				'index' => [
					['date', 'date'],
					['type', 'type'],
					['name', 'name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_condition_group' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'cvid' => $this->integer(10),
					'condition' => $this->stringType(3),
					'parent_id' => $this->integer(10),
					'index' => $this->smallInteger(5),
				],
				'columns_mysql' => [
					'index' => $this->tinyInteger(5),
				],
				'index' => [
					['u_yf_cv_condition_group_cvid_idx', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_condition' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'group_id' => $this->integer(10)->unsigned(),
					'field_name' => $this->stringType(50),
					'module_name' => $this->stringType(25),
					'source_field_name' => $this->stringType(50),
					'operator' => $this->stringType(20),
					'value' => $this->text(),
					'index' => $this->tinyInteger(5),
				],
				'columns_mysql' => [
					'index' => $this->tinyInteger(5),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_duplicates' => [
				'columns' => [
					'cvid' => $this->integer(10),
					'fieldid' => $this->integer(10),
					'ignore' => $this->smallInteger(1)->notNull()->defaultValue(0)
				],
				'columns_mysql' => [
					'ignore' => $this->tinyInteger(1)->notNull()->defaultValue(0)
				],
				'index' => [
					['u_yf_cv_duplicates_cvid_idx', 'cvid'],
					['u_yf_cv_duplicates_fieldid_idx', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorite_owners' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'ownerid' => $this->integer(10)->notNull()
				],
				'index' => [
					['u_yf_favorite_owners_tabid_idx', 'tabid'],
					['u_yf_favorite_owners_userid_idx', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorite_shared_owners' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'ownerid' => $this->integer(10)->notNull()
				],
				'index' => [
					['u_yf_favorite_shared_owners_tabid_idx', 'tabid'],
					['u_yf_favorite_shared_owners_userid_idx', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__modtracker_inv' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'changes' => $this->text(),
				],
				'index' => [
					['u_yf_modtracker_inv_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],

		];
		$this->foreignKey = [
			['fk_1_a_#__record_converter', 'a_#__record_converter', 'source_module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__picklist_close_state', 'u_#__picklist_close_state', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['u_#__users_pinned_fk_1', 'u_#__users_pinned', 'owner_id', 'vtiger_users', 'id', 'CASCADE', null],
			['fk_chat_messages', 'u_#__chat_messages_crm', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__chat_messages_global_ibfk_1', 'u_#__chat_messages_global', 'globalid', 'u_#__chat_global', 'global_room_id', 'CASCADE', 'RESTRICT'],
			['fk_chat_group_messages', 'u_#__chat_messages_group', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_crm_crm', 'u_#__chat_rooms_crm', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_crm_users', 'u_#__chat_rooms_crm', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_global_global', 'u_#__chat_rooms_global', 'global_room_id', 'u_#__chat_global', 'global_room_id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_global_users', 'u_#__chat_rooms_global', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_group', 'u_#__chat_rooms_group', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_group_users', 'u_#__chat_rooms_group', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__cv_condition_fk', 'u_#__cv_condition', 'group_id', 'u_#__cv_condition_group', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__cv_condition_group_fk', 'u_#__cv_condition_group', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['u_#__cv_duplicates_cvid_fk', 'u_#__cv_duplicates', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['u_#__cv_duplicates_fieldid_fk', 'u_#__cv_duplicates', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_owners_tabid_fk', 'u_#__favorite_owners', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_owners_userid_fk', 'u_#__favorite_owners', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_shared_owners_tabid_fk', 'u_#__favorite_shared_owners', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_shared_owners_userid_fk', 'u_#__favorite_shared_owners', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__modtracker_inv_id_fk', 'u_#__modtracker_inv', 'id', 'vtiger_modtracker_basic', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__modentity_sequences_tabid_fk', 'u_#__modentity_sequences', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}
}
