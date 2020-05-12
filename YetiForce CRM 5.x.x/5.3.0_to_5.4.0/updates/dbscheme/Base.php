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
		$tabIdProducts = \App\Module::getModuleId('Products');							// 'tabid' => 14
		$tabIdSCalculations = \App\Module::getModuleId('SCalculations');				// 'tabid' => 88
		$tabIdSQuotes = \App\Module::getModuleId('SQuotes');							// 'tabid' => 89
		$tabIdSingleOrders = \App\Module::getModuleId('SSingleOrders'); 				// 'tabid' => 90
		$tabIdProductCategory = \App\Module::getModuleId('ProductCategory');			// 'tabid' => 132

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
			'i_#__magento_record'
		];
		$this->foreignKey = [
			['i_#__magento_config_ibfk_1', 'i_#__magento_config', 'server_id', 'i_#__magento_servers', 'id', 'CASCADE', null],
		];
		$this->data = [
			'vtiger_cron_task' => [
				'columns' => ['name','handler_class','frequency','laststart','lastend','status','module','sequence','description','lase_error'],
				'values' => [
				['LBL_MAGENTO','Vtiger_Magento_Cron',60,NULL,NULL,0,'Vtiger',34,NULL,NULL],
				]
			],
			'vtiger_currencies' => [
				'columns' => ['currency_name','currency_code','currency_symbol'],
				'values' => [
					['South Sudanese pound','SSP','SSÂŁ'],
					['Afghani','AFN','Af'],
					['Armenian Dram','AMD','Ô´'],
					['Kwanza','AOA','Kz'],
					['Taka','BDT','ŕ§ł'],
					['Burundi Franc','BIF','â‚Ł'],
					['Boliviano Mvdol','BOV','$b'],
					['Ngultrum','BTN','Nu'],
					['Belarussian Ruble','BYN','p.'],
					['Congolese Franc','CDF','FC'],
					['Unidad de Fomento','CLF','$'],
					['Unidad de Valor Real','COU','$'],
					['Peso Convertible','CUC','CUC$'],
					['Cabo Verde Escudo','CVE','$'],
					['Djibouti Franc','DJF','Fdj'],
					['Algerian Dinar','DZD','ŘŻŘ¬'],
					['Nakfa','ERN','Nkf'],
					['Ethiopian Birr','ETB','Br'],
					['Lari','GEL','â‚ľ'],
					['Dalasi','GMD','D'],
					['Guinean Franc','GNF','FG'],
					['Riel','KHR','áź›'],
					['Comorian Franc','KMF','CF'],
					['Loti','LSL','L'],
					['Moldovan Leu','MDL','L'],
					['Kyat','MMK','K'],
					['Pataca','MOP','	MOP$'],
					['Ouguiya','MRU','UM'],
					['Kina','PGK','K'],
					['Rwanda Franc','RWF','Râ‚Ł'],
					['Leone','SLL','Le'],
					['Dobra','STN','Db'],
					['Lilangeni','SZL','L'],
					['Somoni','TJS','SM'],
					['Turkmenistan New Manat','TMT','m'],
					['Tunisian Dinar','TND','ŘŻ.ŘŞ'],
					['Paâ€™anga','TOP','T$'],
					['BolĂ­var Soberano','VES','Bs. S.'],
					['Vatu','VUV','VT'],
					['Tala','WST','WS$'],
					['Zambian Kwacha','ZMW','ZK'],
					['Ghana, Cedis','GHS','Â˘'],
				]
			],
			'vtiger_eventhandlers' => [
				'columns' => ['event_name','handler_class','is_active','include_modules','exclude_modules','priority','owner_id'],
				'values' => [
					['EditViewPreSave','Accounts_DuplicateVatId_Handler',1,'Accounts','',5,6],
					['EditViewPreSave','Products_DuplicateEan_Handler',1,'Products','',5,14],
					['EntityBeforeSave','SSalesProcesses_Finances_Handler',1,'SSalesProcesses','',5,86],
					['IStoragesAfterUpdateStock','IStorages_RecalculateStockHandler_Handler',0,'','',5,0],
					['EditViewPreSave','IGDNC_IgdnExist_Handler',1,'IGDNC','',5,109],
					['EditViewPreSave','IGRNC_IgrnExist_Handler',1,'IGRNC','',5,108],
					['EntityBeforeSave','Vtiger_Meetings_Handler',1,'Calendar,Occurrences','',5,0]
				]
			],
			'vtiger_relatedlists' => [
				'columns' => ['tabid','related_tabid','name','sequence','label','presence','actions','favorites','creator_detail','relation_comment','view_type','field_name'],
				'values' => [
					[\App\Module::getModuleId('Contacts'),$tabIdSingleOrders,'getDependentsList',13,'SSingleOrders',0,'',0,0,0,'RelatedTab','contactid'],
					[$tabIdSingleOrders,$tabIdSingleOrders,'getDependentsList',7,'SSingleOrders',0,'',0,0,0,'RelatedTab','parent_id'],
					[$tabIdSQuotes,$tabIdSQuotes,'getDependentsList',7,'SQuotes',0,'',0,0,0,'RelatedTab','parent_id'],
					[$tabIdSCalculations,$tabIdSCalculations,'getDependentsList',7,'SCalculations',0,'',0,0,0,'RelatedTab','parent_id'],
					[$tabIdProductCategory,$tabIdProductCategory,'getDependentsList',1,'LBL_CHILD_PRODUCTCATEGORY',0,'ADD',0,0,0,'RelatedTab','parent_id'],
					[$tabIdProducts,$tabIdProductCategory,'getRelatedList',1,'ProductCategory',0,'SELECT',0,0,0,'RelatedTab',NULL],
					[$tabIdProductCategory,$tabIdProducts,'getRelatedList',2,'Products',0,'SELECT',0,0,0,'RelatedTab',NULL]
				]
			],
			'vtiger_settings_blocks' => [
				'columns' => ['label','sequence','icon','type','linkto','admin_access'],
				'values' => [
					['LBL_MARKETPLACE_YETIFORCE',0,'yfi yfi-shop',1,'index.php?module=YetiForce&parent=Settings&view=Shop',NULL]
				]
			],
			'vtiger_ssingleorders_source' => [
				'columns' => ['ssingleorders_source','sortorderid','presence'],
				'values' => [
					[5,'PLL_MAGENTO',5,1]
				]
			]
		];
	}
}
