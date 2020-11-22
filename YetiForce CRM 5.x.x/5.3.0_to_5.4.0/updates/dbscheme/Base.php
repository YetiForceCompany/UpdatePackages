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
			'dav_users' => [
				'columns' => [
					'key' => $this->stringType(500),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'i_#__magento_config' => [
				'columns' => [
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->stringType(50)->notNull(),
				],
				'index' => [
					['name', 'name'],
					['server_id', 'server_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'i_#__magento_servers' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType(50)->notNull(),
					'url' => $this->stringType()->notNull(),
					'connector' => $this->stringType(20)->notNull(),
					'user_name' => $this->stringType()->notNull(),
					'password' => $this->stringType()->notNull(),
					'store_code' => $this->stringType()->notNull(),
					'store_id' => $this->integer(10)->unsigned()->notNull(),
					'storage_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'shipping_service_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'payment_paypal_service_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'payment_cash_service_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'storage_quantity_location' => $this->stringType(20)->notNull(),
					'sync_currency' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_categories' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_products' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_customers' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_orders' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_invoices' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'product_map_class' => $this->stringType(),
					'customer_map_class' => $this->stringType(),
					'order_map_class' => $this->stringType(),
					'invoice_map_class' => $this->stringType(),
					'categories_limit' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(200),
					'products_limit' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(1000),
					'customers_limit' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(1000),
					'orders_limit' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(200),
					'invoices_limit' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(200),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_currency' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_categories' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_products' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_customers' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_orders' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'sync_invoices' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_filestore' => [
				'columns' => [
					'file_id' => $this->primaryKeyUnsigned(10),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'context' => $this->stringType(32)->notNull(),
					'filename' => $this->stringType(128)->notNull(),
					'mtime' => $this->integer(10)->notNull(),
					'data' => 'longtext NOT NULL',
				],
				'index' => [
					['uniqueness', ['user_id', 'context', 'filename'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users' => [
				'columns' => [
					'password' => $this->stringType(500),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_crm' => [
				'columns' => [
					'messages' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_global' => [
				'columns' => [
					'messages' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_group' => [
				'columns' => [
					'messages' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_private' => [
				'columns' => [
					'messages' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_user' => [
				'columns' => [
					'messages' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition' => [
				'columns' => [
					'vat_id' => $this->stringType(50),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['u_yf_competition_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['competition_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice' => [
				'columns' => [
					'finvoice_formpayment' => $this->stringType(),
					'finvoice_status' => $this->stringType(),
					'payment_sum' => $this->decimal('28,8'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__interests_conflict_conf' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'date_time' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'user_id' => $this->smallInteger(5)->unsigned()->notNull(),
					'related_id' => $this->integer(10)->unsigned()->notNull(),
					'related_label' => $this->stringType()->notNull(),
					'source_id' => $this->integer(10)->notNull()->defaultValue(0),
					'modify_user_id' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'modify_date_time' => $this->dateTime(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['related_id', 'related_id'],
					['user_id', 'user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__interests_conflict_unlock' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'date_time' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull(),
					'user_id' => $this->smallInteger(5)->notNull(),
					'related_id' => $this->integer(10)->unsigned()->notNull(),
					'source_id' => $this->integer(10)->unsigned()->notNull(),
					'comment' => $this->stringType()->notNull(),
					'modify_user_id' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'modify_date_time' => $this->dateTime(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull(),
				],
				'index' => [
					['date_time', 'date_time'],
					['related_id', 'related_id'],
					['user_id', 'user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locations_address' => [
				'columns' => [
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['locations_address_pk', 'locationaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__multicompany' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__occurrences' => [
				'columns' => [
					'meeting_url' => $this->stringType(2048),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners' => [
				'columns' => [
					'vat_id' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations' => [
				'columns' => [
					'parent_id' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'index' => [
					['u_yf_scalculations_parent_id_idx', 'parent_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes' => [
				'columns' => [
					'parent_id' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'index' => [
					['u_yf_squotes_parent_id_idx', 'parent_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'primaryKeys' => [
					['srecurringorders_address_pk', 'srecurringordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocesses' => [
				'columns' => [
					'estimated_margin' => $this->decimal('28,8'),
					'expected_margin' => $this->decimal('28,8'),
					'expected_sale' => $this->decimal('28,8'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders' => [
				'columns' => [
					'contactid' => $this->integer(10)->unsigned()->defaultValue(0),
					'parent_id' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'index' => [
					['u_yf_ssingleorders_contactid_idx', 'contactid'],
					['u_yf_ssingleorders_parent_id_idx', 'parent_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__users_labels' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'label' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['users_labels_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_account' => [
				'columns' => [
					'vat_id' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accountaddress' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
					'buildingnumberb' => $this->stringType(),
					'localnumberb' => $this->stringType(50),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity' => [
				'columns' => [
					'meeting_url' => $this->stringType(2048),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactaddress' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
					'buildingnumberb' => $this->stringType(),
					'localnumberb' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactdetails' => [
				'columns' => [
					'gender' => $this->stringType()->defaultValue(''),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'columns' => [
					'frequency' => $this->integer(10)->unsigned(),
					'status' => $this->smallInteger(1),
					'module' => $this->stringType(25),
					'lase_error' => $this->text(),
					'max_exe_time' => $this->smallInteger(5),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entityname' => [
				'columns' => [
					'separator' => $this->stringType(5),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'columns' => [
					'sort' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadaddress' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leaddetails' => [
				'columns' => [
					'vat_id' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modentity_num' => [
				'columns' => [
					'prefix' => $this->stringType()->notNull()->defaultValue(''),
					'postfix' => $this->stringType()->notNull()->defaultValue('')
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		/*	'vtiger_payment_methods' => [
				'columns' => [
					'payment_methodsid' => $this->primaryKey(),
					'payment_methods' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'sortorderid' => $this->smallInteger()->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],*/
			'vtiger_products' => [
				'columns' => [
					'ean' => $this->stringType(64),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'secondary_email' => $this->stringType(100)->defaultValue(''),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendor' => [
				'columns' => [
					'vat_id' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendoraddress' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'buildingnumberb' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumbera' => $this->stringType(50),
					'localnumberb' => $this->stringType(50),
					'localnumberc' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'color' => $this->stringType(10)->defaultValue('')
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_fields' => [
				'columns' => [
					'relation_id' => $this->smallInteger(5)->unsigned()->notNull(),
					'fieldid' => $this->integer(10)->notNull(),
					'sequence' => $this->smallInteger(3),
				],
				'index' => [
					['fk_1_relatedlists_fields_fieldid', 'fieldid'],
					['relation_id', 'relation_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->dropTables = [
			'i_#__magento_record'
		];
		$this->foreignKey = [
			['i_#__magento_config_ibfk_1', 'i_#__magento_config', 'server_id', 'i_#__magento_servers', 'id', 'CASCADE', null],
			['user_id_fk_filestore', 'roundcube_filestore', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['vtiger_modcomments_modcommentsid_fk', 'vtiger_modcomments', 'modcommentsid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['vtiger_relatedlists_fields_ibfk_1', 'vtiger_relatedlists_fields', 'relation_id', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
		];
	}
}
