<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/ModTracker/ModTracker.php');

class YetiForceUpdate
{

	var $package;
	var $modulenode;
	var $return = true;
	var $filesToDelete = [
		'modules/Import/helpers/FormatValue.php',
		'modules/KnowledgeBase/actions/Save.php',
		'api/webservice/Core/APISessionPOS.php',
		'libraries/SabreDAV/CalDAV/CalendarQueryParser.php',
		'libraries/SabreDAV/CalDAV/CalendarRootNode.php',
		'libraries/SabreDAV/CalDAV/Property/EmailAddressSet.php',
		'libraries/SabreDAV/CalDAV/Property/Invite.php',
		'libraries/SabreDAV/CalDAV/Property/ScheduleCalendarTransp.php',
		'libraries/SabreDAV/CalDAV/Property/SupportedCalendarComponentSet.php',
		'libraries/SabreDAV/CalDAV/Property/SupportedCalendarData.php',
		'libraries/SabreDAV/CalDAV/Property/SupportedCollationSet.php',
		'libraries/SabreDAV/CalDAV/UserCalendars.php',
		'libraries/SabreDAV/CardDAV/AddressBookQueryParser.php',
		'libraries/SabreDAV/CardDAV/Property/SupportedAddressData.php',
		'libraries/SabreDAV/CardDAV/Property/SupportedCollationSet.php',
		'libraries/SabreDAV/DAV/Exception/FileNotFound.php',
		'libraries/SabreDAV/DAV/FSExt/Node.php',
		'libraries/SabreDAV/DAV/Locks/Backend/FS.php',
		'libraries/SabreDAV/DAV/PartialUpdate/IFile.php',
		'libraries/SabreDAV/DAV/Property.php',
		'libraries/SabreDAV/DAV/Property/GetLastModified.php',
		'libraries/SabreDAV/DAV/Property/Href.php',
		'libraries/SabreDAV/DAV/Property/HrefList.php',
		'libraries/SabreDAV/DAV/Property/IHref.php',
		'libraries/SabreDAV/DAV/Property/LockDiscovery.php',
		'libraries/SabreDAV/DAV/Property/ResourceType.php',
		'libraries/SabreDAV/DAV/Property/Response.php',
		'libraries/SabreDAV/DAV/Property/ResponseList.php',
		'libraries/SabreDAV/DAV/Property/SupportedLock.php',
		'libraries/SabreDAV/DAV/Property/SupportedMethodSet.php',
		'libraries/SabreDAV/DAV/Property/SupportedReportSet.php',
		'libraries/SabreDAV/DAV/PropertyInterface.php',
		'libraries/SabreDAV/DAV/URLUtil.php',
		'libraries/SabreDAV/DAV/XMLUtil.php',
		'libraries/SabreDAV/DAVACL/Property/Acl.php',
		'libraries/SabreDAV/DAVACL/Property/AclRestrictions.php',
		'libraries/SabreDAV/DAVACL/Property/CurrentUserPrivilegeSet.php',
		'libraries/SabreDAV/DAVACL/Property/Principal.php',
		'libraries/SabreDAV/DAVACL/Property/SupportedPrivilegeSet.php',
		'libraries/SabreDAV/VObject/RecurrenceIterator.php',
		'layouts/basic/modules/Vendors/resources/Detail.js',
		'layouts/basic/modules/Vtiger/data_access/check_day_tasks.tpl',
		'modules/Settings/CronTasks/EditCron.php',
		'modules/Settings/CronTasks/ListCronJobs.php',
		'include/utils/export.php',
		'modules/Calendar/iCalExport.php',
		'modules/Calendar/iCalImport.php',
		'modules/ModComments/ModCommentsWidgetHandler.php',
		'modules/OSSTimeControl/Save.php',
		'modules/Reports/AdvancedFilter.php',
		'modules/Reports/CustomReportUtils.php_deprecated',
		'modules/Reports/ReportChartRun.php_deprecated',
		'modules/Reports/ReportSharing.php',
		'modules/Reports/ReportType.php',
		'modules/Users/Authenticate.php',
		'libraries/SabreDAV/CalDAV/Notifications/Notification/Invite.php',
		'libraries/SabreDAV/CalDAV/Notifications/Notification/InviteReply.php',
		'libraries/SabreDAV/CalDAV/Notifications/Notification/SystemStatus.php',
		'libraries/SabreDAV/CalDAV/Notifications/INotificationType.php',
		'libraries/SabreDAV/CalDAV/Property/AllowedSharingModes.php',
		'libraries/SabreDAV/CardDAV/UserAddressBooks.php',
		'libraries/SabreDAV/VObject/Property/Float.php',
		'libraries/SabreDAV/VObject/Property/Integer.php',
		'modules/Vtiger/data_access/check_day_tasks.php',
	];

	function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	function preupdate()
	{
		return true;
	}

	function update()
	{
		$this->setTablesScheme($this->getTablesAction(1));
		$this->setAlterTables($this->getAlterTables(1));
		$this->setFields($this->getFields(1));
		$this->setPicklists($this->getPicklists(1));
		$this->actionMapp($this->getActionMapp(1));
		$this->updatePack();
//		....
		$this->mappedFields();
		$this->updateConfigFile();
		$this->roundcubeConfigFile();
	}

	function postupdate()
	{
		return true;
	}

	public function updatePack()
	{
		$db = PearDatabase::getInstance();
		$data = [
			['1', 'LBL_SYNCHRONIZE_PRODUCTS', 'GetProducts'],
			['2', 'LBL_SYNCHRONIZE_ORDERS', 'SetSSingleOrders'],
			['3', 'LBL_SYNCHRONIZE_PRODUCTS_IMAGES', 'GetImage'],
			['4', 'LBL_SYNCHRONIZE_STORAGES', 'GetIStorages'],
			['5', 'LBL_CANCEL_ORDERS', 'CancelSSingleOrders'],
			['6', 'LBL_SYNCHRONIZE_TREES', 'GetTree']
		];
		$db->delete('w_yf_pos_actions', 'label IN (?,?) AND (`name` IS NULL OR `name` = ?)', ['LBL_SYNCHRONIZE_PRODUCTS', 'LBL_SYNCHRONIZE_ORDERS', '']);
		$result = $db->query("SELECT `name` FROM `w_yf_pos_actions`;");
		$resultData = $db->getArrayColumn($result, 'name');
		foreach ($data as $row) {
			if (!in_array($row[2], $resultData)) {
				$db->insert('w_yf_pos_actions', [
					'label' => $row[1],
					'name' => $row[2]
				]);
			}
		}
		$db->update('vtiger_ssingleorders_source', ['presence' => 0], 'ssingleorders_source IN (?,?,?)', ['PLL_MANUAL', 'PLL_POS', 'PLL_SHOP']);
		$db->update('vtiger_blocks', ['blocklabel' => 'LBL_SYNCHRONIZE_POS'], 'tabid = ? AND blocklabel = ?', [getTabid('SSingleOrders'), 'LBL_POS']);

		$langData = ['6', 'French', 'fr_fr', 'French', '2016-04-29 12:20:00', NULL, '0', '1'];
		$result = $db->pquery('SELECT 1 FROM vtiger_language WHERE `name` = ? LIMIT 1;', [$langData[1]]);
		if (!$db->getRowCount($result)) {
			$db->insert('vtiger_language', [
				'id' => $db->getUniqueID('vtiger_language'),
				'name' => $langData[1],
				'prefix' => $langData[2],
				'label' => $langData[3]
			]);
		}
		$notifDataAll = [['0', 'LBL_MESSAGES_FROM_USERS', '0', '3', '3', 'glyphicon glyphicon-', '0'], ['1', 'LBL_WATCHDOG', '0', '3', '3', 'glyphicon glyphicon-', '0']];
		foreach ($notifDataAll as $notifData) {
			$result = $db->pquery('SELECT 1 FROM `a_yf_notification_type` WHERE `name` = ?;', [$notifData[1]]);
			if (!$result->rowCount()) {
				$db->insert('a_yf_notification_type', [
					'id' => $db->getUniqueID('a_yf_notification_type'),
					'name' => $notifData[1],
					'role' => $notifData[2],
					'width' => $notifData[3],
					'height' => $notifData[4],
					'icon' => $notifData[5],
					'presence' => $notifData[6]
				]);
			} else {
				$db->update('a_yf_notification_type', [
					'role' => $notifData[2],
					'width' => $notifData[3],
					'height' => $notifData[4],
					'icon' => $notifData[5],
					'presence' => $notifData[6]
					], '`name` = ?', [$notifData[1]]);
			}
		}
		$result = $db->pquery('SELECT ossmailtemplatesid FROM `vtiger_ossmailtemplates` WHERE `sysname` = ?;', ['TestMailAboutTheMailServerConfiguration']);
		if ($result->rowCount()) {
			$ID = $db->getSingleValue($result);
			$record = Vtiger_Record_Model::getInstanceById($ID, 'OSSMailTemplates');
			$record->set('content', '<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Dear </span>#a#478#aEnd#&nbsp;#a#479#aEnd#<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">,&nbsp;</span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<b style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">This is a test mail sent to confirm if a mail is actually being sent through the smtp server that you have configured.&nbsp;</b><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Feel free to delete this mail.&nbsp;<br />
CRM&nbsp;</span>address:&nbsp;#s#LinkToCRMRecord#sEnd#<br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Thanks and Regards,</span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" />
<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Team YetiForce</span>');
			$record->save();
		}
		$db->update('vtiger_field', ['summaryfield' => 1], '`tabid` = ? AND columnname IN (?,?,?,?);', [getTabid('ServiceContracts'), 'end_date', 'due_date', 'contract_status', 'contract_type']);
		$db->update('vtiger_field', ['summaryfield' => 0], '`tabid` = ? AND columnname = ?;', [getTabid('ServiceContracts'), 'contract_no']);
	}

	public function mappedFields()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$rootDir = vglobal('root_directory');
		$dirName = 'cache/updates/MappedFields';
		if (is_dir($dirName)) {
			foreach (new DirectoryIterator($dirName) as $file) {
				if (!$file->isDot()) {
					if (strpos($file->getFilename(), '.xml') !== false) {
						$moduleInstance = Settings_MappedFields_Module_Model::getCleanInstance();
						$moduleInstance->importDataFromXML($file->getPathname());
					}
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getPicklists($index)
	{
		$picklists = [];
		switch ($index) {
			case 1:
				$picklists['HelpDesk'] = [
					['name' => 'ticketstatus', 'uitype' => '15', 'add_values' => ['PLL_SUBMITTED_COMMENTS', 'PLL_FOR_APPROVAL', 'PLL_TO_CLOSE'], 'remove_values' => []]
				];
				break;
			default:
				break;
		}
		return $picklists;
	}

	function setPicklists($picklistData)
	{
		$db = PearDatabase::getInstance();
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		$rolesSelected = [];
		foreach ($roleRecordList as $roleRecord) {
			$rolesSelected[] = $roleRecord->getId();
		}
		foreach ($picklistData as $moduleName => $picklists) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if (!$moduleModel)
				continue;
			foreach ($picklists as $piscklist) {
				$fieldModel = Settings_Picklist_Field_Model::getInstance($piscklist['name'], $moduleModel);
				if (!$fieldModel)
					continue;
				$pickListValues = Vtiger_Util_Helper::getPickListValues($piscklist['name']);
				foreach ($piscklist['add_values'] as $key => $newValue) {
					if (!in_array($newValue, $pickListValues)) {
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
						if (is_string($key)) {
							$db->update('vtiger_' . $piscklist['name'], ['presence' => 0], '`' . $piscklist['name'] . '` = ?', [$newValue]);
						}
					}
				}
				foreach ($piscklist['remove_values'] as $newValue) {
					if (!in_array($newValue, $pickListValues))
						continue;
					if ($piscklist['uitype'] != '16') {
						$deletePicklistValueId = self::getPicklistId($piscklist['name'], $newValue);
						if ($deletePicklistValueId)
							$db->pquery("DELETE FROM `vtiger_role2picklist` WHERE picklistvalueid = ? ", [$deletePicklistValueId]);
					}
					$db->pquery("DELETE FROM `vtiger_" . $piscklist['name'] . "` WHERE " . $piscklist['name'] . " = ? ", [$newValue]);
				}
			}
		}
	}

	static function getPicklistId($fieldName, $value)
	{
		$db = PearDatabase::getInstance();
		if (Vtiger_Utils::CheckTable('vtiger_' . $fieldName)) {
			$sql = 'SELECT picklist_valueid FROM vtiger_' . $fieldName . ' WHERE ' . $fieldName . ' = ? ;';
			$result = $db->pquery($sql, [$value]);
			if ($db->getRowCount($result) > 0) {
				return $db->getSingleValue($result);
			}
		}
		return false;
	}

	function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$actions = [
//					['type'=>'add', 'name'=>'', 'tabsData'=>[], 'sql'=>''],
					['type' => 'add', 'name' => 'RemoveRelation', 'sql' => ''],
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	public function actionMapp($actions)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		foreach ($actions as $action) {
			$result = $db->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=? LIMIT 1;', [$action['name']]);
			if (!$db->getRowCount($result)) {
				$securitycheck = 0;
				$key = $this->getMax('vtiger_actionmapping', 'actionid');
				$db->insert('vtiger_actionmapping', ['actionid' => $key, 'actionname' => $action['name'], 'securitycheck' => $securitycheck]);
			} else {
				$key = $db->getSingleValue($result);
			}
			$permission = 0;
			if (!empty($action['tabsData'])) {
				$tabsData = $action['tabsData'];
			} elseif (!empty($action['sql'])) {
				$result = $db->query($action['sql']);
				$tabsData = $db->getArrayColumn($result, 'tabid');
			} else {
				$result = $db->query("SELECT tabid FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name NOT IN ('SMSNotifier','ModComments','PBXManager','Events','Emails','');");
				$tabsData = $db->getArrayColumn($result, 'tabid');
			}
			$resultP = $db->query('SELECT profileid FROM vtiger_profile;');
			while ($profileId = $db->getSingleValue($resultP)) {
				foreach ($tabsData as $tabid) {
					$resultC = $db->pquery('SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;', [$profileId, $tabid, $key]);
					if ($db->getRowCount($resultC) == 0) {
						$db->pquery('INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)', [$profileId, $tabid, $key, $permission]);
					}
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['90', '2344', 'ssingleorders_source', 'u_yf_ssingleorders', '2', '16', 'ssingleorders_source', 'FL_SOURCE', '1', '2', '', '100', '13', '284', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "varchar(255) DEFAULT '' ", 'LBL_SSINGLEORDERS_INFORMATION', ['PLL_MANUAL', 'PLL_POS', 'PLL_SHOP'], [], 'SSingleOrders'],
					['100', '2345', 'ssingleordersid', 'u_yf_igdn', '1', '10', 'ssingleordersid', 'FL_SSIGNLEORDERS', '1', '2', '', '100', '8', '327', '1', 'M~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "int(19)", 'LBL_BASIC_DETAILS', [], ['SSingleOrders'], 'IGDN'],
					['6', '2346', 'last_invoice_date', 'vtiger_account', '1', '5', 'last_invoice_date', 'FL_LAST_INVOICE_DATE', '1', '2', '', '100', '10', '198', '10', 'D~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "date", 'LBL_FINANSIAL_SUMMARY', [], [], 'Accounts'],
					['13', '2347', 'report_time', 'vtiger_troubletickets', '2', '308', 'report_time', 'FL_REPORT_TIME', '1', '2', '', '100', '11', '27', '2', 'I~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'int(10)', 'LBL_CUSTOM_INFORMATION', [], [], 'HelpDesk'],
					['13', '2348', 'response_time', 'vtiger_troubletickets', '2', '308', 'response_time', 'FL_RESPONSE_TIME', '1', '2', '', '100', '12', '27', '3', 'DT~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'datetime', 'LBL_CUSTOM_INFORMATION', [], [], 'HelpDesk'],
					['4', '2349', 'inactivity', 'vtiger_contactdetails', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '29', '4', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_CONTACT_INFORMATION', [], [], 'Contacts'],
					['6', '2350', 'inactivity', 'vtiger_account', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '24', '9', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_ACCOUNT_INFORMATION', [], [], 'Accounts'],
					['7', '2351', 'inactivity', 'vtiger_leaddetails', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '24', '13', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_LEAD_INFORMATION', [], [], 'Leads'],
					['92', '2352', 'inactivity', 'u_yf_partners', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '6', '299', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_PARTNERS_INFORMATION', [], [], 'Partners'],
					['18', '2353', 'inactivity', 'vtiger_vendor', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '21', '42', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_VENDOR_INFORMATION', [], [], 'Vendors'],
					['93', '2354', 'inactivity', 'u_yf_competition', '2', '56', 'inactivity', 'FL_INACTIVITY', '1', '2', '', '100', '6', '303', '1', 'C~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'tinyint(1) NULL DEFAULT 0', 'LBL_COMPETITION_INFORMATION', [], [], 'Competition'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function setFields($fields)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (self::checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' addField - ' . print_r($field[2], true));
			$moduleInstance = Vtiger_Module::getInstance($field[28]);
			$blockInstance = Vtiger_Block::getInstance($field[25], $moduleInstance);
			$fieldInstance = new Vtiger_Field();
			$fieldInstance->column = $field[2];
			$fieldInstance->name = $field[6];
			$fieldInstance->label = $field[7];
			$fieldInstance->table = $field[3];
			$fieldInstance->uitype = $field[5];
			$fieldInstance->typeofdata = $field[15];
			$fieldInstance->readonly = $field[8];
			$fieldInstance->displaytype = $field[14];
			$fieldInstance->masseditable = $field[19];
			$fieldInstance->quickcreate = $field[16];
			$fieldInstance->columntype = $field[24];
			$fieldInstance->presence = $field[9];
			$fieldInstance->maximumlength = $field[11];
			$fieldInstance->quicksequence = $field[17];
			$fieldInstance->info_type = $field[18];
			$fieldInstance->helpinfo = $field[20];
			$fieldInstance->summaryfield = $field[21];
			$fieldInstance->generatedtype = $field[4];
			$fieldInstance->defaultvalue = $field[10];
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 ))
				$fieldInstance->setPicklistValues($field[26]);
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => ['add'], 'name' => 'name', 'table' => 'w_yf_pos_actions', 'sql' => 'ALTER TABLE `w_yf_pos_actions` ADD COLUMN `name` varchar(255) NULL;'],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_addressbookchanges', 'sql' => "ALTER TABLE `dav_addressbookchanges` 
	CHANGE `uri` `uri` varbinary(200) NOT NULL after `id` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['', 'exception'], 'sql' => 'ALTER TABLE `dav_addressbooks` DROP FOREIGN KEY `dav_addressbooks_ibfk_1`;'],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_principals', 'sql' => "ALTER TABLE `dav_principals` 
						CHANGE `uri` `uri` varbinary(200)   NOT NULL after `id` , 
						CHANGE `email` `email` varbinary(80)   NULL after `uri` ,
						CHANGE `displayname` `displayname` varchar(80)  COLLATE utf8mb4_general_ci NULL after `email`,
						DROP COLUMN `vcardurl` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_addressbooks', 'sql' => "ALTER TABLE `dav_addressbooks` 
						CHANGE `principaluri` `principaluri` VARBINARY(255) NULL,
						CHANGE `uri` `uri` VARBINARY(200) NULL,
						CHANGE `displayname` `displayname` varchar(255)  COLLATE utf8mb4_general_ci NULL after `principaluri` , 
						CHANGE `description` `description` text  COLLATE utf8mb4_general_ci NULL after `uri`,
						DROP INDEX `principaluri_2`,
						ADD  INDEX `dav_addressbooks_ibfk_1` (`principaluri`),
						CHARSET=utf8mb4, COLLATE=utf8mb4_general_ci;"],
					['type' => ['', 'exception'], 'sql' => 'ALTER TABLE `dav_addressbooks` ADD CONSTRAINT `dav_addressbooks_ibfk_1` FOREIGN KEY (`principaluri`) REFERENCES `dav_principals` (`uri`) ON DELETE CASCADE ;'],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_calendarchanges', 'sql' => "ALTER TABLE `dav_calendarchanges` 
	CHANGE `uri` `uri` varbinary(200)   NOT NULL after `id` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'uid', 'validType' => 'varbinary', 'table' => 'dav_calendarobjects', 'sql' => "ALTER TABLE `dav_calendarobjects` 
	CHANGE `uid` `uid` varbinary(200)   NULL after `lastoccurence` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'components', 'validType' => 'varbinary(21)', 'table' => 'dav_calendars', 'sql' => "ALTER TABLE `dav_calendars` 
						CHANGE `components` `components` varbinary(21)   NULL after `timezone` ,
						CHANGE `displayname` `displayname` varchar(100)  COLLATE utf8mb4_general_ci NULL after `principaluri` , 
						CHANGE `description` `description` text  COLLATE utf8mb4_general_ci NULL after `synctoken` , 
						CHANGE `timezone` `timezone` text  COLLATE utf8mb4_general_ci NULL after `calendarcolor` , 
						DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_calendarsubscriptions', 'sql' => "ALTER TABLE `dav_calendarsubscriptions` 
						CHANGE `uri` `uri` varbinary(200)   NOT NULL after `id` , 
						CHANGE `principaluri` `principaluri` varbinary(100)   NOT NULL after `uri` , 
						CHANGE `calendarcolor` `calendarcolor` varbinary(10)   NULL after `calendarorder` ,
						CHANGE `source` `source` text  COLLATE utf8mb4_general_ci NULL after `principaluri` , 
						CHANGE `displayname` `displayname` varchar(100)  COLLATE utf8mb4_general_ci NULL after `source` , 
						CHANGE `refreshrate` `refreshrate` varchar(10)  COLLATE utf8mb4_general_ci NULL after `displayname` ,
						DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_cards', 'sql' => "ALTER TABLE `dav_cards` 
	CHANGE `uri` `uri` varbinary(200) NULL after `carddata` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['changeTable', 'Collation'], 'validType' => 'utf8mb4', 'table' => 'dav_groupmembers', 'sql' => "ALTER TABLE `dav_groupmembers` DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'uri', 'validType' => 'varbinary', 'table' => 'dav_schedulingobjects', 'sql' => "ALTER TABLE `dav_schedulingobjects` 
						CHANGE `principaluri` `principaluri` varbinary(255)   NULL after `id` , 
						CHANGE `uri` `uri` varbinary(200)   NULL after `calendardata` , 
						CHANGE `etag` `etag` varbinary(32)   NULL after `lastmodified` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['change', 'Type'], 'name' => 'username', 'validType' => 'varbinary', 'table' => 'dav_users', 'sql' => "ALTER TABLE `dav_users` 
						CHANGE `username` `username` varbinary(50)   NULL after `id` , 
						CHANGE `digesta1` `digesta1` varbinary(32)   NULL after `username` , 
						CHANGE `key` `key` varchar(50)  COLLATE utf8mb4_general_ci NULL after `userid` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_general_ci' ;"],
					['type' => ['add'], 'name' => 'accounts_id', 'table' => 'w_yf_servers', 'sql' => 'ALTER TABLE `w_yf_servers` ADD COLUMN `accounts_id` int(11)   NULL after `type` ;'],
					['type' => ['change', 'Type'], 'name' => 'isdefault', 'validType' => 'tinyint', 'table' => 'vtiger_language', 'sql' => 'ALTER TABLE `vtiger_language` 
						CHANGE `name` `name` varchar(50)  NOT NULL after `id` , 
						CHANGE `prefix` `prefix` varchar(10) NOT NULL after `name` , 
						CHANGE `label` `label` varchar(30) NOT NULL after `prefix` , 
						CHANGE `isdefault` `isdefault` tinyint(1)   NOT NULL DEFAULT 0 after `sequence` , 
						CHANGE `active` `active` tinyint(1)   NOT NULL DEFAULT 1 after `isdefault` , 
						ADD KEY `prefix`(`prefix`) ;'],
					['type' => ['change', 'Type'], 'name' => 'currencyparam', 'validType' => 'varchar(1024)', 'table' => 'u_yf_fcorectinginvoice_inventory', 'sql' => 'ALTER TABLE `u_yf_fcorectinginvoice_inventory` 
	CHANGE `currencyparam` `currencyparam` varchar(1024) NULL after `currency` ;'],
					['type' => ['change', 'Type'], 'name' => 'currencyparam', 'validType' => 'varchar(1024)', 'table' => 'u_yf_finvoice_inventory', 'sql' => 'ALTER TABLE `u_yf_finvoice_inventory` 
	CHANGE `currencyparam` `currencyparam` varchar(1024) NULL after `currency` ;'],
					['type' => ['change', 'Type'], 'name' => 'currencyparam', 'validType' => 'varchar(1024)', 'table' => 'u_yf_finvoiceproforma_inventory', 'sql' => 'ALTER TABLE `u_yf_finvoiceproforma_inventory` 	CHANGE `currencyparam` `currencyparam` varchar(1024) NULL after `currency` ;'],
					['type' => ['change', 'Type'], 'name' => 'currencyparam', 'validType' => 'varchar(1024)', 'table' => 'u_yf_squotes_inventory', 'sql' => 'ALTER TABLE `u_yf_squotes_inventory` 
	CHANGE `currencyparam` `currencyparam` varchar(1024) NULL after `currency`;'],
					['type' => ['change', 'Type'], 'name' => 'currencyparam', 'validType' => 'varchar(1024)', 'table' => 'u_yf_ssingleorders_inventory', 'sql' => 'ALTER TABLE `u_yf_ssingleorders_inventory` CHANGE `currencyparam` `currencyparam` varchar(1024)  COLLATE utf8_general_ci NULL after `currency` ;'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	function setAlterTables($data)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $alter) {
				switch ($alter['type'][1]) {
					case 'Key_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Key_name="' . $alter['name'] . '";';
						break;
					case 'Column_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Column_name="' . $alter['name'] . '";';
						break;
					case 'exception':
						$db->query($alter['sql']);
						continue;
						break;
					default:
						if ($alter['type'][0] == 'changeTable') {
							$checkSql = 'SHOW TABLE STATUS WHERE NAME LIKE "' . $alter['table'] . '";';
						} else {
							$checkSql = 'SHOW COLUMNS FROM `' . $alter['table'] . '` LIKE "' . $alter['name'] . '";';
						}
						break;
				}
				$result = $db->query($checkSql);
				$num = $result->rowCount();
				if (( $num == 0 && $alter['type'][0] == 'add') || ($num > 0 && $alter['type'][0] == 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && in_array($alter['type'][0], ['change', 'changeTable'])) {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					['type' => 'rename', 'name' => 'w_yf_portal_sessions', 'sql' => 'RENAME TABLE w_yf_portal_sessions TO `w_yf_sessions`;'],
					['type' => 'exception', 'name' => 'dav_propertystorage', 'sql' => "CREATE TABLE IF NOT EXISTS `dav_propertystorage`(
						`id` int(10) unsigned NOT NULL  auto_increment , 
						`path` varbinary(1024) NOT NULL  , 
						`name` varbinary(100) NOT NULL  , 
						`valuetype` int(10) unsigned NULL  , 
						`value` mediumblob NULL  , 
						PRIMARY KEY (`id`) , 
						UNIQUE KEY `path_property`(`path`(600),`name`) 
					) ENGINE=InnoDB DEFAULT CHARSET='utf8mb4' COLLATE='utf8mb4_general_ci';"],
					['type' => 'add', 'name' => 'a_yf_relatedlists_inv_fields', 'sql' => '`a_yf_relatedlists_inv_fields` (
						`relation_id` int(19) DEFAULT NULL,
						`fieldname` varchar(30) DEFAULT NULL,
						`sequence` int(10) DEFAULT NULL,
						KEY `relation_id` (`relation_id`)
					  )'],
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	public function setTablesScheme($tables)
	{
		$db = PearDatabase::getInstance();
		foreach ($tables as $table) {
			if (empty($table)) {
				continue;
			}
			switch ($table['type']) {
				case 'add':
					$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE=InnoDB DEFAULT CHARSET="utf8";');
					break;
				case 'remove':
					$db->query('DROP TABLE IF EXISTS ' . $table['sql'] . ';');
					break;
				case 'rename':
					$result = $db->query("SHOW TABLES LIKE '" . $table['name'] . "';");
					if ($result->rowCount()) {
						$db->query($table['sql']);
					}
					break;
				case 'exception':
					$db->query($table['sql']);
					break;
				default:
					break;
			}
		}
	}

	function updateConfigFile()
	{
		$log = vglobal('log');
		$rootDirectory = vglobal('root_directory');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if (!$rootDirectory)
			$rootDirectory = getcwd();
		$config = $rootDirectory . '/config/config.inc.php';
		if (file_exists($config)) {
			if (strpos(file_get_contents($config), 'forceRedirect') === FALSE) {
				$configC = '
$forceRedirect = true;
';
				file_put_contents($config, $configC, FILE_APPEND);
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	function roundcubeConfigFile()
	{
		$log = vglobal('log');
		$rootDirectory = vglobal('root_directory');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if (!$rootDirectory)
			$rootDirectory = getcwd();
		$fileName = $rootDirectory . '/modules/OSSMail/roundcube/config/config.inc.php';
		if (file_exists($fileName)) {
			$config = OSSMail_Record_Model::getViewableData();
			$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...' . print_r($config, true));
			if (empty($config['temp_dir'])) {
				$configContent = file($fileName);
				foreach ($configContent as $key => $line) {
					if (strpos($line, "config['log_dir']") !== FALSE) {
						$configContent[$key] = $configContent[$key] . "\$config['temp_dir'] = RCUBE_INSTALL_PATH . '/../../../cache/mail/';
";
						break;
					}
				}
				$content = implode("", $configContent);
				$file = fopen($fileName, "w+");
				fwrite($file, $content);
				fclose($file);
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function getMax($table, $field, $filter = '')
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
		return $id;
	}

	public static function checkFieldExists($moduleName, $column, $table)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery('SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;', [$column, $table]);
		else {
			if (is_numeric($moduleName)) {
				$tabId = $moduleName;
			} else {
				$tabId = getTabid($moduleName);
			}
			$result = $db->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", [$column, $table, $tabId]);
		}
		if (!$db->getRowCount($result)) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return false;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return true;
	}
}
