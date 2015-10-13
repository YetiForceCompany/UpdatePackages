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
		'languages/de_de/Test.php',
		'languages/en_us/Test.php',
		'languages/nl_nl/Test.php',
		'languages/pl_pl/Test.php',
		'languages/pt_br/Test.php',
		'languages/ru_ru/Test.php',
		'modules/Test/Test.php',
		'layouts/vlayout/modules/Events/uitypes/FollowUp.tpl',
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
		$this->roundcubeConfig();
		$this->databaseSchema();
		$this->databaseData();
		$this->cleanDB();
		$this->verificationOfPowers();
	}

	function postupdate()
	{
		return true;
	}

	public function changeOutgoingServerFile()
	{
		global $log, $adb, $root_directory;
		$log->debug("Entering YetiForceUpdate::changeOutgoingServerFile() method ...");
		$result = $adb->pquery("SELECT ossmailtemplatesid FROM `vtiger_ossmailtemplates` WHERE `name` = ? AND oss_module_list = ? LIMIT 1", ['Test mail about the mail server configuration.', 'Users']);
		$id = $adb->getSingleValue($result);
		if ($id) {
			if (!$root_directory)
				$root_directory = getcwd();
			$fileName = $root_directory . '/modules/Settings/Vtiger/models/OutgoingServer.php';
			if (file_exists($fileName)) {
				$completeData = file_get_contents($fileName);
				$updatedFields = "'id'";
				$patternString = "%s => %s,";
				$pattern = '/' . $updatedFields . '[\s]+=([^,]+),/';
				$replacement = sprintf($patternString, $updatedFields, ltrim($id, '0'));
				$fileContent = preg_replace($pattern, $replacement, $completeData);
				$filePointer = fopen($fileName, 'w');
				fwrite($filePointer, $fileContent);
				fclose($filePointer);
			}
		}
		$log->debug("Exiting YetiForceUpdate::changeOutgoingServerFile() method ...");
	}

	public function updateForgotPassword()
	{
		global $log, $adb, $root_directory;
		$log->debug("Entering YetiForceUpdate::updateForgotPassword() method ...");
		$result = $adb->pquery("SELECT ossmailtemplatesid FROM `vtiger_ossmailtemplates` WHERE `name` = ? AND oss_module_list = ? LIMIT 1", ['ForgotPassword', 'Users']);
		$id = $adb->getSingleValue($result);
		if ($id) {
			if (!$root_directory)
				$root_directory = getcwd();
			$fileName = $root_directory . '/modules/Users/actions/ForgotPassword.php';
			if (file_exists($fileName)) {
				$completeData = file_get_contents($fileName);
				$updatedFields = "'id'";
				$patternString = "%s => %s,";
				$pattern = '/' . $updatedFields . '[\s]+=([^,]+),/';
				$replacement = sprintf($patternString, $updatedFields, ltrim($id, '0'));
				$fileContent = preg_replace($pattern, $replacement, $completeData);
				$filePointer = fopen($fileName, 'w');
				fwrite($filePointer, $fileContent);
				fclose($filePointer);
			}
		}
		$log->debug("Exiting YetiForceUpdate::updateForgotPassword() method ...");
	}

	function roundcubeConfig()
	{
		global $log, $root_directory;
		$log->debug("Entering YetiForceUpdate::roundcubeConfig() method ...");
		if (!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory . '/modules/OSSMail/roundcube/config/config.inc.php';
		if (file_exists($fileName)) {
			$config = OSSMail_Record_Model::getViewableData();
			if (!is_array($config['default_host'])) {
				$fileContent = file_get_contents($fileName);
				$value = $config['default_host'];
				if ($value == 'ssl://imap.gmail.com') {
					$value = 'ssl://smtp.gmail.com';
				}
				$saveValue = "['$value' => '$value',]";
				$patternString = "\$config['default_host'] = %s;";
				$pattern = '/(\$config\[\'default_host\'\])[\s]+=([^;]+);/';
				$replacement = sprintf($patternString, $saveValue);
				$fileContent = preg_replace($pattern, $replacement, $fileContent);
				$filePointer = fopen($fileName, 'w');
				fwrite($filePointer, $fileContent);
				fclose($filePointer);
			}
		}
		$log->debug("Exiting YetiForceUpdate::roundcubeConfig() method ... ");
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
			$langInLoginView = true;
			foreach ($configContent as $key => $line) {
				if (strpos($line, 'isActiveSendingMails') !== false) {
					$configContent[$key] = str_replace(['false', 'FALSE'], ['true', 'true'], $configContent[$key]);
				}
				if (strpos($line, "unblockedTimeoutCronTasks") !== false) {
					$unblockedTimeoutCronTasks = false;
				}
				if (strpos($line, "langInLoginView") !== false) {
					$langInLoginView = false;
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
			if ($langInLoginView) {
				$content .= "// System's language selection in the login window (true/false).
\$langInLoginView = false;

";
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

		$result = $adb->query("SHOW COLUMNS FROM `vtiger_role` LIKE 'editrelatedrecord';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_role` ADD COLUMN `editrelatedrecord` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `previewrelatedrecord`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_role` LIKE 'permissionsrelatedfield';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_role` ADD COLUMN `permissionsrelatedfield` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `editrelatedrecord`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_role` LIKE 'globalsearchadv';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_role` ADD COLUMN `globalsearchadv` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `permissionsrelatedfield`;");
		}

		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_activity_update_dates`(
				`activityid` int(19) NOT NULL  , 
				`parent` int(19) NOT NULL  , 
				`task_id` int(19) NOT NULL  , 
				PRIMARY KEY (`activityid`) , 
				KEY `parent`(`parent`) , 
				KEY `vtiger_activity_update_dates_ibfk_1`(`task_id`) , 
				CONSTRAINT `vtiger_activity_update_dates_ibfk_1` 
				FOREIGN KEY (`task_id`) REFERENCES `com_vtiger_workflowtasks` (`task_id`) ON DELETE CASCADE , 
				CONSTRAINT `vtiger_activity_update_dates_ibfk_2` 
				FOREIGN KEY (`parent`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE , 
				CONSTRAINT `vtiger_activity_update_dates_ibfk_3` 
				FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE 
			) ENGINE=InnoDB DEFAULT CHARSET='utf8'");

		$adb->query("CREATE TABLE IF NOT EXISTS `s_yf_multireference` (
				`source_module` varchar(50) NOT NULL,
				`dest_module` varchar(50) NOT NULL,
				`lastid` int(19) unsigned NOT NULL DEFAULT '0',
				KEY `source_module` (`source_module`,`dest_module`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$result = $adb->query("SHOW COLUMNS FROM `yetiforce_menu` LIKE 'filters';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `yetiforce_menu` ADD COLUMN `filters` varchar(255) NULL after `hotkey`;");
		}

		$result = $adb->query("SHOW KEYS FROM `vtiger_account` WHERE Key_name='accountname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_account` ADD KEY `accountname`(`accountname`) ;;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_contactdetails` WHERE Key_name='lastname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_contactdetails` ADD KEY `lastname`(`lastname`) ;;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_leaddetails` WHERE Key_name='lastname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_leaddetails` ADD KEY `lastname`(`lastname`) ;;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_project` WHERE Key_name='projectname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_project` ADD KEY `projectname`(`projectname`) ;;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_projecttask` WHERE Key_name='projecttaskname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_projecttask` ADD KEY `projecttaskname`(`projecttaskname`) ;;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_vendor` WHERE Key_name='vendorname';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_vendor` ADD KEY `vendorname`(`vendorname`) ;;");
		}

		$adb->query("CREATE TABLE IF NOT EXISTS `l_yf_access_to_record` (
				`id` int(19) unsigned NOT NULL AUTO_INCREMENT,
				`username` varchar(50) NOT NULL,
				`date` datetime NOT NULL,
				`ip` varchar(100) NOT NULL,
				`record` int(19) NOT NULL,
				`module` varchar(30) NOT NULL,
				`url` varchar(300) NOT NULL,
				`agent` varchar(255) NOT NULL,
				PRIMARY KEY (`id`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$adb->query("CREATE TABLE IF NOT EXISTS `l_yf_switch_users` (
				`id` int(19) unsigned NOT NULL AUTO_INCREMENT,
				`date` datetime NOT NULL,
				`status` varchar(10) NOT NULL,
				`baseid` int(19) NOT NULL,
				`destid` int(19) NOT NULL,
				`busername` varchar(50) NOT NULL,
				`dusername` varchar(50) NOT NULL,
				`ip` varchar(100) NOT NULL,
				`agent` varchar(255) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `baseid` (`baseid`),
				KEY `destid` (`destid`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$adb->query('DROP TABLE IF EXISTS `s_yf_accesstorecord`;');

		$result = $adb->query("SHOW COLUMNS FROM `vtiger_modtracker_basic` LIKE 'whodidsu';");
		if (!$adb->num_rows($result)) {
			$adb->query("ALTER TABLE `vtiger_modtracker_basic` ADD COLUMN `whodidsu` int(20) DEFAULT NULL ;");
		}

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

		$adb->pquery("UPDATE `vtiger_relatedlists` SET `actions` = ?, `name` = ?  WHERE tabid = ? AND `related_tabid` = ? AND `name` = ?;", ['ADD', 'get_dependents_list', getTabid('Vendors'), getTabid('Contacts'), 'get_contacts']);

		$adb->pquery("UPDATE `vtiger_entityname` SET `fieldname` = ?, `searchcolumn` = ? WHERE `fieldname` = ? AND `tabid` = ?;", ['company', 'company', 'lastname', getTabid('Leads')]);
		$adb->pquery("UPDATE `vtiger_field` SET `uitype` = ?, `typeofdata` = ?, `presence` = ? WHERE `fieldname` = ? AND `tabid` = ?;", ['2', 'V~O', '1', 'lastname', getTabid('Leads')]);
		$adb->pquery("UPDATE `vtiger_field` SET `typeofdata` = ? WHERE `fieldname` IN (?) AND `tabid` = ?;", ['V~O', 'legal_form', getTabid('Leads')]);
		$adb->pquery("UPDATE `vtiger_field` SET `typeofdata` = ? WHERE `fieldname` IN (?) AND `tabid` = ?;", ['V~O', 'vat_id', getTabid('Leads')]);

		$adb->pquery("UPDATE `vtiger_blocks` SET `sequence` = ? WHERE `tabid` = ? AND `blocklabel` = ? AND `sequence` = ?;", [7, getTabid('Events'), 'LBL_DESCRIPTION_INFORMATION', 6]);
		$adb->pquery("UPDATE `vtiger_blocks` SET `sequence` = ? WHERE `tabid` = ? AND `blocklabel` = ? AND `sequence` = ?;", [6, getTabid('SMSNotifier'), 'LBL_DESCRIPTION_INFORMATION', 5]);
		$adb->pquery("UPDATE `vtiger_blocks` SET `sequence` = ? WHERE `tabid` = ? AND `blocklabel` = ? AND `sequence` = ?;", [7, getTabid('OSSMailView'), 'LBL_DESCRIPTION_INFORMATION', 6]);
		$adb->pquery("UPDATE `vtiger_blocks` SET `sequence` = ? WHERE `tabid` = ? AND `blocklabel` = ? AND `sequence` = ?;", [7, getTabid('OSSPasswords'), 'LBL_DESCRIPTION_INFORMATION', 6]);
		$adb->pquery("UPDATE `vtiger_blocks` SET `sequence` = ? WHERE `tabid` = ? AND `blocklabel` = ? AND `sequence` = ?;", [7, getTabid('OSSPasswords'), 'LBL_DESCRIPTION_INFORMATION', 6]);

		$this->deleteInheritsharing();
		$this->move();

		$result = $adb->pquery("SELECT actionid FROM `vtiger_actionmapping` WHERE `actionname` = ? ", ['TagCloud']);
		if ($adb->getRowCount($result)) {
			$actionid = $adb->getSingleValue($result);
			$result = $adb->pquery("SELECT actionid FROM `vtiger_actionmapping` WHERE `actionid` = ? ", [$actionid]);
			if ($adb->getRowCount($result) > 1) {
				$adb->pquery('DELETE FROM vtiger_actionmapping WHERE actionname=?', ['TagCloud']);
			}
		}
		$this->addActionMap();
		$this->addWorkflowType();

		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ?;", array(getTabid('Contacts'), getTabid('Events'), 'get_dependents_list'));
		if ($adb->getRowCount($result) == 0) {
			$moduleInstance = Vtiger_Module::getInstance('Events');
			$target_Module = Vtiger_Module::getInstance('Contacts');
			$target_Module->setRelatedList($moduleInstance, 'Events', array('ADD'), 'get_dependents_list');
		}

		$result = $adb->pquery('SELECT * FROM `yetiforce_proc_tc` WHERE type = ?;', ['timeControlWidget']);
		if (!$adb->getRowCount($result)) {
			$adb->pquery('insert  into `yetiforce_proc_tc`(`type`,`param`,`value`) values (?,?,?);', ['timeControlWidget', 'holidays', 'true']);
			$adb->pquery('insert  into `yetiforce_proc_tc`(`type`,`param`,`value`) values (?,?,?);', ['timeControlWidget', 'workingDays', 'true']);
			$adb->pquery('insert  into `yetiforce_proc_tc`(`type`,`param`,`value`) values (?,?,?);', ['timeControlWidget', 'workingTime', 'true']);
		}

		$result = $adb->pquery('SELECT * FROM `vtiger_ws_fieldtype` WHERE fieldtype = ?;', ['taxes']);
		if (!$adb->getRowCount($result)) {
			$key = $this->getMax('vtiger_ws_fieldtype', 'fieldtypeid');
			$adb->pquery('insert  into `vtiger_ws_fieldtype`(`fieldtypeid`,`uitype`,`fieldtype`) values (?,?,?);', [$key, '303', 'taxes']);
		}
		$result = $adb->pquery('SELECT * FROM `vtiger_ws_fieldtype` WHERE fieldtype = ?;', ['inventoryLimit']);
		if (!$adb->getRowCount($result)) {
			$key = $this->getMax('vtiger_ws_fieldtype', 'fieldtypeid');
			$adb->pquery('insert  into `vtiger_ws_fieldtype`(`fieldtypeid`,`uitype`,`fieldtype`) values (?,?,?);', [$key, '304', 'inventoryLimit']);
		}
		$result = $adb->pquery('SELECT * FROM `vtiger_ws_fieldtype` WHERE fieldtype = ?;', ['multiReferenceValue']);
		if (!$adb->getRowCount($result)) {
			$key = $this->getMax('vtiger_ws_fieldtype', 'fieldtypeid');
			$adb->pquery('insert  into `vtiger_ws_fieldtype`(`fieldtypeid`,`uitype`,`fieldtype`) values (?,?,?);', [$key, '305', 'multiReferenceValue']);
		}

		$this->changeActivity();


		$addHandler[] = ['vtiger.entity.link.after', 'modules/Vtiger/handlers/MultiReferenceUpdater.php', 'Vtiger_MultiReferenceUpdater_Handler', '', '1', '[]'];
		$addHandler[] = ['vtiger.entity.unlink.after', 'modules/Vtiger/handlers/MultiReferenceUpdater.php', 'Vtiger_MultiReferenceUpdater_Handler', '', '1', '[]'];
		$this->addHandler($addHandler);
		$this->addCron([['LBL_MULTI_REFERENCE_VALUE', 'cron/MultiReference.service', '900', NULL, NULL, '1', 'com_vtiger_workflow', '15', NULL]]);

		$adb->pquery("UPDATE com_vtiger_workflow_tasktypes SET  modules = CASE "
			. " WHEN tasktypename = 'VTCreateTodoTask' THEN ? "
			. "WHEN tasktypename = 'VTCreateEventTask' THEN ? "
			. "ELSE modules END WHERE tasktypename IN (?,?) ", ['{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Potentials","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', '{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Potentials","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', 'VTCreateTodoTask', 'VTCreateEventTask']);

		$result = $adb->pquery('SELECT * FROM `vtiger_dataaccess_cnd` WHERE fieldname = ?;', ['taskstatus']);
		if ($adb->getRowCount($result)) {
			$adb->pquery('UPDATE `vtiger_dataaccess_cnd` SET `val` = ?, fieldname = ?  WHERE `fieldname` = ?;', ['PLL_PLANNED', 'activitystatus', 'taskstatus']);
		}
		$result = $adb->pquery('SELECT * FROM `vtiger_dataaccess_cnd` WHERE fieldname = ?;', ['eventstatus']);
		if ($adb->getRowCount($result)) {
			$adb->pquery('UPDATE `vtiger_dataaccess_cnd` SET `val` = ?, fieldname = ?  WHERE `fieldname` = ?;', ['PLL_COMPLETED', 'activitystatus', 'eventstatus']);
		}
		$adb->pquery('UPDATE `vtiger_picklist` SET `name` = ? WHERE `name` = ?;', ['activitystatus', 'taskstatus']);

		$this->updateForgotPassword();
		$this->changeOutgoingServerFile();

		$adb->pquery("UPDATE `vtiger_field` SET `quickcreate` = ?, `quickcreatesequence` = ? WHERE `fieldname` = ? AND tabid IN (?,?);", [2, 8, 'shownerid', getTabid('Events'), getTabid('Calendar')]);

		$integrationBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?', ['LBL_OTHER_SETTINGS']);
		$blockid = 0;
		if ($adb->getRowCount($integrationBlock) > 0) {
			$blockid = $adb->query_result($integrationBlock, 0, 'blockid');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_settings_field` WHERE `name` = ? ", ['LBL_SWITCH_USERS']);
		if (!$adb->getRowCount($result) && $blockid) {
			$sequence = (int) $adb->query_result($adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", [$blockid]), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
			VALUES (?,?,?,?,?,?,?)", [$fieldid, $blockid, $sequence, 'LBL_SWITCH_USERS', '', 'LBL_SWITCH_USERS_DESCRIPTION', 'index.php?module=Users&view=SwitchUsers&parent=Settings']);
		}

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
		$Events = [
			['16', '1761', 'followup', 'vtiger_activity', '2', '10', 'followup', 'LBL_FOLLOWUP', '1', '2', '', '100', '25', '39', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', "int(19)", "LBL_EVENT_INFORMATION", [], ['Calendar']],
		];
		$Calendar = [
			['9', '1761', 'followup', 'vtiger_activity', '2', '10', 'followup', 'LBL_FOLLOWUP', '1', '2', '', '100', '25', '19', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', "int(19)", "LBL_TASK_INFORMATION", [], ['Calendar']]
		];
		$PriceBooks = [
			['6', '20', 'smownerid', 'vtiger_crmentity', '1', '53', 'assigned_user_id', 'Assigned To', '1', '0', '', '100', '6', '9', '1', 'V~M', '0', '2', 'BAS', '1', '', '1', '', "int(19)", "LBL_PRICEBOOK_INFORMATION", [], []]
		];
		$Faq = [
			['6', '20', 'smownerid', 'vtiger_crmentity', '1', '53', 'assigned_user_id', 'Assigned To', '1', '0', '', '100', '6', '9', '1', 'V~M', '0', '2', 'BAS', '1', '', '1', '', "int(19)", "LBL_FAQ_INFORMATION", [], []]
		];


		$setToCRM = ['Accounts' => $Accounts, 'Products' => $Products, 'Events' => $Events, 'Calendar' => $Calendar, 'Faq' => $Faq, 'PriceBooks' => $PriceBooks];

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

	public function deleteInheritsharing()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::deleteInheritsharing() method ...");
		$result = $adb->pquery("SELECT fieldid FROM vtiger_field WHERE columnname IN (?);", ['inheritsharing']);
		while ($row = $adb->fetch_array($result)) {
			$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($row['fieldid']);
			try {
				$fieldInstance->delete();
			} catch (Exception $e) {
				$log->debug("ERROR YetiForceUpdate::deleteInheritsharing: code " . $e->getCode() . " message " . $e->getMessage());
			}
		}
		if ($adb->getRowCount($result)) {
			$adb->query('ALTER TABLE `vtiger_crmentity` DROP COLUMN `inheritsharing`;');
		}
		$log->debug("Exiting YetiForceUpdate::deleteInheritsharing() method ...");
	}

	public function move()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::move() method ...");
		$move = [];
		$moveOtherFields = [];
		$assignedTabs = $this->getSmowneridTab();
		$fields = $this->getBlocks();
		foreach ($assignedTabs as $assignedTab) {
			foreach ($fields['showners'] as $showner) {
				if ($assignedTab['tabid'] == $showner['tabid']) {
					$move[] = ['fieldid' => $showner['fieldid'], 'sequence' => (int) $assignedTab['sequence'] + 1, 'block' => $assignedTab['block']];
				}
			}
			if ($fields['others']) {
				$maxSeq = $this->getNextSequence($assignedTab['tabid'], $assignedTab['block']);
				foreach ($fields['others'] as $field) {
					if ($assignedTab['tabid'] == $field['tabid']) {
						$moveOtherFields[] = ['fieldid' => $field['fieldid'], 'sequence' => ++$maxSeq, 'block' => $assignedTab['block']];
					}
				}
			}
		}
		//This will update the fields sequence for the updated blocks
		if ($move) {
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($move);
		}
		if ($moveOtherFields) {
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($moveOtherFields);
		}
		$this->deleteBlocks($fields['blocks']);
		$log->debug("Exiting YetiForceUpdate::move() method ...");
	}

	public function deleteBlocks($blocks)
	{
		global $log;
		$log->debug("Entering YetiForceUpdate::deleteBlocks() method ...");
		foreach ($blocks as $block) {
			$checkIfFieldsExists = Vtiger_Block_Model::checkFieldsExists($block);
			if (!$checkIfFieldsExists && $block) {
				$blockInstance = Vtiger_Block_Model::getInstance((int) $block);
				try {
					$blockInstance->delete(false);
				} catch (Exception $e) {
					$log->debug("ERROR YetiForceUpdate::deleteBlocks: code " . $e->getCode() . " message " . $e->getMessage());
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::deleteBlocks() method ...");
	}

	public function getSmowneridTab()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::getSmowneridTab() method ...");
		$result = $adb->query("SELECT * FROM vtiger_field WHERE columnname = 'smownerid';");
		$log->debug("Exiting YetiForceUpdate::getSmowneridTab() method ...");
		return $adb->getArray($result);
	}

	public function getNextSequence($tabId, $block)
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::getNextSequence() method ...");
		$result = $adb->pquery("SELECT MAX(sequence) AS max_seq FROM vtiger_field WHERE tabid=? AND block=?", [$tabId, $block]);
		$maxseq = 0;
		if ($result && $adb->num_rows($result)) {
			$maxseq = (int) $adb->query_result($result, 0, 'max_seq');
			$maxseq += 1;
		}
		$log->debug("Exiting YetiForceUpdate::getNextSequence() method ...");
		return $maxseq;
	}

	public function getMax($table, $field)
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::getMax() method ...");
		$result = $adb->query("SELECT MAX(" . $field . ") AS max_seq  FROM " . $table . " ;");
		$id = (int) $adb->getSingleValue($result) + 1;
		$log->debug("Exiting YetiForceUpdate::getMax() method ...");
		return $id;
	}

	public function getShowneridTab()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::getShowneridTab() method ...");
		$result = $adb->query("SELECT * FROM vtiger_field WHERE columnname = 'shownerid';");
		$log->debug("Exiting YetiForceUpdate::getShowneridTab() method ...");
		return $adb->getArray($result);
	}

	public function addActionMap()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addTagCloud() method ...");
		$actions = ['TagCloud', 'DetailTransferOwnership', 'ActivityPostponed', 'ActivityCancel', 'ActivityComplete'];
		foreach ($actions as $key => $action) {
			$result = $adb->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=?;', [$action]);
			if ($adb->getRowCount($result)) {
				continue;
			}
			$key = $this->getMax('vtiger_actionmapping', 'actionid');
			$securitycheck = 0;
			if ($action == 'TagCloud') {
				$securitycheck = 1;
			}
			$adb->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?, ?);", [$key, $action, $securitycheck]);

			if (in_array($action, ['TagCloud', 'DetailTransferOwnership'])) {
				$sql = "SELECT tabid, `name`  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND `name` NOT IN ('SMSNotifier','ModComments','PBXManager','Events','Emails','CallHistory','OSSMailView','');";
			} else {
				$sql = "SELECT tabid, `name` FROM `vtiger_tab` WHERE `name` = 'Calendar';";
			}

			$result = $adb->query($sql);
			$rowCount = $adb->getRowCount($result);

			$resultP = $adb->query("SELECT profileid FROM vtiger_profile;");
			$rowCountP = $adb->getRowCount($resultP);
			for ($i = 0; $i < $rowCountP; $i++) {
				$profileId = $adb->query_result_raw($resultP, $i, 'profileid');
				for ($k = 0; $k < $rowCount; $k++) {
					$row = $adb->query_result_rowdata($result, $k);
					$tabid = $row['tabid'];
					$resultC = $adb->pquery("SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;", [$profileId, $tabid, $key]);
					if ($adb->num_rows($resultC) == 0) {
						$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", [$profileId, $tabid, $key, 0]);
					}
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::addTagCloud() method ...");
		return $adb->getArray($result);
	}

	public function getBlocks()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::getBlocks() method ...");
		$blocks = [];
		$showners = [];
		$otherFields = [];
		$result = $adb->query("SELECT blockid,tabid,blocklabel FROM vtiger_blocks WHERE blocklabel = 'LBL_SHARING_INFORMATION';");
		while ($row = $adb->fetch_array($result)) {
			$blocks[] = $row['blockid'];
		}
		if ($blocks) {
			$result = $adb->pquery('SELECT * FROM vtiger_field WHERE block IN (' . generateQuestionMarks($blocks) . ');', $blocks);
			while ($row = $adb->fetch_array($result)) {
				if ($row['columnname'] == 'shownerid') {
					$showners[] = $row;
				} else {
					$otherFields[] = $row;
				}
			}
		}

		$log->debug("Exiting YetiForceUpdate::getBlocks() method ...");
		return ['others' => $otherFields, 'showners' => $showners, 'blocks' => $blocks];
	}

	public function addWorkflowType()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addWorkflowType() method ...");

		$newTaskType = [];
		$newTaskType[] = ['VTUpdateCalendarDates', 'LBL_UPDATE_DATES_CREATED_EVENTS_AUTOMATICALLY', 'VTUpdateCalendarDates', 'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.inc', 'com_vtiger_workflow/taskforms/VTUpdateCalendarDates.tpl', '{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Potentials","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', NULL];

		foreach ($newTaskType as $taskType) {
			$result = $adb->pquery("SELECT `id` FROM `com_vtiger_workflow_tasktypes` WHERE tasktypename = ?;", [$taskType[0]]);
			if (!$adb->getRowCount($result)) {
				$taskTypeId = $adb->getUniqueID("com_vtiger_workflow_tasktypes");
				$adb->pquery("INSERT INTO com_vtiger_workflow_tasktypes (id, tasktypename, label, classname, classpath, templatepath, modules, sourcemodule) values (?,?,?,?,?,?,?,?)", array($taskTypeId, $taskType[0], $taskType[1], $taskType[2], $taskType[3], $taskType[4], $taskType[5], $taskType[6]));
			}
		}
		$log->debug("Exiting YetiForceUpdate::addWorkflowType() method ...");
	}

	public function changeActivity()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::changeActivity() method ...");
		$result = $adb->query("SHOW TABLES LIKE 'vtiger_taskstatus_seq';");
		if ($adb->getRowCount($result)) {
			$adb->query('RENAME TABLE `vtiger_taskstatus_seq` TO `vtiger_activitystatus_seq`;');
		}
		$result = $adb->query("SHOW TABLES LIKE 'vtiger_taskstatus';");
		if ($adb->getRowCount($result)) {
			$adb->query('RENAME TABLE `vtiger_taskstatus` TO `vtiger_activitystatus`;');
			$adb->query('ALTER TABLE `vtiger_activitystatus` CHANGE `taskstatus` `activitystatus` VARCHAR(200) CHARSET utf8 NULL;');
			$adb->query('ALTER TABLE `vtiger_activitystatus` CHANGE `taskstatusid` `activitystatusid` INT(19) NOT NULL AUTO_INCREMENT;');
			$adb->query("UPDATE `vtiger_field` SET `fieldname` = 'activitystatus',displaytype = '10', `typeofdata` = 'V~O', `defaultvalue` ='PLL_PLANNED' WHERE `fieldname` = 'taskstatus';");
			$adb->pquery('DELETE FROM vtiger_field WHERE tabid=? AND columnname = ?', [getTabid('Calendar'), 'eventstatus']);
			$adb->query("UPDATE `vtiger_field` SET `fieldname` = 'activitystatus', columnname = 'status', displaytype = '10', `typeofdata` = 'V~O', `defaultvalue` ='PLL_PLANNED' WHERE `fieldname` = 'eventstatus';");
			$adb->query('ALTER TABLE `vtiger_activity` DROP INDEX `activitytype`,  ADD  INDEX `activitytype` (`activitytype`, `date_start`, `due_date`, `time_start`, `time_end`, `deleted`, `smownerid`);');
			$adb->query('ALTER TABLE `vtiger_activity` DROP INDEX `activity_eventstatus_idx`;');
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_PLANNED' where vtiger_activity.`eventstatus` = 'Not Held' and activitytype NOT IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_COMPLETED' where vtiger_activity.`eventstatus` = 'Held' and activitytype NOT IN ('Task');");
			$adb->query('ALTER TABLE `vtiger_activity` DROP COLUMN `eventstatus`;');

			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_CANCELLED' where vtiger_activity.`status` = 'Cancelled' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_COMPLETED' where vtiger_activity.`status` = 'Completed' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_PLANNED' where vtiger_activity.`status` = 'Not Started' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_IN_REALIZATION' where vtiger_activity.`status` = 'In Progress' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_OVERDUE' where vtiger_activity.`status` = 'Deferred' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_PLANNED' where vtiger_activity.`status` = 'Pending Input' and activitytype IN ('Task');");
			$adb->query("update vtiger_activity set vtiger_activity.`status` = 'PLL_PLANNED' where vtiger_activity.`status` = 'Pending Input' and activitytype IN ('Task');");

			$adb->query("UPDATE vtiger_activitystatus SET  activitystatus = CASE "
				. " WHEN activitystatus = 'Cancelled' THEN 'PLL_CANCELLED' "
				. "WHEN activitystatus = 'Completed' THEN 'PLL_COMPLETED' "
				. "WHEN activitystatus = 'Not Started' THEN 'PLL_PLANNED' "
				. "WHEN activitystatus = 'In Progress' THEN 'PLL_IN_REALIZATION' "
				. "WHEN activitystatus = 'Deferred' THEN 'PLL_OVERDUE' "
				. "WHEN activitystatus = 'Pending Input' THEN 'PLL_POSTPONED' ELSE activitystatus END  ;");
		}
		$adb->query("UPDATE vtiger_activitystatus SET sortorderid = CASE "
			. " WHEN activitystatus = 'PLL_CANCELLED' THEN '4' "
			. "WHEN activitystatus = 'PLL_COMPLETED' THEN '5' "
			. "WHEN activitystatus = 'PLL_PLANNED' THEN '0' "
			. "WHEN activitystatus = 'PLL_IN_REALIZATION' THEN '1' "
			. "WHEN activitystatus = 'PLL_OVERDUE' THEN '2' "
			. "WHEN activitystatus = 'PLL_POSTPONED' THEN '3' ELSE sortorderid END  ;");

		$this->addHandler([['vtiger.entity.beforesave', 'modules/Calendar/handlers/CalendarHandler.php', 'CalendarHandler', '', 1, '[]']]);
		$this->addCron([['Activity state', 'modules/Calendar/cron/ActivityState.php', '1800', NULL, NULL, '1', 'Calendar', 0, '']]);

		$log->debug("Exiting YetiForceUpdate::changeActivity() method ...");
	}

	public function addHandler($addHandler = [])
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addHandler() method ...");
		if ($addHandler) {
			$em = new VTEventsManager($adb);
			foreach ($addHandler as $handler) {
				$result = $adb->pquery("SELECT * FROM `vtiger_eventhandlers` WHERE event_name = ? AND handler_class = ?;", [$handler[0], $handler[2]]);
				if ($adb->getRowCount($result) == 0) {
					$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::addHandler() method ...");
	}

	public function addCron($addCrons = [])
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addCron() method ...");
		if ($addCrons) {
			foreach ($addCrons as $cron) {
				$result = $adb->pquery("SELECT * FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;", [$cron[0], $cron[1]]);
				if ($adb->getRowCount($result) == 0) {
					Vtiger_Cron::register($cron[0], $cron[1], $cron[2], $cron[6], $cron[5], 0, $cron[8]);
					$key = $this->getMax('vtiger_cron_task', 'sequence');
					$adb->pquery('UPDATE `vtiger_cron_task` SET `sequence` = ? WHERE name = ? AND handler_file = ?;', [$key, $cron[0], $cron[1]]);
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::addCron() method ...");
	}

	public function cleanDB()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::cleanDB() method ...");
		$result = $adb->query("SELECT vtiger_def_org_field.tabid,vtiger_def_org_field.fieldid FROM `vtiger_def_org_field` WHERE fieldid NOT IN (SELECT fieldid FROM `vtiger_field`)");
		$num = $adb->getRowCount($result);
		$deleteField = [];
		for ($i = 0; $i < $num; $i++) {
			$deleteField[] = $adb->query_result($result, $i, "fieldid");
		}
		if ($deleteField) {
			$adb->pquery("delete from vtiger_def_org_field where fieldid in (" . generateQuestionMarks($deleteField) . ")", $deleteField);
		}

		$result = $adb->query("SELECT vtiger_profile2field.tabid,vtiger_profile2field.fieldid FROM `vtiger_profile2field` WHERE fieldid NOT IN (SELECT fieldid FROM `vtiger_field`)");
		$num = $adb->getRowCount($result);
		$deleteField = [];
		for ($i = 0; $i < $num; $i++) {
			$deleteField[] = $adb->query_result($result, $i, "fieldid");
		}
		if ($deleteField) {
			$adb->pquery("delete from vtiger_profile2field where fieldid in (" . generateQuestionMarks($deleteField) . ")", $deleteField);
		}
		$log->debug("Exiting YetiForceUpdate::cleanDB() method ...");
	}

	public function verificationOfPowers()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::verificationOfPowers() method ...");
		$resultP = $adb->query('SELECT profileid FROM vtiger_profile;');
		$all = [];
		$profiles = [];
		while ($rowP = $adb->fetch_array($resultP)) {
			$profileid = $rowP['profileid'];
			$resultPU = $adb->pquery('select * from vtiger_profile2utility where profileid=? order by(tabid)', [$profileid]);
			while ($rowPT = $adb->fetch_array($resultPU)) {
				$all[$rowPT['tabid']][$rowPT['activityid']] = $rowPT['permission'];
				$profiles[$profileid][$rowPT['tabid']][$rowPT['activityid']] = $rowPT['permission'];
			}
		}

		foreach ($profiles as $profile => $dataP) {
			foreach ($all as $tabid => $dataT) {
				foreach ($dataT as $activityid => $permission) {
					if (!isset($dataP[$tabid][$activityid])) {
						echo "Profil: $profile | TabID: $tabid | ActionID: $activityid<br/>";
						$adb->insert('vtiger_profile2utility', [
							'profileid' => $profile,
							'tabid' => $tabid,
							'activityid' => $activityid,
							'permission' => $permission,
						]);
					}
				}
			}
		}
		RecalculateSharingRules();
		$log->debug("Exiting YetiForceUpdate::verificationOfPowers() method ...");
	}
}
