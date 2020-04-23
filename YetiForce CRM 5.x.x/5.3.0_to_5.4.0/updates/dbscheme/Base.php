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
			'roundcube_users' => [
				'columns' => [
					'password' => $this->stringType(500),
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
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_address' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
					'first_name_a' => $this->stringType(),
					'last_name_a' => $this->stringType(),
					'vat_id_a' => $this->stringType(50),
					'email_a' => $this->stringType(100),
					'phone_a' => $this->stringType(100),
					'addresslevel8b' => $this->stringType(),
					'localnumberb' => $this->stringType(50),
					'addresslevel5b' => $this->stringType(),
					'buildingnumberb' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'poboxb' => $this->stringType(50),
					'first_name_b' => $this->stringType(),
					'last_name_b' => $this->stringType(),
					'company_name_b' => $this->stringType(),
					'vat_id_b' => $this->stringType(50),
					'email_b' => $this->stringType(100),
					'phone_b' => $this->stringType(100),
					'company_name_a' => $this->stringType(),
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
			'u_#__finvoiceproforma_address' => [
				'columns' => [
					'localnumberc' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
					'addresslevel8b' => $this->stringType(),
					'localnumberb' => $this->stringType(50),
					'addresslevel5b' => $this->stringType(),
					'buildingnumberb' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'poboxb' => $this->stringType(50),
					'first_name_a' => $this->stringType(),
					'first_name_b' => $this->stringType(),
					'last_name_a' => $this->stringType(),
					'last_name_b' => $this->stringType(),
					'company_name_a' => $this->stringType(),
					'company_name_b' => $this->stringType(),
					'vat_id_a' => $this->stringType(50),
					'vat_id_b' => $this->stringType(50),
					'email_a' => $this->stringType(100),
					'email_b' => $this->stringType(100),
					'phone_a' => $this->stringType(100),
					'phone_b' => $this->stringType(100),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__github' => [
				'columns' => [
					'token' => $this->stringType(500),
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
			'u_#__multicompany' => [
				'columns' => [
					'buildingnumbera' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__occurrences' => [
				'columns' => [
					'meeting_utl' => $this->stringType(),
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
					'first_name_a' => $this->stringType(),
					'last_name_a' => $this->stringType(),
					'company_name_a' => $this->stringType(),
					'vat_id_a' => $this->stringType(50),
					'email_a' => $this->stringType(100),
					'phone_a' => $this->stringType(100),
					'addresslevel8b' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'buildingnumberb' => $this->stringType(),
					'localnumberb' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
					'first_name_b' => $this->stringType(),
					'last_name_b' => $this->stringType(),
					'company_name_b' => $this->stringType(),
					'vat_id_b' => $this->stringType(50),
					'email_b' => $this->stringType(100),
					'phone_b' => $this->stringType(100),
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
					'meeting_utl' => $this->stringType(),
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
					'status' => $this->smallInteger(1),
					'module' => $this->stringType(25),
					'lase_error' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1),
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
			'vtiger_gender' => [
				'columns' => [
					'genderid' => $this->primaryKey(),
					'gender' => $this->stringType(),
					'presence' => $this->smallInteger(1)->defaultValue(1),
					'sortorderid' => $this->smallInteger()->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->defaultValue(1),
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
			'vtiger_payment_methods' => [
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
			],
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
		];
		$this->dropColumns = [
			'u_#__ssingleorders' => ['company'],
			'vtiger_account' => ['ownership'],
			'vtiger_finvoicecost_paymentstatus' => ['picklist_valueid'],
			'vtiger_relatedlists_fields' => ['fieldname'],
			'vtiger_troubletickets' => ['ordertime', 'contract_type', 'contracts_end_date'],
		];
		$this->dropTables = [
			'i_#__magento_record', 'vtiger_fcorectinginvoice_formpayment', 'vtiger_finvoice_formpayment', 'vtiger_finvoicecost_formpayment', 'vtiger_finvoiceproforma_formpayment', 'vtiger_ssingleorders_method_payments'
		];
		$this->foreignKey = [
			['i_#__magento_config_ibfk_1', 'i_#__magento_config', 'server_id', 'i_#__magento_servers', 'id', 'CASCADE', ''],
			['fk_1_u_#__productcategoryproductcategoryid', 'u_#__productcategory', 'productcategoryid', 'vtiger_crmentity', 'crmid', 'CASCADE', ''],
			['fk_1_u_#__productcategorycfproductcategoryid', 'u_#__productcategorycf', 'productcategoryid', 'u_#__productcategory', 'productcategoryid', 'CASCADE', ''],
		];
	}
}
