<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base4 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_status' => [
				'columns' => [
					'statusid' => $this->integer(10)->autoIncrement()->notNull(),
					'status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer(10)->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(10)->defaultValue(0),
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
					'view_type' => $this->stringType(100)->notNull()->defaultValue('RelatedTab'),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned()->notNull(),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'favorites' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'creator_detail' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'relation_comment' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'user_name' => $this->stringType(32),
					'first_name' => $this->stringType(30),
					'last_name' => $this->stringType(30),
					'email1' => $this->stringType(100),
					'is_admin' => $this->stringType(3)->defaultValue(0),
					'status' => $this->stringType(25),
					'deleted' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'language' => $this->stringType(36),
					'user_password' => $this->stringType(200),
					'internal_mailer' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'reports_to_id' => $this->integer(10)->unsigned(),
					'modified_user_id' => $this->stringType(36),
					'currency_id' => $this->integer(10)->notNull()->defaultValue(1),
					'description' => $this->text(),
					'date_entered' => $this->timestamp()->null(),
					'date_modified' => $this->timestamp()->null(),
					'date_password_change' => $this->dateTime(),
					'force_password_change' => $this->smallInteger(1)->defaultValue(0),
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
					'reminder_interval' => $this->stringType(100),
					'reminder_next_time' => $this->stringType(100),
					'theme' => $this->stringType(100),
					'tz' => $this->stringType(30),
					'time_zone' => $this->stringType(200),
					'currency_grouping_pattern' => $this->stringType(100),
					'currency_decimal_separator' => $this->stringType(2),
					'currency_grouping_separator' => $this->stringType(2),
					'currency_symbol_placement' => $this->stringType(20),
					'no_of_currency_decimals' => $this->smallInteger(1)->unsigned(),
					'truncate_trailing_zeros' => $this->smallInteger(1)->unsigned(),
					'dayoftheweek' => $this->stringType(100),
					'callduration' => $this->smallInteger(3)->unsigned(),
					'othereventduration' => $this->smallInteger(3)->unsigned(),
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
					'phone_crm_extension' => $this->stringType(100),
					'phone_crm_extension_extra' => $this->stringType(100),
					'accesskey' => $this->stringType(36),
					'confirm_password' => $this->stringType(200),
					'cal_color' => $this->stringType(25),
					'user_preferences' => $this->text(),
				],
				'columns_mysql' => [
					'deleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'internal_mailer' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'force_password_change' => $this->tinyInteger(1)->defaultValue(0),
					'no_of_currency_decimals' => $this->tinyInteger(1)->unsigned(),
					'truncate_trailing_zeros' => $this->tinyInteger(1)->unsigned(),
					'leftpanelhide' => $this->tinyInteger(3)->unsigned(),
					'emailoptout' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(1),
					'available' => $this->tinyInteger(1)->defaultValue(0),
					'auto_assign' => $this->tinyInteger(1)->defaultValue(0),
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
			'vtiger_reservations' => [
				'index' => [
					['process', 'process'],
					['link', 'link'],
					['subprocess', 'subprocess'],
					['linkextend', 'linkextend'],
					['deleted', 'deleted'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
