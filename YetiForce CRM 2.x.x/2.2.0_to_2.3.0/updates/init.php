<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');

class YetiForceUpdate
{

	var $package;
	var $modulenode;
	var $return = true;
	var $filesToDelete = [
		'layouts/vlayout/modules/Users/Body.tpl',
		'layouts/vlayout/modules/Vtiger/innventoryFields',
		'layouts/vlayout/modules/Vtiger/Body.tpl',
		'layouts/vlayout/modules/Vtiger/BodyContent.tpl',
		'layouts/vlayout/modules/Vtiger/BodyHeader.tpl',
		'layouts/vlayout/modules/Vtiger/BodyHidden.tpl',
		'layouts/vlayout/modules/Vtiger/BodyLeft.tpl',
		'layouts/vlayout/modules/Vtiger/Menu.tpl',
		'modules/Vtiger/innventoryFields'
	];

	function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	function preupdate()
	{
		//$this->package->_errorText = 'Errot';
		return true;
	}

	function update()
	{
		$this->updateFiles();
		$this->databaseSchema();
		$this->databaseData();
	}

	function postupdate()
	{
		return true;
	}

	function updateFiles()
	{
		global $log, $root_directory;
		$log->debug("Entering YetiForceUpdate::updateFiles() method ...");
		if (!$root_directory)
			$root_directory = getcwd();
		$config = $root_directory . '/config/config.inc.php';
		if (file_exists($config)) {
			$configContent = file($config);
			$unblockedTimeoutCronTasks = true;
			$gsAutocomplete = true;
			foreach ($configContent as $key => $line) {
				if (strpos($line, 'isActiveSendingMails') !== false) {
					$configContent[$key] = str_replace(['false', 'FALSE'], ['true', 'true'], $configContent[$key]);
				}
				if (strpos($line, "unblockedTimeoutCronTasks") !== false) {
					$unblockedTimeoutCronTasks = false;
				}
			}
			$content = implode("", $configContent);
			if ($unblockedTimeoutCronTasks) {
				$content .= '
// Should the task in cron be unblocked if the script execution time was exceeded
$unblockedTimeoutCronTasks = true;

// The maximum time of executing a cron. Recommended same as the max_exacution_time parameter value.
$maxExecutionCronTime = 3600;

';
			}
			$file = fopen($config, "w+");
			fwrite($file, $content);
			fclose($file);
		}
		$log->debug("Exiting YetiForceUpdate::updateFiles() method ... ");
	}

	function databaseSchema()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::databaseSchema() method ...");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_tab` LIKE 'trial';");
		if ($adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_tab` 
				CHANGE `color` `color` varchar(30) NULL after `parent` , 
				CHANGE `coloractive` `coloractive` tinyint(1) NULL DEFAULT 0 after `color` , 
				DROP COLUMN `trial` ;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_tab` LIKE 'type';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_tab` ADD COLUMN `type` tinyint(1) NULL DEFAULT 0 after `coloractive`;");
		}
		$result = $adb->query("SHOW KEYS FROM `yetiforce_currencyupdate` WHERE Key_name='fetchdate_currencyid_unique';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `yetiforce_currencyupdate` ADD UNIQUE KEY `fetchdate_currencyid_unique`(`currency_id`,`exchange_date`,`bank_id`) ;");
		}
		$result = $adb->query("SHOW KEYS FROM `yetiforce_currencyupdate_banks` WHERE Key_name='unique_bankname';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `yetiforce_currencyupdate_banks` ADD UNIQUE KEY `unique_bankname`(`bank_name`) ;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `a_yf_discounts_config` (
				`param` varchar(30) NOT NULL,
				`value` varchar(255) NOT NULL,
				PRIMARY KEY (`param`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `a_yf_discounts_global` (
				`id` int(19) unsigned NOT NULL  auto_increment , 
				`name` varchar(50) NOT NULL  , 
				`value` decimal(5,2) unsigned NOT NULL  DEFAULT 0.00 , 
				`status` tinyint(1) NOT NULL  DEFAULT 1 , 
				PRIMARY KEY (`id`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `a_yf_inventory_limits` (
				`id` int(19) unsigned NOT NULL  auto_increment , 
				`status` tinyint(1) NOT NULL  DEFAULT 0 , 
				`name` varchar(50) NOT NULL  , 
				`value` int(10) unsigned NOT NULL  , 
				PRIMARY KEY (`id`) 
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `a_yf_taxes_config` (
				`param` varchar(30) NOT NULL,
				`value` varchar(255) NOT NULL,
				PRIMARY KEY (`param`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `a_yf_taxes_global` (
				`id` int(19) unsigned NOT NULL  auto_increment , 
				`name` varchar(50) NOT NULL  , 
				`value` decimal(5,2) unsigned NOT NULL  DEFAULT 0.00 , 
				`status` tinyint(1) NOT NULL  DEFAULT 1 , 
				PRIMARY KEY (`id`) 
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$result = $adb->query("SHOW COLUMNS FROM `vtiger_account` LIKE 'inventorybalance';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_account` ADD COLUMN `inventorybalance` decimal(25,8)   NULL DEFAULT 0.00000000 after `sum_time`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_account` LIKE 'discount';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_account` ADD COLUMN `discount` decimal(5,2)   NULL DEFAULT 0.00 after `inventorybalance`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_account` LIKE 'creditlimit';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_account` ADD COLUMN `creditlimit` int(10)   NULL after `discount`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_products` LIKE 'taxes';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_products` ADD COLUMN `taxes` varchar(50) NULL after `currency_id`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_role` LIKE 'listrelatedrecord';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_role` ADD COLUMN `listrelatedrecord` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `clendarallorecords`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_role` LIKE 'previewrelatedrecord';");
		if (!$adb->getRowCount($result)) {
			$adb->query("ALTER TABLE `vtiger_role` ADD COLUMN `previewrelatedrecord` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `listrelatedrecord`;");
		}
		
		$adb->query("CREATE TABLE IF NOT EXISTS `s_yf_accesstorecord` (
				`id` int(19) unsigned NOT NULL AUTO_INCREMENT,
				`username` varchar(50) NOT NULL,
				`date` datetime NOT NULL,
				`ip` varchar(100) NOT NULL,
				`record` int(19) NOT NULL,
				`module` varchar(30) NOT NULL,
				`url` varchar(300) NOT NULL,
				`description` varchar(300) NOT NULL,
				`agent` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$log->debug("Exiting YetiForceUpdate::databaseSchema() method ...");
	}

	function databaseData()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");

		$result = $adb->query('SELECT * FROM `a_yf_discounts_config`;');
		if (!$adb->getRowCount($result)) {
			$adb->pquery('insert  into `a_yf_discounts_config`(`param`,`value`) values (?,?);', ['active', '0']);
			$adb->pquery('insert  into `a_yf_discounts_config`(`param`,`value`) values (?,?);', ['aggregation', '0']);
			$adb->pquery('insert  into `a_yf_discounts_config`(`param`,`value`) values (?,?);', ['discounts', '0,1,2']);
		}
		$result = $adb->query('SELECT * FROM `a_yf_taxes_config`;');
		if (!$adb->getRowCount($result)) {
			$adb->pquery('insert  into `a_yf_taxes_config`(`param`,`value`) values (?,?);', ['active', '0']);
			$adb->pquery('insert  into `a_yf_taxes_config`(`param`,`value`) values (?,?);', ['aggregation', '0']);
			$adb->pquery('insert  into `a_yf_taxes_config`(`param`,`value`) values (?,?);', ['taxs', '0,1,2,3']);
		}
		$blockId = getBlockId(getTabid('Accounts'), 'LBL_FINANSIAL_SUMMARY');
		if ($blockId) {
			$adb->pquery('UPDATE `vtiger_field` SET `block` = ?, `sequence` = ? WHERE `tabid` = ? AND `columnname` = ?;', [$blockId, '6', getTabid('Accounts'), 'payment_balance']);
		}
		$this->addFields();

		$integrationBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?', ['LBL_EXTENDED_MODULES']);
		$integrationBlockCount = $adb->getRowCount($integrationBlock);

		// To add Block
		if ($integrationBlockCount > 0) {
			$blockid = $adb->query_result($integrationBlock, 0, 'blockid');
		} else {
			$blockid = $adb->getUniqueID('vtiger_settings_blocks');
			$sequenceResult = $adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_blocks", []);
			if ($adb->getRowCount($sequenceResult)) {
				$sequence = $adb->query_result($sequenceResult, 0, 'sequence');
			}
			$adb->pquery("INSERT INTO vtiger_settings_blocks(blockid, label, sequence) VALUES(?,?,?)", [$blockid, 'LBL_EXTENDED_MODULES', ++$sequence]);
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_CREDITLIMITS']);
		if (!$adb->getRowCount($result)) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_CREDITLIMITS', '', 'LBL_CREDITLIMITS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=CreditLimits']);
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_TAXES']);
		if (!$adb->getRowCount($result)) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_TAXES', '', 'LBL_TAXES_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Taxes']);
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_TAXCONFIGURATION']);
		if (!$adb->getRowCount($result)) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_TAXCONFIGURATION', '', 'LBL_TAXCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=TaxConfiguration']);
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_DISCOUNTS']);
		if (!$adb->getRowCount($result)) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_DISCOUNTS', '', 'LBL_DISCOUNTS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Discounts']);
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_DISCOUNTCONFIGURATION']);
		if (!$adb->getRowCount($result)) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_DISCOUNTCONFIGURATION', '', 'LBL_DISCOUNTCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=DiscountConfiguration']);
		}

		$result = $adb->pquery("SELECT * FROM `vtiger_ossmailscanner_config` WHERE `conf_type` = ? AND `parameter` = ? ", ['exceptions', 'crating_mails']);
		$num = $adb->getRowCount($result);
		if ($num > 1) {
			$adb->pquery('UPDATE `vtiger_ossmailscanner_config` SET `parameter` = ?, `value` = ? WHERE `parameter` = ? AND `conf_type` = ? LIMIT 1;', ['crating_tickets', '', 'crating_mails', 'exceptions']);
		}
		
		$adb->pquery("UPDATE `vtiger_relatedlists` SET `actions` = ?, `name` = ?  WHERE tabid = ? AND `related_tabid` = ? AND `name` = ?;", ['ADD','get_dependents_list',getTabid('Vendors'),getTabid('Contacts'), 'get_contacts']);
		
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}

	public function addFields()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addFields() method ...");
		include_once('vtlib/Vtiger/Module.php');

		$columnName = ["tabid", "id", "column", "table", "generatedtype", "uitype", "name", "label", "readonly", "presence", "defaultvalue", "maximumlength", "sequence", "block", "displaytype", "typeofdata", "quickcreate", "quicksequence", "info_type", "masseditable", "helpinfo", "summaryfield", "fieldparams", "columntype", "blocklabel", "setpicklistvalues", "setrelatedmodules"];

		$Accounts = [
			['6', '1756', 'inventorybalance', 'vtiger_account', '1', '7', 'inventorybalance', 'LBL_INVENTORY_BALANCE', '1', '2', '', '100', '7', '198', '10', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', "decimal(25,8)", "LBL_FINANSIAL_SUMMARY", [], []],
			['6', '1757', 'discount', 'vtiger_account', '1', '9', 'discount', 'LBL_DISCOUNT', '1', '2', '', '100', '8', '198', '1', 'N~O~2~2', '1', NULL, 'BAS', '1', '', '0', '', "decimal(5,2)", "LBL_FINANSIAL_SUMMARY", [], []],
			['6', '1758', 'creditlimit', 'vtiger_account', '1', '304', 'creditlimit', 'LBL_CREDIT_LIMIT', '1', '2', '', '100', '9', '198', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', "int(10)", "LBL_FINANSIAL_SUMMARY", [], []]
		];
		$Products = [
			['14', '1759', 'taxes', 'vtiger_products', '1', '303', 'taxes', 'LBL_TAXES', '1', '2', '', '100', '5', '32', '1', 'V~I', '1', NULL, 'BAS', '1', '', '0', '', "varchar(50)", "LBL_PRICING_INFORMATION", [], []]
		];


		$setToCRM = ['Accounts' => $Accounts, 'Products' => $Products];

		$setToCRMAfter = [];
		foreach ($setToCRM as $nameModule => $module) {
			if (!$module)
				continue;
			foreach ($module as $key => $fieldValues) {
				for ($i = 0; $i < count($fieldValues); $i++) {
					$setToCRMAfter[$nameModule][$key][$columnName[$i]] = $fieldValues[$i];
				}
			}
		}
		foreach ($setToCRMAfter as $moduleName => $fields) {
			foreach ($fields as $field) {
				if (self::checkFieldExists($field, $moduleName)) {
					continue;
				}
				$moduleInstance = Vtiger_Module::getInstance($moduleName);
				$blockInstance = Vtiger_Block::getInstance($field['blocklabel'], $moduleInstance);
				$fieldInstance = new Vtiger_Field();
				$fieldInstance->column = $field['column'];
				$fieldInstance->name = $field['name'];
				$fieldInstance->label = $field['label'];
				$fieldInstance->table = $field['table'];
				$fieldInstance->uitype = $field['uitype'];
				$fieldInstance->typeofdata = $field['typeofdata'];
				$fieldInstance->readonly = $field['readonly'];
				$fieldInstance->displaytype = $field['displaytype'];
				$fieldInstance->masseditable = $field['masseditable'];
				$fieldInstance->quickcreate = $field['quickcreate'];
				$fieldInstance->columntype = $field['columntype'];
				$fieldInstance->presence = $field['presence'];
				$fieldInstance->maximumlength = $field['maximumlength'];
				$fieldInstance->quicksequence = $field['quicksequence'];
				$fieldInstance->info_type = $field['info_type'];
				$fieldInstance->helpinfo = $field['helpinfo'];
				$fieldInstance->summaryfield = $field['summaryfield'];
				$fieldInstance->generatedtype = $field['generatedtype'];
				$fieldInstance->defaultvalue = $field['defaultvalue'];
				$blockInstance->addField($fieldInstance);
				if ($field['setpicklistvalues'] && ($field['uitype'] == 15 || $field['uitype'] == 16 || $field['uitype'] == 33 ))
					$fieldInstance->setPicklistValues($field['setpicklistvalues']);
				if ($field['setrelatedmodules'] && $field['uitype'] == 10) {
					$fieldInstance->setRelatedModules($field['setrelatedmodules']);
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::addFields() method ...");
	}

	public function checkFieldExists($field, $moduleName)
	{
		global $adb;
		if ($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", [$field[1], $field[4]]);
		else
			$result = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", [$field['column'], $field['table'], getTabid($moduleName)]);
		if (!$adb->getRowCount($result)) {
			return false;
		}
		return true;
	}
}
