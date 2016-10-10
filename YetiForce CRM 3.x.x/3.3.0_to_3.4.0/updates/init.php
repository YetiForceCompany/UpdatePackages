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

	public $package;
	public $modulenode;
	public $return = true;
	private $cron = [];
	public $filesToDelete = [
		'include/database/Postgres8.php',
		'include/Debuger.php',
		'languages/api/yetiportal.php',
		'include/Debuger.php',
		'languages/api/yetiportal.php',
		'libraries/Psr',
		'libraries/symfony',
		'vendor/php-debugbar/src/DebugBar/StandardDebugBar.php',
		'.travis.yml',
		'include/ChartUtils.php',
		'include/InventoryPDFController.php',
		'include/utils/EditViewUtils.php',
		'include/utils/ExportRecords.php',
		'include/utils/GraphUtils.php',
		'layouts/basic/modules/Home/NotificationConfig.tpl',
		'layouts/basic/modules/Home/NotificationsListPostProcess.tpl',
		'layouts/basic/modules/Home/NotificationsListPreProcess.tpl',
		'layouts/basic/modules/Home/NotificationsListView.tpl',
		'layouts/basic/modules/Home/resources/NotificationsList.js',
		'layouts/basic/modules/Home/resources/NotificationsList.min.js',
		'layouts/basic/modules/Settings/ModuleManager/IndexContents.tpl',
		'modules/Home/models/NoticeEntries.php',
		'modules/Home/models/Notification.php',
		'modules/Settings/ModuleManager/views/Index.php',
		'vtlib/Vtiger/Version.php',
		'layouts/basic/modules/Settings/ModuleManager/IndexContents.tpl',
		'modules/Settings/ModuleManager/views/Index.php',
		'vtlib/Vtiger/Version.php',
		'admin',
		'config/log4php.properties',
		'include/ComboStrings.php',
		'include/FormValidationUtil.php',
		'include/GlobalPrivileges.php',
		'include/logging.php',
		'include/PrivilegeFile.php',
		'include/Privileges.php',
		'include/PrivilegesUtils.php',
		'config/log4php.properties',
		'include/ComboStrings.php',
		'include/FormValidationUtil.php',
		'include/GlobalPrivileges.php',
		'include/logging.php',
		'include/PrivilegeFile.php',
		'include/Privileges.php',
		'include/PrivilegesUtils.php',
		'layouts/basic/modules/Home/CreateNotificationModal.tpl',
		'layouts/basic/modules/Home/NotificationsItem.tpl',
		'layouts/basic/modules/Vtiger/dashboards/Notifications.tpl',
		'layouts/basic/modules/Vtiger/dashboards/NotificationsContents.tpl',
		'libraries/php-debugbar',
		'libraries/PHPMarkdown',
		'libraries/log4php',
		'libraries/log4php.debug',
		'libraries/PEAR',
		'modules/Home/actions/Notification.php',
		'modules/Home/cron/Notifications.php',
		'modules/Home/views/CreateNotificationModal.php',
		'modules/Home/views/NotificationConfig.php',
		'modules/Home/views/NotificationsList.php',
		'modules/Vtiger/dashboards/Notifications.php',
		'vtlib/thirdparty/parser/feed/simplepie.inc',
		'vtlib/Vtiger/Feed/Parser.php',
	];

	public function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	public function preupdate()
	{
		return true;
	}

	public function update()
	{
		$this->setTablesScheme($this->getTablesAction(1));
		$this->setAlterTables($this->getAlterTables(1));
		$this->updatePack();
		$this->addModule('Notification');
		$this->cron($this->getCronData(1));
		$this->updateConfigurationFiles();
		$this->updateSettingMenu();
	}

	public function postupdate()
	{
		\vtlib\Deprecated::createModuleMetaFile();
		foreach ($this->cron as $cronName) {
			$cron = \vtlib\Cron::getInstance($cronName);
			if (!empty($cron)) {
				$cron->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
			}
		}
		return true;
	}

	private function addModule($module)
	{
		$db = PearDatabase::getInstance();
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		$dirName = 'cache/updates/files/';
		if (file_exists('cache/updates/' . $module . '.xml') && !\vtlib\Module::getInstance($module)) {
			$locations = ['modules/' . $module];
			foreach ($locations as $loc) {
				if (is_dir($dirName . $loc) && !file_exists($rootDir . $loc)) {
					mkdir($rootDir . $loc);
				}
				\vtlib\Functions::recurseCopy($dirName . $loc, $loc, true);
				\vtlib\Functions::recurseDelete($dirName . $loc);
			}

			$importInstance = new \vtlib\PackageImport();
			$importInstance->_modulexml = simplexml_load_file('cache/updates/' . $module . '.xml');
			$importInstance->import_Module();
			unlink('cache/updates/' . $module . '.xml');
			$this->postInstalModule($module);
		}
	}

	private function postInstalModule($moduleName)
	{
		$db = PearDatabase::getInstance();
		$db->update('vtiger_tab', ['customized' => 0], '`name` = ?', [$moduleName]);
		$prefix = $this->getPrefix($moduleName);
		$moduleData = \vtlib\Functions::getModuleData($moduleName);
		\includes\fields\RecordNumber::setNumber($moduleData['tabid'], $prefix, '1');
		$this->workflow();
	}

	private function getPrefix($moduleName)
	{
		$prefixes = [
			'Notification' => 'NT'
		];
		return $prefixes[$moduleName];
	}

	private function getCronData($index)
	{
		$crons = [];
		switch ($index) {
			case 1:
				$crons = [
					['type' => 'add', 'data' => ['LBL_MARK_RECORDS_AS_REVIEWED', 'modules/ModTracker/cron/ReviewChanges.php', '900', null, null, '0', 'ModTracker', '25', '']],
					['type' => 'remove', 'data' => ['LBL_SEND_NOTIFICATIONS', 'modules/Home/cron/Notifications.php', '900', null, null, '1', 'Home', '19', '']],
					['type' => 'add', 'data' => ['LBL_SEND_NOTIFICATIONS', 'modules/Notification/cron/Notifications.php', '900', null, null, '0', 'Notification', '19', '']],
				];
				break;
			default:
				break;
		}
		return $crons;
	}

	private function cron($crons = [])
	{
		$db = \PearDatabase::getInstance();
		if ($crons) {
			foreach ($crons as $cron) {
				if (empty($cron)) {
					continue;
				}
				$cronData = $cron['data'];
				$result = $db->pquery('SELECT 1 FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;', [$cronData[0], $cronData[1]]);
				if (!$db->getRowCount($result) && $cron['type'] === 'add') {
					\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
					$this->cron[] = $cronData[0];
				} elseif ($db->getRowCount($result) && $cron['type'] === 'remove') {
					\vtlib\Cron::deregister($cronData[0]);
				}
			}
		}
	}

	private function updateSettingMenu()
	{
		$db = PearDatabase::getInstance();
		$maxFieldId = $db->getUniqueID('vtiger_settings_field');
		$db->update('vtiger_settings_field_seq', ['id' => $maxFieldId - 1]);

		$menu = [
			['LBL_USER_MANAGEMENT', 'LBL_ADVANCED_PERMISSION', 'glyphicon glyphicon-ice-lolly', null, 'index.php?module=AdvancedPermission&parent=Settings&view=List', '10', '0', '0']
		];
		$blocks = [];
		foreach ($menu as $row) {
			if (!array_key_exists($row[0], $blocks)) {
				$blockInstance = Settings_Vtiger_Menu_Model::getInstance($row[0]);
				$blocks[$row[0]] = $blockInstance;
			}
			$result = $db->pquery('SELECT 1 FROM `vtiger_settings_field` WHERE `name` = ?', [$row[1]]);
			if ($result->rowCount() > 0 && !empty($blocks[$row[0]])) {
				$db->update('vtiger_settings_field', ['blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => $row[5], 'active' => $row[6], 'pinned' => $row[7]], '`name` = ?', [$row[1]]);
			} elseif (!empty($blocks[$row[0]])) {
				$fieldId = $db->getUniqueID('vtiger_settings_field');
				$db->insert('vtiger_settings_field', ['fieldid' => $fieldId, 'blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => $row[5], 'active' => $row[6], 'pinned' => $row[7]]);
			}
		}
	}

	private function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					['type' => 'add', 'name' => 'a_yf_adv_permission', 'sql' => '`a_yf_adv_permission` (
						`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
						`name` varchar(255) NOT NULL,
						`tabid` int(19) DEFAULT NULL,
						`status` tinyint(1) unsigned NOT NULL,
						`action` tinyint(1) unsigned NOT NULL,
						`conditions` text NOT NULL,
						`members` text NOT NULL,
						`priority` tinyint(1) unsigned NOT NULL,
						PRIMARY KEY (`id`),
						KEY `tabid` (`tabid`)
					  )'],
					['type' => 'add', 'name' => 'u_yf_reviewed_queue', 'sql' => '`u_yf_reviewed_queue` (
						`id` int(19) NOT NULL,
						`userid` int(11) NOT NULL,
						`tabid` int(11) DEFAULT NULL,
						`data` text,
						`time` datetime DEFAULT NULL,
						PRIMARY KEY (`id`),
						KEY `userid` (`userid`),
						CONSTRAINT `fk_1_u_yf_reviewed_queue` FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
					  )'],
					['type' => 'remove', 'name' => 'l_yf_notification'],
					['type' => 'remove', 'name' => 'l_yf_notification_archive'],
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	private function setTablesScheme($tables)
	{
		$db = \PearDatabase::getInstance();
		foreach ($tables as $table) {
			if (empty($table)) {
				continue;
			}
			switch ($table['type']) {
				case 'add':
					$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE=InnoDB DEFAULT CHARSET="utf8";');
					break;
				case 'remove':
					$db->query('DROP TABLE IF EXISTS ' . $table['name'] . ';');
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

	private function getLink($index)
	{
		$links = [];
		switch ($index) {
			case 1:
				$links = [
					['type' => 'add', 'data' => ['297', 'Home', 'DASHBOARDWIDGET', 'LBL_CLOSED_TICKETS_BY_PRIORITY', 'index.php?module=HelpDesk&view=ShowWidget&name=ClosedTicketsByPriority', '', '0', null, null, null, null]],
					['type' => 'add', 'data' => ['298', 'Home', 'DASHBOARDWIDGET', 'LBL_CLOSED_TICKETS_BY_USER', 'index.php?module=HelpDesk&view=ShowWidget&name=ClosedTicketsByUser', '', '0', null, null, null, null]],
					['type' => 'add', 'data' => ['299', 'Home', 'DASHBOARDWIDGET', 'LBL_ACCOUNTS_BY_INDUSTRY', 'index.php?module=Accounts&view=ShowWidget&name=AccountsByIndustry', '', '0', null, null, null, null]],
					['type' => 'add', 'data' => ['300', 'Accounts', 'DASHBOARDWIDGET', 'LBL_ACCOUNTS_BY_INDUSTRY', 'index.php?module=Accounts&view=ShowWidget&name=AccountsByIndustry', '', '0', null, null, null, null]],
					['type' => 'update', 'data' => ['3', 'Home', 'DASHBOARDWIDGET', 'Notifications', 'index.php?module=Notification&view=ShowWidget&name=Notifications', null, '3', null, null, null, null]],
					['type' => 'add', 'data' => ['301', 'Home', 'DASHBOARDWIDGET', 'LBL_TOTAL_ESTIMATED_VALUE_BY_STATUS', 'index.php?module=SSalesProcesses&view=ShowWidget&name=EstimatedValueByStatus', '', '0', null, null, null, null]],
					['type' => 'add', 'data' => ['302', 'SSalesProcesses', 'DASHBOARDWIDGET', 'LBL_TOTAL_ESTIMATED_VALUE_BY_STATUS', 'index.php?module=SSalesProcesses&view=ShowWidget&name=EstimatedValueByStatus', '', '0', null, null, null, null]],
				];
				break;
			default:
				break;
		}
		return $links;
	}

	private function setLink($links)
	{
		$db = PearDatabase::getInstance();
		if (!empty($links)) {
			foreach ($links as $link) {
				if (empty($link)) {
					continue;
				}
				list($id, $tabid, $type, $label, $url, $iconpath, $sequence, $path, $class, $method, $params) = $link['data'];
				$tabid = \vtlib\Functions::getModuleId($tabid);
				$handlerInfo = ['path' => $path, 'class' => $class, 'method' => $method];
				if ($link['type'] === 'add') {
					$result = $db->pquery('SELECT 1 FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=?;', [$tabid, $type, $label, $url]);
					if (!$db->getRowCount($result))
						\vtlib\Link::addLink($tabid, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
				} elseif ($link['type'] === 'remove') {
					\vtlib\Link::deleteLink($tabid, $type, $label, $url);
				} elseif ($link['type'] === 'update') {
					$db->update('vtiger_links', [
						'linktype' => $type,
						'tabid' => $tabid,
						'linklabel' => $label,
						'linkurl' => $url,
						'linkicon' => $iconpath,
						'sequence' => $sequence,
						'handler_path' => $path,
						'handler_class' => $class,
						'handler' => $method,
						'params' => $params,
						], 'linklabel = ? AND tabid = ?;', [$label, $tabid]);
				}
			}
		}
	}

	private function checkFieldExists($moduleName, $column, $table)
	{
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery('SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;', [$column, $table]);
		else {
			if (is_numeric($moduleName)) {
				$tabId = $moduleName;
			} else {
				$tabId = \vtlib\Functions::getModuleId($moduleName);
			}
			$result = $db->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", [$column, $table, $tabId]);
		}
		if (!$db->getRowCount($result)) {
			return false;
		}
		return true;
	}

	private function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['86', '1781', 'campaignid', 'u_yf_ssalesprocesses', '1', '10', 'campaignid', 'FL_CAMPAIGN', '1', '2', '', '100', '2', '269', '1', 'V~O', '1', null, 'BAS', '1', '', '0', '', null, "int(19)", 'LBL_SSALESPROCESSES_INFORMATION', [], ['Campaigns'], 'SSalesProcesses'],
					['86', '1781', 'ssalesprocessesid', 'vtiger_project', '1', '10', 'ssalesprocessesid', 'SINGLE_SSalesProcesses', '1', '2', '', '100', '2', '269', '1', 'V~O', '1', null, 'BAS', '1', '', '0', '', null, "int(19)", 'LBL_PROJECT_INFORMATION', [], ['SSalesProcesses'], 'Project']
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setFields($fields)
	{
//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (!\vtlib\Functions::getModuleId($field[28]) || $this->checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$moduleInstance = \vtlib\Module::getInstance($field[28]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $moduleInstance);
			$fieldInstance = new \vtlib\Field();
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
	}

	private function getRelations($index)
	{
		$ralations = [];
		switch ($index) {
			case 1:
				$ralations = [
					['type' => 'add', 'data' => ['513', 'Campaigns', 'SSalesProcesses', 'get_dependents_list', '10', 'SSalesProcesses', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['514', 'SSalesProcesses', 'Project', 'get_dependents_list', '19', 'Project', '0', 'ADD', '0', '0', '0']]
				];
				break;
			default:
				break;
		}
		return $ralations;
	}

	private function setRelations($data)
	{
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $relation) {
				if (empty($relation)) {
					continue;
				}
				list($id, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment) = $relation['data'];
				$tabid = \vtlib\Functions::getModuleId($moduleName);
				$relTabid = \vtlib\Functions::getModuleId($relModuleName);
				$result = $db->pquery("SELECT 1 FROM `vtiger_relatedlists` WHERE tabid=? AND related_tabid = ? AND name = ? AND label = ?;", [$tabid, $relTabid, $name, $label]);
				if ($result->rowCount() === 0 && $relation['type'] === 'add') {
					$sequence = $this->getMax('vtiger_relatedlists', 'sequence', "WHERE tabid = $tabid");
					$db->insert('vtiger_relatedlists', [
						'relation_id' => $db->getUniqueID('vtiger_relatedlists'),
						'tabid' => $tabid,
						'related_tabid' => $relTabid,
						'name' => $name,
						'sequence' => $sequence,
						'label' => $label,
						'presence' => $presence,
						'actions' => $actions,
						'favorites' => $favorites,
						'creator_detail' => $creatorDetail,
						'relation_comment' => $relationComment
					]);
				} elseif ($result->rowCount() > 0 && $relation['type'] === 'remove') {
					$db->delete('vtiger_relatedlists', '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
				} elseif ($relation['type'] === 'update') {
					$keyByName = ['relation_id', 'tabid', 'related_tabid', 'name', 'sequence', 'label', 'presence', 'actions', 'favorites', 'creator_detail', 'relation_comment'];
					$updateField = [];
					foreach ($relation['updateField'] as $key => $value) {
						$relation['data'][$key] = $value;
						$updateField[$keyByName[$key]] = $value;
					}
					if ($result->rowCount() > 0) {
						if (empty($updateField)) {
							trigger_error('ERROR ' . __CLASS__ . '::' . __METHOD__ . ' A row in vtiger_relatedlists was not updated due to lack of data. ' . print_r($relation, true), E_USER_WARNING);
						} else {
							$db->update('vtiger_relatedlists', $updateField, '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
						}
					} else {
						$relation['type'] = 'add';
						$this->setRelations([$relation]);
					}
				}
			}
		}
	}

	private function getConfigurations()
	{
		return [
			['name' => 'config/csrf_config.php', 'conditions' => [
					['type' => 'update', 'search' => '$_SERVER[\'HTTP_X_PJAX\'] == true', 'replace' => ['$_SERVER[\'HTTP_X_PJAX\'] == true', '$_SERVER[\'HTTP_X_PJAX\'] === true']],
				]
			],
			['name' => 'config/modules/ModTracker.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'REVIEW_CHANGES_LIMIT', 'addingType' => 'before', 'value' => "	// Max number to update records
	'REVIEW_CHANGES_LIMIT' => 50,
	// Max number to update records by cron
	'REVIEWED_SCHEDULE_LIMIT' => 1000,
"],
				]
			],
			['name' => 'config/security.php', 'conditions' => [
					['type' => 'add', 'search' => 'PERMITTED_BY_RECORD_HIERARCHY', 'checkInContents' => 'PERMITTED_BY_ADVANCED_PERMISSION', 'addingType' => 'after', 'value' => "	'PERMITTED_BY_ADVANCED_PERMISSION' => true,
"],
					['type' => 'add', 'search' => 'PERMITTED_BY_RECORD_HIERARCHY', 'checkInContents' => 'CACHING_PERMISSION_TO_RECORD', 'addingType' => 'after', 'value' => "	/*
	 * Configuration of the permission mechanism on records list.
	 * true - Permissions based on the users column in vtiger_crmentity
	 * false - Permissions based on adding tables with permissions to query (old mechanism)
	 */
	'CACHING_PERMISSION_TO_RECORD' => false,
"],
					['type' => 'update', 'search' => 'RESTRICTED_DOMAINS_EXCLUDED', 'checkInLine' => 'Users', 'replace' => [']', ", 'Users']"]],
				]
			],
			['name' => 'config/modules/OSSMail.php', 'conditions' => []],
			['name' => 'config/modules/API.php', 'conditions' => []],
		];
	}

	private function updateConfigurationFiles()
	{
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = file($fileName);
				$emptyLine = false;
				$addContent = [];
				$indexes = [];
				foreach ($configContent as $key => $line) {
					if ($emptyLine && strlen($line) == 1) {
						unset($configContent[$key]);
						$emptyLine = false;
						continue;
					}
					$emptyLine = false;
					foreach ($conditions as $index => $condition) {
						if (empty($condition)) {
							continue;
						}
						if ($condition['type'] === 'add' && !in_array($index, $indexes)) {
							$addContent[$index] = $condition['value'];
							$indexes[] = $index;
						}
						if (strpos($line, $condition['search']) !== false) {
							switch ($condition['type']) {
								case 'add':
									if ($condition['checkInContents'] && strpos($baseContent, $condition['checkInContents']) === false) {
										if ($condition['trim']) {
											$configContent[$key] = $this->getTrimValue($condition['trim'], $configContent[$key]);
										}
										$configContent[$key] = $condition['addingType'] === 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									unset($configContent[$key]);
									$emptyLine = true;
									break;
								case 'update':
									if ($condition['checkInLine'] && (strpos($condition['checkInLine'], $configContent[$key]) !== false)) {
										break;
									}
									if ($condition['replace']) {
										$configContent[$key] = str_replace($condition['replace'][0], $condition['replace'][1], $configContent[$key]);
									} else {
										$configContent[$key] = $condition['value'];
									}
									break;
								default:
									break;
							}
						}
					}
				}
				$content = implode("", $configContent);
				if ($addContent) {
					$addContentString = implode("", $addContent);
					$content .= $addContentString;
				}
				$file = fopen($fileName, "w+");
				fwrite($file, $content);
				fclose($file);
			} else {
				$dirName = 'cache/updates/' . $config['name'];
				$sourceFile = $rootDirectory . $dirName;
				if (file_exists($sourceFile)) {
					copy($sourceFile, $fileName);
				}
			}
		}
	}

	private function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => ['remove', 'Key_name'], 'name' => 'PRIMARY', 'table' => 'u_yf_mail_address_boock', 'sql' => "ALTER TABLE `u_yf_mail_address_boock` 
	ADD KEY `id`(`id`), DROP KEY `PRIMARY`;"],
					['type' => ['add'], 'name' => 'users', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` ADD COLUMN `users` text  NULL after `was_read` ;"],
					['type' => ['change', 'Type'], 'name' => 'generatedtype', 'validType' => 'tinyint', 'table' => 'vtiger_field', 'sql' => "ALTER TABLE `vtiger_field` 
						CHANGE `generatedtype` `generatedtype` tinyint(19) unsigned   NOT NULL DEFAULT 0 after `tablename` , 
						CHANGE `uitype` `uitype` smallint(5) unsigned   NOT NULL after `generatedtype` , 
						CHANGE `readonly` `readonly` tinyint(1) unsigned   NOT NULL after `fieldlabel` , 
						CHANGE `presence` `presence` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `readonly` , 
						CHANGE `maximumlength` `maximumlength` smallint(5) unsigned   NOT NULL after `defaultvalue` , 
						CHANGE `sequence` `sequence` smallint(5) unsigned   NOT NULL after `maximumlength` , 
						CHANGE `displaytype` `displaytype` tinyint(1) unsigned   NOT NULL after `block` , 
						CHANGE `quickcreate` `quickcreate` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `typeofdata` ;"],
					['type' => ['add'], 'name' => 'members', 'table' => 'a_yf_adv_permission', 'sql' => "ALTER TABLE `a_yf_adv_permission` 
						CHANGE `conditions` `conditions` text  NOT NULL after `action` , 
						ADD COLUMN `members` text NOT NULL after `conditions` , 
						ADD COLUMN `priority` tinyint(1) unsigned   NOT NULL after `members` , ENGINE=InnoDB; "],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setAlterTables($data)
	{
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
				if (( $num === 0 && $alter['type'][0] === 'add') || ($num > 0 && $alter['type'][0] === 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && in_array($alter['type'][0], ['change', 'changeTable'])) {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
	}

	private function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$actions = [
					['type' => 'add', 'name' => 'CreateDashboardChartFilter'],
					['type' => 'add', 'name' => 'CreateCustomFilter', 'sql' => "SELECT tabid FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name NOT IN ('ModComments','Events','Emails','');"],
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	private function actionMapp($actions)
	{
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
			$permission = 1;
			if (isset($action['permission'])) {
				$permission = $action['permission'];
			}

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
						$db->insert('vtiger_profile2utility', [
							'profileid' => $profileId, 'tabid' => $tabid, 'activityid' => $key, 'permission' => $permission
						]);
					}
				}
			}
		}
	}

	private function updatePack()
	{
		$db = \PearDatabase::getInstance();
		$modules = ['PaymentsIn', 'PaymentsOut', 'LettersIn', 'LettersOut'];
		$modules = array_map('\vtlib\Functions::getModuleId', $modules);
		$db->update('vtiger_field', ['typeofdata' => 'DT~O'], 'tabid IN (' . $db->generateQuestionMarks($modules) . ') AND columnname = ? AND typeofdata = ?', [ $modules, 'createdtime', 'V~O']);
		$db->update('vtiger_field', ['typeofdata' => 'DT~O'], 'tablename = ? AND columnname IN (?,?) AND typeofdata = ?', [ 'vtiger_callhistory', 'start_time', 'end_time', 'V~O']);
		$this->setFields($this->getFields(1));
		$this->setRelations($this->getRelations(1));
		$this->setLink($this->getLink(1));
	}

	private function workflow()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT task_id FROM `com_vtiger_workflowtasks` WHERE `task` LIKE \'%VTWatchdog%\'';
		$result = $db->query($query);
		$records = $db->getArrayColumn($result);
		if ($records) {
			$db->delete('com_vtiger_workflowtasks', 'task_id IN (' . $db->generateQuestionMarks($records) . ')', $records);
		}
	}

	private function getMax($table, $field, $filter = '')
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		return $id;
	}
}
