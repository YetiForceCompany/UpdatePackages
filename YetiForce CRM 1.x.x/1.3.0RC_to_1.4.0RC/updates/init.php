<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class YetiForceUpdate{
	var $modulenode;
	var $return = true;
	var $dropTablePicklist = array();
	var $filesToDelete = array(
		'modules/OSSCosts/copy',
		'data',
		'README.md',
		'logs/',
		'includes/',
		'test/contact/',
		'test/logo/',
		'test/product/',
		'test/templates_c/',
		'test/user/',
		'test/upload/',
		//'test/vtlib/',
		'test/wordtemplatedownload/',
		'modules/Settings/vtigerCRM.CAB',
		'modules/Utilities/Merge.php',
		'modules/Utilities/UtilitiesAjax.php',
		'modules/Utilities/Currencies.php',
		'layouts/vlayout/modules/Calendar/SideBarWidgets.tpl',
		'libraries/fullcalendar/fullcalendar-bootstrap.css',
		'libraries/fullcalendar/fullcalendar-bootstrap.less',
		'modules/Settings/Vtiger/actions/SaveCompanyField.php',
		'modules/Calculations/EditView.tpl',
		'modules/Calculations/Hierarchy.tpl',
		'modules/Calculations/LineItemsContent.tpl',
		'modules/Calculations/LineItemsDetail.tpl',
		'modules/Calculations/LineItemsEdit.tpl',
		'modules/Calculations/resources/Detail.js',
		'modules/Calculations/resources/Edit.js',
	);
	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {
		$this->recurseCopy('cache/updates/files','');
	}
	
	function update() {
		$this->addModules();
		$this->updateFiles();
		$this->databaseStructureExceptDeletedTables();
		$this->databaseData();
		$this->transferLogo();
		@chmod($root_directory.'/logs/installation.log', 0777);
		@chmod($root_directory.'/logs/migration.log', 0777);
		@chmod($root_directory.'/logs/platform.log', 0777);
		@chmod($root_directory.'/logs/security.log', 0777);
		@chmod($root_directory.'/logs/soap.log', 0777);
		@chmod($root_directory.'/logs/sqltime.log', 0777);
		@chmod($root_directory.'/logs/vtigercrm.log', 0777);
	}
	function postupdate() {
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	public function transferLogo(){
		global $log,$adb,$root_directory;
		$log->debug("Entering YetiForceUpdate::transferLogo() method ...");
		$result = $adb->query( "SELECT `logoname` FROM `vtiger_organizationdetails` ;");
		$num = $adb->num_rows( $result );
		if($num == 1){
			$logoName = $adb->query_result( $result, 0, 'logoname' );
			copy($root_directory.'test/logo/'.$logoName, $root_directory.'/storage/Logo/'.$logoName);
		}
		$log->debug("Exiting YetiForceUpdate::transferLogo() method ...");
	}
	public function databaseStructureExceptDeletedTables(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_portalinfo` LIKE 'crypt_type';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_portalinfo` CHANGE `user_password` `user_password` varchar(200) NULL after `user_name` , 
							ADD COLUMN `crypt_type` varchar(20) NULL after `isactive` ;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_portalinfo` LIKE 'password_sent';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_portalinfo` ADD COLUMN `password_sent` varchar(255) NOT NULL after `crypt_type` ;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_blocks_hide` (
					  `id` int(19) NOT NULL AUTO_INCREMENT,
					  `blockid` int(19) DEFAULT NULL,
					  `conditions` text,
					  `enabled` tinyint(1) DEFAULT NULL,
					  `view` varchar(100) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
					
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_publicholiday` (
					  `publicholidayid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id of public holiday',
					  `holidaydate` date NOT NULL COMMENT 'date of holiday',
					  `holidayname` varchar(255) NOT NULL COMMENT 'name of holiday',
					  PRIMARY KEY (`publicholidayid`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_calendar_config`(
					`type` varchar(10) NULL  , 
					`name` varchar(20) NULL  , 
					`label` varchar(20) NULL  , 
					`value` varchar(100) NULL  
				) ENGINE=InnoDB DEFAULT CHARSET='utf8';");
		
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_trees_templates_data` LIKE 'state';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_trees_templates_data` ADD COLUMN `state` varchar(10) NULL after `label` ; ");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_calculations` LIKE 'currency_id';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_calculations` ADD COLUMN `currency_id` INT(19) UNSIGNED NOT NULL AFTER `date`; ");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_calculations` LIKE 'conversion_rate';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_calculations` ADD COLUMN `conversion_rate` decimal(10,3) unsigned NOT NULL AFTER `currency_id`; ");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_converttoaccount_settings` (
  `state` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_salesprocesses_settings` (
  `id` int(11) NOT NULL,
  `products_rel_potentials` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_bruteforce` LIKE 'active';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_bruteforce` ADD COLUMN `active` tinyint(1) NULL DEFAULT 1 after `timelock` ;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_bruteforce_users`(
					`id` int(19) NOT NULL  , 
					KEY `fk_1_vtiger_bruteforce_users`(`id`) , 
					CONSTRAINT `fk_1_vtiger_bruteforce_users` 
					FOREIGN KEY (`id`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE 
				) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';");
		
		$result = $adb->query("SHOW KEYS FROM `vtiger_module_dashboard_widgets` WHERE Key_name='templateid';");
		if($adb->num_rows($result) == 1){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` DROP KEY `templateid`;");
		}
		$result = $adb->query("SHOW KEYS FROM `vtiger_module_dashboard_widgets` WHERE Key_name='templateid';");
		if($adb->num_rows($result) == 1){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` DROP KEY `templateid`;");
		}
		$result = $adb->query("SHOW KEYS FROM  `vtiger_module_dashboard_widgets` WHERE Key_name='vtiger_module_dashboard_widgets_ibfk_1';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD KEY `vtiger_module_dashboard_widgets_ibfk_1`(`templateid`) ;");
		}
		$log->debug("Exiting YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
	}
	
	
	function settingsReplace() {
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::settingsReplace() method ...");

		//add new record
		$settings_field = array();
		$settings_field[] = array('LBL_STUDIO','LBL_MODTRACKER_SETTINGS',NULL,'LBL_MODTRACKER_SETTINGS_DESCRIPTION','index.php?module=ModTracker&parent=Settings&view=List',16,0,0);
		$settings_field[] = array('LBL_STUDIO','LBL_HIDEBLOCKS',NULL,'LBL_HIDEBLOCKS_DESCRIPTION','index.php?module=HideBlocks&parent=Settings&view=List',17,0,0);
		$settings_field[] = array('LBL_OTHER_SETTINGS','LBL_PUBLIC_HOLIDAY',NULL,'LBL_PUBLIC_HOLIDAY_DESCRIPTION','index.php?module=PublicHoliday&view=Configuration&parent=Settings',25,0,0);
		$settings_field[] = array('LBL_STUDIO','LBL_CALENDAR_CONFIG',NULL,'LBL_CALENDAR_CONFIG_DESCRIPTION','index.php?parent=Settings&module=Calendar&view=UserColors',18,0,0);
		$settings_field[] = array('LBL_PROCESSES','LBL_CONVERSION_TO_ACCOUNT',NULL,'LBL_CONVERSION_TO_ACCOUNT_DESCRIPTION','index.php?module=Leads&parent=Settings&view=ConvertToAccount',2,0,0);
		$settings_field[] = array('LBL_PROCESSES','LBL_SALES_PROCESSES',NULL,'LBL_SALES_PROCESSES_DESCRIPTION','index.php?module=SalesProcesses&view=Configuration&parent=Settings',1,0,0);
		foreach ($settings_field AS $field){
			if(!self::checkFieldExists( $field, 'Settings' )){
				$field[0] = self::getBlockId($field[0]);
				$count = self::countRow('vtiger_settings_field', 'fieldid');
				array_unshift($field, ++$count);
				$adb->pquery('insert into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values (?, ?, ?, ?, ?, ?, ?, ?, ?)', $field);
			}
		}
		$adb->pquery('UPDATE `vtiger_settings_field_seq` SET id = ?;', array(self::countRow('vtiger_settings_field', 'fieldid')));

		$log->debug("Exiting YetiForceUpdate::settingsReplace() method ...");
	}
	public function getBlockId($label){
		global $adb;
		$result = $adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label = ? ;",array($label), true);
		return $adb->query_result($result, 0, 'blockid');
	}
	public function countRow($table, $field){
		global $adb;
		$result = $adb->query("SELECT MAX(".$field.") AS max_seq  FROM ".$table." ;");
		return $adb->query_result($result, 0, 'max_seq');
	}
	public function checkFieldExists($field, $moduleName){
		global $adb;
		if($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", array($field[1],$field[4]));
		else
			$result = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", array($field['name'],$field['table'], getTabid($moduleName)));
		if(!$adb->num_rows($result)) {
			return false;
		}
		return true;
	}
	public function databaseData(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");
		$result = $adb->pquery("SELECT * FROM `vtiger_links` WHERE linklabel = ?", array('LIST_OF_LAST_UPDATED_RECORD'));
		if($adb->num_rows($result) == 0){
			$instanceModule = Vtiger_Module::getInstance('Home');
			$instanceModule->addLink('DASHBOARDWIDGET', 'LIST_OF_LAST_UPDATED_RECORD', 'index.php?module=Home&view=ShowWidget&name=ListUpdatedRecord');
		}
		
		$result = $adb->query("SELECT MAX(blockid) AS id FROM vtiger_settings_blocks");
		$result = $adb->pquery("UPDATE vtiger_settings_blocks_seq SET `id` = ?",array($adb->query_result($result, 0, 'id')));
		$blockid = $adb->getUniqueId("vtiger_settings_blocks");
		$adb->pquery("insert  into `vtiger_settings_blocks`(`blockid`,`label`,`sequence`) values (?,?,?);",array($blockid,'LBL_PROCESSES',9));
		$adb->pquery("UPDATE `vtiger_modentity_num` SET `cur_id` = ? WHERE `semodule` = ? ;", array(1, 'Products'));
		$result = $adb->pquery("SELECT * FROM `vtiger_ossmailtemplates` WHERE name = ? AND oss_module_list = ? ", array('Customer Portal Login Details', 'Contacts'));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_ossmailtemplates` SET `content` = ? WHERE name = ? AND oss_module_list = ?  ;", array('<p>#s#LogoImage#sEnd# </p><p>Dear #a#67#aEnd#  #a#70#aEnd#</p><p>Created for your account in the customer portal, below sending data access.</p><p>Login: #a#80#aEnd#<br />Password: #s#ContactsPortalPass#sEnd#</p><p>Regards</p>', 'Customer Portal Login Details', 'Contacts'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_ossmailtemplates` WHERE name = ? AND oss_module_list = ? ", array('Customer Portal - ForgotPassword', 'Contacts'));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_ossmailtemplates` SET `content` = ? WHERE name = ? AND oss_module_list = ?  ;", array('Dear #a#67#aEnd# #a#70#aEnd#,<br /><br />
You recently requested a reminder of your access data for the YetiForce Portal.<br /><br />
You can login by entering the following data:<br /><br />
Your username: #a#80#aEnd#<br />
Your password: #s#ContactsPortalPass#sEnd#<br /><br /><br />
Regards,<br />
YetiForce CRM Support Team.', 'Customer Portal - ForgotPassword', 'Contacts'));
		}
		$adb->pquery("UPDATE `vtiger_ossmenumanager` SET `label` = ?, `langfield` = ? WHERE `label` = ? ;", array('Teamwork', 'en_us*Teamwork#pl_pl*Praca grupowa', 'Group Card'));
		$adb->pquery("UPDATE `vtiger_osspdf` SET `footer_content` = ? WHERE `title` = ? AND `moduleid` = ?;", array('<title></title>
<table width="537px">
	<tbody>
		<tr>
			<td colspan="6" rowspan="2"><img src="#special_function#siteUrl#end_special_function#storage/Logo/logo_yetiforce.png" style="width: 200px;" width="200" /></td>
			<td colspan="4"><span style="font-size:6px;">#company_organizationname# #company_address# #company_code# #company_city#. VAT:#company_vatid#</span></td>
		</tr>
		<tr>
			<td colspan="5">
			<table border="1">
				<tbody>
					<tr>
						<td>
						<table cellpadding="1">
							<tbody>
								<tr>
									<td style="text-align: center;"><span style="font-size:9px;">Calculation confirmation: <strong>#calculations_no#</strong></span></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td>
						<table cellpadding="1">
							<tbody>
								<tr>
									<td style="text-align: center;"><span style="font-size:9px;">Date: #special_function#CreatedDateTime#end_special_function#</span></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="7">&nbsp;</td>
			<td colspan="5" rowspan="2">
			<table border="1">
				<tbody>
					<tr>
						<td>
						<table cellpadding="5">
							<tbody>
								<tr>
									<td>
									<table cellpadding="0" style="font-size:8px;">
										<tbody>
											<tr>
												<td colspan="2">Issued by:</td>
												<td colspan="3">#Users_first_name# #Users_last_name#</td>
											</tr>
											<tr>
												<td colspan="2">Email:</td>
												<td colspan="3">#Users_email1#</td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td colspan="3">
			<table>
				<tbody>
					<tr>
						<td><span style="font-size:10px;">&nbsp;<span style="font-size:8px;">#Accounts_account_no#</span></span></td>
					</tr>
					<tr>
						<td>
						<table>
							<tbody>
								<tr>
									<td>
									<p><span style="font-size:10px;">#Accounts_accountname#<br />
									<span style="font-size:8px;">#Accounts_addresslevel8b# #Accounts_buildingnumberb# #Accounts_localnumberb#<br />
									#Accounts_addresslevel7b#, #Accounts_addresslevel5b#<br />
									<span style="font-size:10px;">#Accounts_addresslevel1b#</span><br />
									#Accounts_vat_id#<br />
									#Contacts_email#</span></span></p>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td colspan="3">&nbsp;</td>
		</tr>
	</tbody>
</table>
&nbsp;

<table>
	<tbody>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>#special_function#replaceProductTable#end_special_function#</td>
		</tr>
	</tbody>
</table>', 'Calculation PDF', getTabid('Calculations')));
		$this->addWorkflow();
		$this->addRecords();
		$this->settingsReplace();
		
		$result = $adb->pquery("SELECT * FROM `vtiger_payment_duration` WHERE payment_duration = ?", array('payment:+0 days'));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_payment_duration` SET `payment_duration` = ? WHERE `payment_duration` = ? ;", array('payment:+0 day', 'payment:+0 days'));
			$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `payment_duration` = ? WHERE `payment_duration` = ? ;", array('payment:+0 day', 'payment:+0 days'));
		}
		$this->picklists();
		$adb->pquery("UPDATE `vtiger_blocks` SET `display_status` = ? WHERE `tabid` = ? AND `blocklabel` = ? ;", array(0,getTabid('OSSTimeControl'), 'LBL_BLOCK'));
		$adb->query("ALTER TABLE `vtiger_notes` CHANGE `title` `title` varchar(200) NOT NULL after `note_no` ;");
		
		$adb->pquery("UPDATE `vtiger_field` SET `uitype` = ? WHERE `columnname` = ? AND `tablename` = ? ;", array(14,'time_start', 'vtiger_osstimecontrol'));
		$adb->pquery("UPDATE `vtiger_field` SET `uitype` = ? WHERE `columnname` = ? AND `tablename` = ? ;", array(14,'time_end', 'vtiger_osstimecontrol'));
		$adb->pquery('insert  into `vtiger_salesprocesses_settings`(`id`,`products_rel_potentials`) values (1,1);');
		$adb->pquery("UPDATE `vtiger_field` SET `displaytype` = ? WHERE `columnname` = ? AND `tablename` = ? ;", array(1,'product_id', 'vtiger_troubletickets'));
		
		$fieldsToDelete = array(
		'OSSTimeControl'=>array('payment')
		);
		self::deleteFields($fieldsToDelete);
		self::addFields();
		
		$result = $adb->query("SHOW TABLES LIKE 'vtiger_calendar_config';");
		if($adb->num_rows($result) == 1){
			$result = $adb->pquery("SELECT * FROM `vtiger_calendar_config` ;");
			if($adb->num_rows($result) == 0){
				$adb->query("insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','break_time','LBL_BREAK_TIME','#ffd000');");
				$adb->query("insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values ('colors','holiday','LBL_HOLIDAY','#00d4f5');");
			}
		}
		$adb->pquery("UPDATE `vtiger_entityname` SET `searchcolumn` = ? WHERE `modulename` IN (?,?);", array('subject','RequirementCards', 'QuotesEnquires'));
		$adb->pquery("UPDATE `vtiger_entityname` SET `searchcolumn` = ?, fieldname = ?  WHERE `modulename` = ?;", array('holidaysentitlement_year,ossemployeesid','holidaysentitlement_year', 'HolidaysEntitlement'));
		// To Do CRM
		$adb->pquery("UPDATE `vtiger_entityname` SET `searchcolumn` = ? WHERE `modulename` IN (?,?);", array('paymentsname','PaymentsIn', 'PaymentsOut'));
		
		$result1 = $adb->pquery("SELECT fieldid FROM `vtiger_field` WHERE columnname = ? AND tablename = ?", array('parentid','vtiger_contactdetails'));
		$result2 = $adb->pquery("SELECT * FROM `vtiger_fieldmodulerel` WHERE fieldid = ? AND relmodule = ?", array($adb->query_result($result1, 0, 'fieldid'),'Vendors'));
		if($adb->num_rows($result2) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`) values (".$adb->query_result($result1, 0, 'fieldid').",'Contacts','Vendors');");
		}
		$result1 = $adb->pquery("SELECT fieldid FROM `vtiger_field` WHERE columnname = ? AND tablename = ?", array('potentialid','vtiger_quotesenquires'));
		$result2 = $adb->pquery("SELECT * FROM `vtiger_fieldmodulerel` WHERE fieldid = ? AND relmodule = ?", array($adb->query_result($result1, 0, 'fieldid'),'Potentials'));
		if($adb->num_rows($result2) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`) values (".$adb->query_result($result1, 0, 'fieldid').",'QuotesEnquires','Potentials');");
		}
		
		$result = $adb->pquery("SELECT * FROM `vtiger_calendar_config` WHERE type = ? ;", array('reminder'));
		if($adb->num_rows($result) == 0){
			$adb->pquery("insert  into `vtiger_calendar_config`(`type`,`name`,`label`,`value`) values (?,?,?,?);", array('reminder','update_event','LBL_UPDATE_EVENT','0'));
		}
		// related list
		$adb->pquery("UPDATE `vtiger_relatedlists` SET label = ? WHERE related_tabid = ? AND name = ? AND label = ?;", array('Upcoming Activities',getTabid('Calendar'),'get_activities','Activities'));
		
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Accounts'),getTabid('OSSMailView'),'get_related_list','OSSMailView', 7));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(8, getTabid('Accounts'),getTabid('OSSMailView'),'get_related_list','OSSMailView'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `actions` = ?;", array(getTabid('Accounts'),getTabid('Calendar'),'get_history','Activity History', 'add'));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ?, `actions` = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(7,'',getTabid('Accounts'),getTabid('Calendar'),'get_history','Activity History'));
		}
		//
		$adb->pquery("UPDATE `vtiger_relatedlists` SET actions = ? WHERE related_tabid = ? AND name = ? AND label = ?;", array('',getTabid('Calendar'),'get_history','Activity History'));
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Contacts'),getTabid('OSSMailView'),'get_related_list','OSSMailView', 3));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(9, getTabid('Contacts'),getTabid('OSSMailView'),'get_related_list','OSSMailView'));
		}
		//
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Contacts'),getTabid('Calendar'),'get_history','Activity History', 9));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(3, getTabid('Contacts'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Potentials'),getTabid('Contacts'),'get_contacts','Contacts', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(8, getTabid('Potentials'),getTabid('Contacts'),'get_contacts','Contacts'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Potentials'),getTabid('Calendar'),'get_history','Activity History', 8));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('Potentials'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('HelpDesk'),getTabid('Documents'),'get_attachments','Documents', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(4, getTabid('HelpDesk'),getTabid('Documents'),'get_attachments','Documents'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('HelpDesk'),getTabid('Calendar'),'get_history','Activity History', 4));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('HelpDesk'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Quotes'),getTabid('Documents'),'get_attachments','Documents', 3));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(4, getTabid('Quotes'),getTabid('Documents'),'get_attachments','Documents'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Quotes'),getTabid('Calendar'),'get_history','Activity History', 4));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(3, getTabid('Quotes'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('PurchaseOrder'),getTabid('Documents'),'get_attachments','Documents', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(3, getTabid('PurchaseOrder'),getTabid('Documents'),'get_attachments','Documents'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('PurchaseOrder'),getTabid('Calendar'),'get_history','Activity History', 3));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('PurchaseOrder'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('SalesOrder'),getTabid('Documents'),'get_attachments','Documents', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(4, getTabid('SalesOrder'),getTabid('Documents'),'get_attachments','Documents'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('SalesOrder'),getTabid('Calendar'),'get_history','Activity History', 4));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('SalesOrder'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Invoice'),getTabid('Documents'),'get_attachments','Documents', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(3, getTabid('Invoice'),getTabid('Documents'),'get_attachments','Documents'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Invoice'),getTabid('Calendar'),'get_history','Activity History', 3));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('Invoice'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Campaigns'),getTabid('Accounts'),'get_accounts','Accounts', 5));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(6, getTabid('Campaigns'),getTabid('Accounts'),'get_accounts','Accounts'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('ServiceContracts'),getTabid('HelpDesk'),'get_dependents_list','HelpDesk', 2));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(4, getTabid('ServiceContracts'),getTabid('HelpDesk'),'get_dependents_list','HelpDesk'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Project'),getTabid('HelpDesk'),'get_dependents_list','HelpDesk', 6));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(5, getTabid('Project'),getTabid('HelpDesk'),'get_dependents_list','HelpDesk'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Project'),0,'get_gantt_chart','Charts', 5));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(1, getTabid('Project'),0,'get_gantt_chart','Charts'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ? AND `sequence` = ?;", array(getTabid('Project'),getTabid('Calendar'),'get_activities','Upcoming Activities', 1));
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(6, getTabid('Project'),getTabid('Calendar'),'get_activities','Upcoming Activities'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(getTabid('Campaigns'),getTabid('Calendar'),'get_history','Activity History'));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Campaigns');
			$moduleInstance = Vtiger_Module::getInstance('Calendar');
			$targetModule->setRelatedList($moduleInstance, 'Activity History', array(),'get_history');
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(5, getTabid('Campaigns'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(getTabid('Project'),getTabid('Calendar'),'get_history','Activity History'));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Project');
			$moduleInstance = Vtiger_Module::getInstance('Calendar');
			$targetModule->setRelatedList($moduleInstance, 'Activity History', array(),'get_history');
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(7, getTabid('Project'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(getTabid('ServiceContracts'),getTabid('Calendar'),'get_history','Activity History'));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('ServiceContracts');
			$moduleInstance = Vtiger_Module::getInstance('Calendar');
			$targetModule->setRelatedList($moduleInstance, 'Activity History', array(),'get_history');
			$adb->pquery("UPDATE `vtiger_relatedlists` SET sequence = ? WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(2, getTabid('ServiceContracts'),getTabid('Calendar'),'get_history','Activity History'));
		}
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
	public function picklists(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::picklists() method ...");
		
		$addPicklists = array();
		$addPicklists['SalesOrder'][] = array('name'=>'payment_duration','uitype'=>'16','add_values'=>array('payment:+0 day','payment:+1 day','payment:+7 days','payment:+14 days','payment:+21 days','payment:+30 days','payment:+60 days','payment:+90 days','payment:+180 days','payment:+360 days','payment:+1 month','payment:+3 months','payment:+6 months','payment:+1 year','payment:monday next week','payment:friday next week','payment:first day of next month','payment:last day of next month','payment:first day of +3 months','payment:last day of +3 months'),'remove_values'=>array('Net 30 days','Net 45 days','Net 60 days'));
		$addPicklists['SalesOrder'][] = array('name'=>'recurring_frequency','uitype'=>'16','add_values'=>array('+1 day','+7 days','+14 days','+21 days','+30 days','+60 days','+90 days','+180 days','+360 days','+1 month','+3 months','+6 months','+1 year','monday next week','friday next week','first day of next month','last day of next month','first day of +3 months','last day of +3 months'),'remove_values'=>array('Daily','Weekly','Monthly','Quarterly','Yearly'));
		
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		$rolesSelected = array();
		foreach($roleRecordList as $roleRecord) {
			$rolesSelected[] = $roleRecord->getId();
		}
		foreach($addPicklists as $moduleName=>$piscklists){
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if(!$moduleModel)
				continue;
			foreach($piscklists as $piscklist){
				$fieldModel = Settings_Picklist_Field_Model::getInstance($piscklist['name'], $moduleModel);
				if(!$fieldModel)
					continue;
				$pickListValues = Vtiger_Util_Helper::getPickListValues($piscklist['name']);
				foreach($piscklist['add_values'] as $newValue){
					if(!in_array($newValue, $pickListValues)){
						//$moduleModel->addPickListValues($fieldModel, $newValue);
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
					}
				}
				foreach($piscklist['remove_values'] as $newValue){
					if(!in_array($newValue, $pickListValues))
						continue;
					if($piscklist['uitype'] == '15'){
						$deletePicklistValueId = self::getPicklistId($piscklist['name'], $newValue);
						if($deletePicklistValueId)
							$adb->pquery("DELETE FROM `vtiger_role2picklist` WHERE picklistvalueid = ? ", array($deletePicklistValueId));
					}
					$adb->pquery("DELETE FROM `vtiger_".$piscklist['name']."` WHERE ".$piscklist['name']." = ? ", array($newValue));
					if($piscklist['name'] == 'Net 30 days'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `payment_duration` = ? WHERE `payment_duration` = ? ;", array($piscklist['name'], 'payment:+30 days'));
					}if($piscklist['name'] == 'Net 60 days'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `payment_duration` = ? WHERE `payment_duration` = ? ;", array($piscklist['name'], 'payment:+60 days'));
					}if($piscklist['name'] == 'Daily'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `recurring_frequency` = ? WHERE `recurring_frequency` = ? ;", array($piscklist['name'], '+1 day'));
					}if($piscklist['name'] == 'Weekly'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `recurring_frequency` = ? WHERE `recurring_frequency` = ? ;", array($piscklist['name'], '+7 days'));
					}if($piscklist['name'] == 'Monthly'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `recurring_frequency` = ? WHERE `recurring_frequency` = ? ;", array($piscklist['name'], '+1 month'));
					}if($piscklist['name'] == 'Quarterly'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `recurring_frequency` = ? WHERE `recurring_frequency` = ? ;", array($piscklist['name'], '+3 months'));
					}if($piscklist['name'] == 'Yearly'){
						$adb->pquery("UPDATE `vtiger_invoice_recurring_info` SET `recurring_frequency` = ? WHERE `recurring_frequency` = ? ;", array($piscklist['name'], '+1 year'));
					}
					//$moduleModel->remove($piscklist['name'], $deletePicklistId, '', $moduleName); // remove and replace in records
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::picklists() method ...");
	}
	public function deleteFields($fieldsToDelete){
		global $log;
		$log->debug("Entering YetiForceUpdate::deleteFields() method ...");
		require_once('includes/main/WebUI.php');
		$adb = PearDatabase::getInstance();
		foreach($fieldsToDelete AS $fld_module=>$columnnames){
			$moduleId = getTabid($fld_module);
			foreach($columnnames AS $columnname){
				$fieldquery = 'select * from vtiger_field where tabid = ? AND columnname = ?';
				$res = $adb->pquery($fieldquery,array($moduleId,$columnname));
				$id = $adb->query_result($res,0,'fieldid');
				if(empty($id))
					continue;
				$typeofdata = $adb->query_result($res,0,'typeofdata');
				$fieldname = $adb->query_result($res,0,'fieldname');
				$oldfieldlabel = $adb->query_result($res,0,'fieldlabel');
				$tablename = $adb->query_result($res,0,'tablename');
				$uitype = $adb->query_result($res,0,'uitype');
				$colName = $adb->query_result($res,0,'columnname');
				$tablica = $adb->query_result($res,0,'tablename');
				$fieldtype =  explode("~",$typeofdata);

				//Deleting the CustomField from the Custom Field Table
				$query='delete from vtiger_field where fieldid = ? and vtiger_field.presence in (0,2)';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_profile2field table
				$query='delete from vtiger_profile2field where fieldid=?';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_def_org_field table
				$query='delete from vtiger_def_org_field where fieldid=?';
				$adb->pquery($query, array($id));

				$focus = CRMEntity::getInstance($fld_module);

				$deletecolumnname =$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel).":".$fieldtype[0];
				$column_cvstdfilter = 	$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel);
				$select_columnname = $tablename.":".$columnname .":".$fld_module. "_" . str_replace(" ","_",$oldfieldlabel).":".$fieldname.":".$fieldtype[0];
				$reportsummary_column = $tablename.":".$columnname.":".str_replace(" ","_",$oldfieldlabel);

				$dbquery = 'alter table '. $adb->sql_escape_string($tablica).' drop column '. $adb->sql_escape_string($colName);
				$adb->pquery($dbquery, array());

				//To remove customfield entry from vtiger_field table
				$dbquery = 'delete from vtiger_field where columnname= ? and fieldid=? and vtiger_field.presence in (0,2)';
				$adb->pquery($dbquery, array($colName, $id));
				//we have to remove the entries in customview and report related tables which have this field ($colName)
				$adb->pquery("delete from vtiger_cvcolumnlist where columnname = ? ", array($deletecolumnname));
				$adb->pquery("delete from vtiger_cvstdfilter where columnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_cvadvfilter where columnname = ?", array($deletecolumnname));
				$adb->pquery("delete from vtiger_selectcolumn where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_relcriteria where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportsortcol where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportdatefilter where datecolumnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_reportsummary where columnname like ?", array('%'.$reportsummary_column.'%'));
				$adb->pquery("delete from vtiger_fieldmodulerel where fieldid = ?", array($id));

				//Deleting from convert lead mapping vtiger_table- Jaguar
				if($fld_module=="Leads") {
					$deletequery = 'delete from vtiger_convertleadmapping where leadfid=?';
					$adb->pquery($deletequery, array($id));
				}elseif($fld_module=="Accounts" || $fld_module=="Contacts" || $fld_module=="Potentials") {
					$map_del_id = array("Accounts"=>"accountfid","Contacts"=>"contactfid","Potentials"=>"potentialfid");
					$map_del_q = "update vtiger_convertleadmapping set ".$map_del_id[$fld_module]."=0 where ".$map_del_id[$fld_module]."=?";
					$adb->pquery($map_del_q, array($id));
				}

				//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
				if($uitype == 15 || $uitype == 16 ) {
					$deltablequery = 'drop table IF EXISTS vtiger_'.$adb->sql_escape_string($colName);
					$adb->pquery($deltablequery, array());
					$deltablequeryseq = 'drop table IF EXISTS vtiger_'.$adb->sql_escape_string($colName).'_seq';
					$adb->pquery($deltablequeryseq, array());		
					$adb->pquery("delete from  vtiger_picklist_dependency where sourcefield=? or targetfield=?", array($colName,$colName));
					
					$fieldquery = 'select * from vtiger_picklist where name = ?';
					$res = $adb->pquery($fieldquery,array($columnname));
					$picklistid = $adb->query_result($res,0,'picklistid');
					$adb->pquery("delete from vtiger_picklist where name = ?", array($columnname));
					$adb->pquery("delete from vtiger_role2picklist where picklistid = ?", array($picklistid));
				}
			}
			
		}
		$log->debug("Exiting YetiForceUpdate::deleteFields() method ...");
	}
	public function addFields(){
		global $log;
		$log->debug("Entering YetiForceUpdate::addFields() method ...");
		include_once('vtlib/Vtiger/Module.php'); 
		$columnName = array("tabid","id","column","table","generatedtype","uitype","name","label","readonly","presence","defaultvalue","maximumlength","sequence","block","displaytype","typeofdata","quickcreate","quicksequence","info_type","masseditable","helpinfo","summaryfield","fieldparams","columntype","blocklabel","setpicklistvalues","setrelatedmodules");

		$OSSTimeControl = array(
		array('51','1600','timecontrol_type','vtiger_osstimecontrol','1','16','timecontrol_type','Type','1','2','PLL_WORKING_TIME','100','17','128','1','V~M','2','','BAS','1','0','0','',"varchar(255)","LBL_MAIN_INFORMATION",array('PLL_WORKING_TIME','PLL_BREAK_TIME','PLL_HOLIDAY'))
		);
		$Quotes = array(
		array('20','1585','requirementcards_id','vtiger_quotes','1','10','requirementcards_id','RequirementCards','1','2','','100','28','49','1','V~O','1','','BAS','1','0','0','',"int(19)","LBL_QUOTE_INFORMATION",array(),array('RequirementCards'))
		);
		$Calculations = array(
		array(70,1601,'currency_id','vtiger_calculations',1,'117','currency_id','Currency',1,2,'1',100,11,182,3,'I~O',3,NULL,'BAS',1,0,0,'',"int(19)","LBL_INFORMATION",array(),array()),
		array(70,1602,'conversion_rate','vtiger_calculations',1,'1','conversion_rate','Conversion Rate',1,2,'1',100,12,182,3,'N~O',3,NULL,'BAS',1,0,0,'',"decimal(10,3)","LBL_INFORMATION",array(),array())
		);
		
		$Calendar = array(
		array(9,1603,'allday','vtiger_activity',1,'56','allday','All day',1,2,'',100,26,19,1,'C~O',1,NULL,'BAS',1,0,0,'',"tinyint(1)","LBL_TASK_INFORMATION",array(),array())
		);
		$Events = array(
		array(16,1604,'allday','vtiger_activity',1,'56','allday','All day',1,2,'',100,24,39,1,'C~O',1,NULL,'BAS',1,0,0,'',"tinyint(1)","LBL_EVENT_INFORMATION",array(),array())
		);
		$Invoice = array(
		array(23,1629,'payment_balance','vtiger_invoice',1,'7','payment_balance','Payment balance',1,2,'',100,31,67,2,'NN~O',1,NULL,'BAS',1,0,0,'',"decimal(25,8)","LBL_INVOICE_INFORMATION",array(),array())
		);
		$Accounts = array(
		array(6,1630,'payment_balance','vtiger_account',1,'7','payment_balance','Payment balance',1,2,'',100,25,9,2,'NN~O',1,NULL,'BAS',1,0,0,'',"decimal(25,8)","LBL_ACCOUNT_INFORMATION",array(),array())
		);
		$Potentials = array(
		array(2,1631,'payment_balance','vtiger_potential',1,'7','payment_balance','Payment balance',1,2,'',100,19,1,2,'NN~O',1,NULL,'BAS',1,0,0,'',"decimal(25,8)","LBL_OPPORTUNITY_INFORMATION",array(),array())
		);
		
		
		$setToCRM = array('OSSTimeControl'=>$OSSTimeControl,'Calculations'=>$Calculations,'Quotes'=>$Quotes,'Calendar'=>$Calendar,'Events'=>$Events,'Invoice'=>$Invoice,'Accounts'=>$Accounts,'Potentials'=>$Potentials);

		$setToCRMAfter = array();
		foreach($setToCRM as $nameModule=>$module){
			if(!$module)
				continue;
			foreach($module as $key=>$fieldValues){
				for($i=0;$i<count($fieldValues);$i++){
					$setToCRMAfter[$nameModule][$key][$columnName[$i]] = $fieldValues[$i];
				}
			}
		}
		foreach($setToCRMAfter as $moduleName=>$fields){
			foreach($fields as $field){
				if(self::checkFieldExists($field, $moduleName)){
					continue;
				}
					$moduleInstance = Vtiger_Module::getInstance($moduleName);
					$blockInstance = Vtiger_Block::getInstance($field['blocklabel'],$moduleInstance);
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
					$fieldInstance->info_type = $field['info_type'];
					$fieldInstance->helpinfo = $field['helpinfo'];
					$fieldInstance->summaryfield = $field['summaryfield'];
					$fieldInstance->generatedtype = $field['generatedtype'];
					$fieldInstance->defaultvalue = $field['defaultvalue'];
					$blockInstance->addField($fieldInstance);
					if($field['setpicklistvalues'] && ($field['uitype'] == 15 || $field['uitype'] == 16 || $field['uitype'] == 33 ))
						$fieldInstance->setPicklistValues($field['setpicklistvalues']);
					if($field['setrelatedmodules'] && $field['uitype'] == 10){
						$fieldInstance->setRelatedModules($field['setrelatedmodules']);
					}
			}
		}
	}
	public function addWorkflow (){
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addWorkflow() method ...");
		require_once 'modules/com_vtiger_workflow/include.inc';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
		require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		require_once('include/events/include.inc');
		
		// rename workflow
		$workflowRename = array();
		$workflowRename[] = array(34,'Potentials','Proces sprzedażowy - Weryfikacja danych','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Data verification\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(35,'Potentials','Proces sprzedażowy - Wewnętrzna analiza Klienta','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Customer internal analysis\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(36,'Potentials','Proces sprzedażowy - Pierwszy kontakt z Klientem','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"First contact with a customer\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(37,'Potentials','Proces sprzedażowy - Zaawansowana analiza biznesowa','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Advanced business analysis\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(38,'Potentials','Proces sprzedażowy - Przygotowywanie kalkulacji','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Preparation of calculations\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(39,'Potentials','Proces sprzedażowy - Przygotowywanie oferty','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Preparation of offers\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(40,'Potentials','Proces sprzedażowy - Oczekiwanie na decyzje','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Awaiting a decision\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(41,'Potentials','Proces sprzedażowy - Negocjacje','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Negotiations\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(42,'Potentials','Proces sprzedażowy - Zamówienie i umowa','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Order and contract\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(43,'Potentials','Proces sprzedażowy - Weryfikacja dokumentów','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Documentation verification\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(44,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - oczekiwanie na realizacje','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Waiting for processing\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(45,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - realizacja zamówienia/umowy','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Order\\/contract processing\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(46,'Potentials','Proces sprzedażowy - Sprzedaż wygrana - działania posprzedażowe','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Presale activities\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(47,'Leads','Proces marketingowy - Weryfikacja danych','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_REQUIRES_VERIFICATION\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(48,'Leads','Proces marketingowy - Wstępna analiza','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_PRELIMINARY_ANALYSIS_OF\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(49,'Leads','Proces marketingowy - Zaawansowana analiza','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_ADVANCED_ANALYSIS\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowRename[] = array(50,'Leads','Proces marketingowy - Wstępne pozyskanie','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_INITIAL_ACQUISITION\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName = array();
		$workflowNewName[] = array(34,'Potentials','Sales stage - Data verification','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Data verification\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(35,'Potentials','Sales stage - Customer internal analysis','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Customer internal analysis\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(36,'Potentials','Sales stage - First contact with customer','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"First contact with a customer\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(37,'Potentials','Sales stage - Advanced business analysis','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Advanced business analysis\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(38,'Potentials','Sales stage - Preparing calculations','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Preparation of calculations\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(39,'Potentials','Sales stage - Preparing quote','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Preparation of offers\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(40,'Potentials','Sales stage - Awaiting decision','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Awaiting a decision\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(41,'Potentials','Sales stage - Negotiations','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Negotiations\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(42,'Potentials','Sales stage - Order and Contract','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Order and contract\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(43,'Potentials','Sales stage - Verification of documents','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Documentation verification\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(44,'Potentials','Sales stage - Sales winnings - waiting for projects','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Waiting for processing\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(45,'Potentials','Sales stage - Sales Win - performance of the contract / agreement','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Order\\/contract processing\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(46,'Potentials','Sales stage - Sales Win - post sales activities','[{\"fieldname\":\"sales_stage\",\"operation\":\"is\",\"value\":\"Closed Presale activities\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(47,'Leads','Marketing process - Data Verification','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_REQUIRES_VERIFICATION\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(48,'Leads','Marketing process - Preliminary analysis','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_PRELIMINARY_ANALYSIS_OF\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(49,'Leads','Marketing process - Advanced Analysis','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_ADVANCED_ANALYSIS\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflowNewName[] = array(50,'Leads','Marketing process - Initial acquisition','[{\"fieldname\":\"leadstatus\",\"operation\":\"is\",\"value\":\"LBL_INITIAL_ACQUISITION\",\"valuetype\":\"rawtext\",\"joincondition\":\"\",\"groupjoin\":\"and\",\"groupid\":\"0\"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);

		foreach($workflowRename AS $oldName){
			$result = $adb->pquery("SELECT workflow_id,summary FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array($oldName[2],$oldName[1]));
			if($adb->num_rows($result) == 1){
				foreach($workflowNewName AS $newName){
					if($newName[0] == $oldName[0]){
						$adb->pquery("UPDATE `com_vtiger_workflows` SET `summary` = ? WHERE `summary` = ? AND module_name = ? ;", array($newName[2], $oldName[2], $newName[1]));
					}
				}
			}
		}
		//add new entity method
		$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks_entitymethod` WHERE module_name = ? AND method_name =? ", array('Contacts','MarkPasswordSent'));
		if($adb->num_rows($result) == 0){
			$task_entity_method = array();
			$task_entity_method[] = array('Contacts','MarkPasswordSent','modules/Contacts/handlers/ContactsHandler.php','Contacts_markPasswordSent');
			$emm = new VTEntityMethodManager($adb);
			foreach($task_entity_method as $method){
				$emm->addEntityMethod($method[0], $method[1], $method[2], $method[3]);
			}
		}
		
		$workflow = array();
		
		$workflow[] = array(56,'ModComments','New comment added to ticket from portal','[{"fieldname":"(related_to : (HelpDesk) ticket_title)","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"customer","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(57,'ModComments','New comment added to ticket - contact person','[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(related_to : (HelpDesk) contact_id)","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(58,'ModComments','New comment added to ticket - account','[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(related_to : (Accounts) accountname)","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(related_to : (Contacts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		$workflow[] = array(59,'ModComments','New comment added to ticket - contact','[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(related_to : (Contacts) lastname)","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(related_to : (Contacts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]',1,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		
		$workflowTask = array();
		$workflowTask[] = array(124,56,'Send e-mail to user','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"56";s:7:"summary";s:19:"Send e-mail to user";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:3:"105";s:11:"attachments";s:0:"";s:5:"email";s:28:"created_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:124;}');
		$workflowTask[] = array(125,57,'Send e-mail to contact person','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"57";s:7:"summary";s:29:"Send e-mail to contact person";s:6:"active";b:1;s:7:"trigger";N;s:8:"template";s:3:"106";s:11:"attachments";s:0:"";s:5:"email";s:23:"customer=Contacts=email";s:10:"copy_email";s:0:"";s:2:"id";i:125;}');
		$workflowTask[] = array(126,58,'Send e-mail to account','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"58";s:7:"summary";s:22:"Send e-mail to account";s:6:"active";b:1;s:7:"trigger";N;s:8:"template";s:3:"106";s:11:"attachments";s:0:"";s:5:"email";s:26:"related_to=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:126;}');
		$workflowTask[] = array(127,59,'Send e-mail to contact','O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"59";s:7:"summary";s:22:"Send e-mail to contact";s:6:"active";b:1;s:7:"trigger";N;s:8:"template";s:3:"106";s:11:"attachments";s:0:"";s:5:"email";s:25:"related_to=Contacts=email";s:10:"copy_email";s:0:"";s:2:"id";i:127;}');
		
		$workflowManager = new VTWorkflowManager($adb);
		$taskManager = new VTTaskManager($adb);
		foreach($workflow as $record){
			$result = $adb->pquery("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array($record[2],$record[1]));
			if($adb->num_rows($result) > 0){
				continue;
			}
			$newWorkflow = $workflowManager->newWorkFlow($record[1]);
			$newWorkflow->description = $record[2];
			$newWorkflow->test = $record[3];
			$newWorkflow->executionCondition = $record[4];
			$newWorkflow->defaultworkflow = $record[5];
			$newWorkflow->type = $record[6];
			$newWorkflow->filtersavedinnew = $record[7];
			$workflowManager->save($newWorkflow);
			foreach($workflowTask as $indexTask){
				if($indexTask[1] == $record[0]){
					$task = $taskManager->unserializeTask($indexTask[3]);
					$task->id = '';
					$task->workflowId = $newWorkflow->id;
					$taskManager->saveTask($task);
				}
			}	
		}
                $result = $adb->pquery("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array('Send Customer Login Details','Contacts'));
                if($adb->num_rows($result) == 1){
                    $workflow_id = $adb->query_result_raw($result, 0, 'workflow_id');
                    $workflowTaskAdd = array(128,53,'Mark portal users password as sent.','O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:35:"Mark portal users password as sent.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:16:"MarkPasswordSent";s:2:"id";i:128;}');
                    $result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks` WHERE summary = ? AND workflow_id =? ", array($workflowTaskAdd[2],$workflow_id));
                    if($adb->num_rows($result) == 0){
                        $taskManager = new VTTaskManager($adb);
                        $task = $taskManager->unserializeTask($workflowTaskAdd[3]);
                        $task->id = '';
                        $task->workflowId = $workflow_id;
                        $taskManager->saveTask($task);
                    }
                }
		$log->debug("Exiting YetiForceUpdate::addWorkflow() method ...");
	}
	public function addRecords(){
		global $log,$adb,$current_user;
		$log->debug("Entering YetiForceUpdate::addRecords() method ...");
		$assigned_user_id = $current_user->id;
		$moduleName = 'OSSMailTemplates';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$records = array();
		$records[] = array('New comment added to ticket from portal','ModComments','New comment added to ticket from portal','Dear User,<br />
A new comment has been added to the ticket.<br />
#b#597#bEnd# #a#597#aEnd#<br /><br />
 ');
		$records[] = array('New comment added to ticket','ModComments','New comment added to ticket','<span class="value">Dear User,<br />
A new comment has been added to the ticket.<br />
#b#597#bEnd# #a#597#aEnd#</span>');
		$records[] = array('Security risk has been detected - Brute Force','Contacts','Security risk has been detected','<span class="value">Dear user,<br />
Failed login attempts have been detected. </span>');

		foreach($records as $record){
			$result = $adb->query("SELECT * FROM `vtiger_ossmailtemplates` WHERE `name` = '".$record[0]."'");
			if($adb->num_rows($result) == 0){
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $assigned_user_id;
				$instance->column_fields['name'] = $record[0];
				$instance->column_fields['oss_module_list'] = $record[1];
				$instance->column_fields['subject'] = $record[2];
				$instance->column_fields['content'] = $record[3];
				$save = $instance->save($moduleName);
			}
		}
		$log->debug("Exiting YetiForceUpdate::addRecords() method ...");
	}
	public function addModules(){
		$modules = array('QuotesEnquires','RequirementCards','HolidaysEntitlement','PaymentsIn','PaymentsOut');
		foreach($modules as $module){
			try {
				if(file_exists('cache/updates/'.$module.'.xml') && !Vtiger_Module::getInstance($module)){
					$importInstance = new Vtiger_PackageImport();
					$importInstance->_modulexml = simplexml_load_file('cache/updates/'.$module.'.xml');
					$importInstance->import_Module();
					self::addModuleToMenu($module, (string)$importInstance->_modulexml->parent);
				}
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
	}
	public function addModuleToMenu($moduleName, $parent){
		global $log;
		$log->debug("Entering YetiForceUpdate::addModuleToMenu($moduleName, $parent) method ...");
		$adb = PearDatabase::getInstance();
		if(!$parent)
			return false;
		
		$sql = "SELECT `profileid` FROM `vtiger_profile` WHERE 1;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[] = $adb->query_result( $result, $i, 'profileid' );
        }
        
        $profilePermissions = implode( ' |##| ', $profiles );
		$profilePermissions = ' ' . $profilePermissions . ' ';
		
		//$blocksModule = array('My Home Page','Companies','Human resources','Sales','Projects','Support','Databases');
		$sql = "SELECT `id` FROM `vtiger_ossmenumanager` WHERE label = ? AND tabid = ? AND parent_id = ?;";
		$result = $adb->pquery( $sql, array($parent, 0, 0), true );
		$num = $adb->num_rows( $result );
		if($num == 0){
			$subParams = array(
				'name'         => $parent,
				'visible'       => '1',
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => ''
				
			);
			$id = OSSMenuManager_Record_Model::addBlock( $subParams ); 
			if($id){
				$subParams = array(
				'parent_id'     => $id,
				'tabid'         => getTabid($moduleName),
				'label'         => $moduleName,
				'sequence'      => -1,
				'visible'       => '1',
				'type'          => 0,
				'url'           => '',
				'new_window'    => 0,
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => ''
				
				);
				$id = OSSMenuManager_Record_Model::addMenu( $subParams ); 
			}
		}
		elseif($num == 1){
			$subParams = array(
				'parent_id'     => $adb->query_result( $result, 0, 'id' ),
				'tabid'         => getTabid($moduleName),
				'label'         => $moduleName,
				'sequence'      => -1,
				'visible'       => '1',
				'type'          => 0,
				'url'           => '',
				'new_window'    => 0,
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => ''
				
			);
			$id = OSSMenuManager_Record_Model::addMenu( $subParams ); 
		}
		$log->debug("Exiting YetiForceUpdate::addModuleToMenu() method ...");
	}
	function updateFiles() {
		$config = '
	
//Update the current session id with a newly generated one after login
$session_regenerate_id = false;';
		file_put_contents( 'config/config.inc.php', $config, FILE_APPEND );
	}
	
	function recurseCopy($src,$dst) {
		global $root_directory;
		if(!file_exists( $src ) )
			return;
		$dir = opendir($src); 
		@mkdir($root_directory.$dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					$this->recurseCopy($src . '/' . $file,$dst . '/' . $file); 
				} else {
					copy($root_directory.$src . '/' . $file,$root_directory.$dst . '/' . $file);
					unlink($root_directory.$src . '/' . $file);
				}
			} 
		} 
		closedir($dir); 
		rmdir($src);
	}
}