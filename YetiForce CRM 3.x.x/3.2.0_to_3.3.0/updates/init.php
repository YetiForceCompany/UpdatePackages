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
	protected $cron = [];
	protected $roles = [];
	var $filesToDelete = [
		'layouts/basic/modules/Assets/ListViewContents.tpl',
		'layouts/basic/modules/Assets/ListViewLeftSide.tpl',
		'layouts/basic/modules/Assets/ListViewRecordActions.tpl',
		'layouts/basic/modules/Vtiger/ListViewRecordActions.tpl',
		'layouts/basic/modules/Vtiger/RelatedListActions.tpl',
		'layouts/basic/modules/Settings/ModuleManager/MissingLibrary.tpl',
		'modules/Vtiger/layout_utils.php',
		'libraries/kcfinder',
		'libraries/nusoap/class.soapclient.php',
		'libraries/Smarty/libs/plugins/function.math.php',
		'libraries/Smarty/libs/plugins/modifiercompiler.escape.php',
		'libraries/Smarty/libs/plugins/shared.literal_compiler_param.php',
		'libraries/Smarty/libs/sysplugins/smarty_cacheresource_custom.php',
		'libraries/Smarty/libs/sysplugins/smarty_cacheresource_keyvaluestore.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_cacheresource_file.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_compile_include_php.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_compile_insert.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_runtime_tplfunction.php',
		'modules/Mobile',
		'modules/Assets/handlers/Renewal.php',
		'modules/OSSSoldServices/handlers/Renewal.php',
		'languages/de_de/Mobile.php',
		'languages/en_us/Mobile.php',
		'languages/fr_fr/Mobile.php',
		'languages/pl_pl/Mobile.php',
		'languages/pt_br/Mobile.php',
		'languages/ru_ru/Mobile.php',
		'layouts/basic/modules/Mobile',
		'layouts/basic/skins/images/Mobile.png',
		'libraries/php-debugbar/src/DebugBar/Bridge/Twig/TraceableTwigEnvironment.php',
		'layouts/basic/modules/OSSPasswords/ListViewRecordActions.tpl ',
		'layouts/basic/modules/OSSPasswords/RelatedListActions.tpl',
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
		$this->actionMapp($this->getActionMapp(1));
		$this->setTablesScheme($this->getTablesAction(1));
		$this->addModule('OpenStreetMap');
		$this->updatePack();
		$this->improveProfileActions();
		$this->updateConfigurationFiles();
	}

	public function postupdate()
	{
		$db = PearDatabase::getInstance();
		$dirName = 'cache/updates';
		$result = true;
		$modulenode = $this->modulenode;

		foreach ($this->cron as $cronName) {
			$cron = \vtlib\Cron::getInstance($cronName);
			if (!empty($cron)) {
				$cron->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
			}
		}
		$cronToCoordinates = \vtlib\Cron::getInstance('UpdaterCoordinates');
		if ($cronToCoordinates && $cronToCoordinates->isDisabled()) {
			$cronToCoordinates->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
		}

		$db->query('SET FOREIGN_KEY_CHECKS = 1;');
		$db->insert('yetiforce_updates', [
			'user' => Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => $modulenode->label,
			'from_version' => $modulenode->from_version,
			'to_version' => $modulenode->to_version,
			'result' => $result,
		]);
		if ($result) {
			$db->update('vtiger_version', ['current_version' => $modulenode->to_version]);
		}
		\vtlib\Functions::recurseDelete($dirName);
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\vtlib\Deprecated::createModuleMetaFile();
		$this->updateModuleMetaFile();
		\vtlib\Access::syncSharingAccess();
		if (headers_sent()) {
			die('<div class="well pushDown">System update completed: <a class="btn btn-success" href="' . AppConfig::main('site_URL') . '">Return to the homepage</a></div>');
		} else {
			exit(header('Location: ' . AppConfig::main('site_URL')));
		}
	}

	private function updateModuleMetaFile()
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$fileName = 'user_privileges/tabdata.php';
		if (file_exists($fileName)) {
			$tabData = require 'user_privileges/tabdata.php';
			if (!is_array($tabData)) {
				if ($handle = fopen($fileName, 'w+')) {
					$newBuf = "<?php\n";
					$tabData = [];
					$map = ['tabId' => 'tab_info_array', 'tabPresence' => 'tab_seq_array', 'tabOwnedby' => 'tab_ownedby_array', 'actionId' => 'action_id_array', 'actionName' => 'action_name_array'];
					foreach ($map as $key => $variable) {
						if (isset($$variable)) {
							$data = $$variable;
							$newBuf.= "\$$variable=" . self::varExportMin($data) . ";\n";
							$tabData[$key] = $data;
						}
					}
					$newBuf .= 'return ' . self::varExportMin($tabData) . ";\n";
					fputs($handle, $newBuf);
					fclose($handle);
				} else {
					$log->debug("ERROR - Cannot open file ($fileName)");
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private static function varExportMin($var)
	{
		if (is_array($var)) {
			$toImplode = [];
			foreach ($var as $key => $value) {
				$toImplode[] = var_export($key, true) . '=>' . self::varExportMin($value);
			}
			$code = '[' . implode(',', $toImplode) . ']';
			return $code;
		} else {
			return var_export($var, true);
		}
	}

	private function getConfigurations()
	{
		return [
			['name' => 'config/api.php', 'conditions' => [
					['type' => 'remove', 'search' => 'mobileModule'],
				]
			],
			['name' => 'config/config.inc.php', 'conditions' => [
					['type' => 'remove', 'search' => 'disable send files using KCFinder'],
					['type' => 'remove', 'search' => '$upload_disabled'],
					['type' => 'remove', 'search' => 'TODO: set db_hostname dependending on db_type'],
					['type' => 'remove', 'search' => 'TODO: test if port is empty'],
				]
			],
			['name' => 'config/debug.php', 'conditions' => [
					['type' => 'add', 'search' => 'DISPLAY_DEBUG_VIEWER', 'checkInContents' => 'DISPLAY_DEBUG_CONSOLE', 'addingType' => 'after', 'value' => "	// Display Main Debug Console
	'DISPLAY_DEBUG_CONSOLE' => false,
"],
				]
			],
			['name' => 'config/developer.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'MISSING_LIBRARY_DEV_MODE', 'addingType' => 'before', 'value' => "	// Developer libraries update mode 
	'MISSING_LIBRARY_DEV_MODE' => false,
"],
				]
			],
			['name' => 'config/modules/Assets.php', 'conditions' => [
					['type' => 'update', 'search' => 'ex. -2 month, -1 day', 'replace' => ['-', '']],
					['type' => 'update', 'search' => 'RENEWAL_TIME', 'replace' => ['-', '']],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'RENEWAL_CUSTOMER_FUNCTION', 'addingType' => 'before', 'value' => "	// ['class' => '', 'method' => '', 'hierarchy' => ''],
	'RENEWAL_CUSTOMER_FUNCTION' => [],
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_HIERARCHY_IN_MODAL', 'addingType' => 'before', 'value' => "	// false, [] - inherit fields, [ label => column name, .. ]
	'SHOW_HIERARCHY_IN_MODAL' => [],
"],
					['type' => 'update', 'search' => 'SHOW_RELATION_IN_MODAL', 'replace' => ["'relatedModule' => 'FInvoice'", "'relatedModule' => ['FInvoice', 'ModComments', 'Calendar', 'Documents']"]],
				]
			],
			['name' => 'config/modules/OSSSoldServices.php', 'conditions' => [
					['type' => 'update', 'search' => 'ex. -2 month, -1 day', 'replace' => ['-', '']],
					['type' => 'update', 'search' => 'RENEWAL_TIME', 'replace' => ['-', '']],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'RENEWAL_CUSTOMER_FUNCTION', 'addingType' => 'before', 'value' => "	// ['class' => '', 'method' => '', 'hierarchy' => ''],
	'RENEWAL_CUSTOMER_FUNCTION' => [],
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_HIERARCHY_IN_MODAL', 'addingType' => 'before', 'value' => "	// false, [] - inherit fields, [ label => column name, .. ]
	'SHOW_HIERARCHY_IN_MODAL' => [],
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_RELATION_IN_MODAL', 'addingType' => 'before', 'value' => "	'SHOW_RELATION_IN_MODAL' => ['relationField' => 'parent_id', 'module' => 'Accounts', 'relatedModule' => ['FInvoice', 'ModComments', 'Calendar', 'Documents']],
"],
				]
			],
			['name' => 'config/modules/Email.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'HELPDESK_OPENTICKET_STATUS', 'addingType' => 'before', 'value' => "	// What status should be set when a ticket is closed, but a new mail regarding the ticket is received.
	'HELPDESK_OPENTICKET_STATUS' => 'Open',
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'HELPDESK_NEXT_WAIT_FOR_RESPONSE_STATUS', 'addingType' => 'before', 'value' => "	// What status should be set when a new mail is received regarding a ticket, whose status is awaiting response.
	'HELPDESK_NEXT_WAIT_FOR_RESPONSE_STATUS' => 'Answered',
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'ORIENTATION_PANEL_VIEW', 'addingType' => 'before', 'value' => "	// h - Horinzontal, v - vertical
	'ORIENTATION_PANEL_VIEW' => 'v',
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'RC_COMPOSE_ADDRESS_MODULES', 'addingType' => 'before', 'value' => "	// List of of modules from which you can choose e-mail address in the mail
	'RC_COMPOSE_ADDRESS_MODULES' => ['Accounts', 'Contacts', 'OSSEmployees', 'Leads', 'Vendors', 'Partners', 'Competition'],
"],
				]
			],
			['name' => 'config/performance.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER', 'addingType' => 'before', 'value' => "	// In how many records should the address boock be updated in cron
	'CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER' => 10000,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'ENABLE_CACHING_USERS', 'addingType' => 'before', 'value' => "	// Enable caching of user data
	'ENABLE_CACHING_USERS' => false,
"],
				]
			],
			['name' => 'config/security.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'RESTRICTED_DOMAINS_ACTIVE', 'addingType' => 'before', 'value' => "	// Restricted domains allow you to block saving an email address from a given domain in the system. 
	// Restricted domains work only for email address type fields.
	'RESTRICTED_DOMAINS_ACTIVE' => false,
	// Restricted domains
	'RESTRICTED_DOMAINS_VALUES' => [],
	// List of modules where restricted domains are enabled, if empty it will be enabled everywhere.
	'RESTRICTED_DOMAINS_ALLOWED' => [],
	//List of modules excluded from restricted domains validation.
	'RESTRICTED_DOMAINS_EXCLUDED' => ['OSSEmployees'],
"],
				]
			],
			['name' => 'config/modules/API.php', 'conditions' => []],
		];
	}

	private function updateConfigurationFiles()
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
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
						if ($condition['type'] == 'add' && !in_array($index, $indexes)) {
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
										$configContent[$key] = $condition['addingType'] == 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									unset($configContent[$key]);
									$emptyLine = true;
									break;
								case 'update':
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
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	private function improveProfileActions()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT profileid FROM vtiger_profile;');
		$profile = $db->getArrayColumn($result);
		$nonConfigurableActions = Vtiger_Action_Model::$nonConfigurableActions;
		$standardActions = Vtiger_Action_Model::$standardActions;
		$standardActionsKeys = array_keys($standardActions);
		$query = sprintf('SELECT profileid, tabid, operation FROM vtiger_profile2standardpermissions WHERE operation IN (%s);', $db->generateQuestionMarks($standardActionsKeys));
		$result = $db->pquery($query, $standardActionsKeys);
		$indexPermisions = [];
		while ($row = $db->getRow($result)) {
			$indexPermisions[$row['profileid']][$row['operation']][] = $row['tabid'];
		}
		$modules = \vtlib\Functions::getAllModules(true);
		$modulesId = array_keys($modules);
		foreach ($profile as $profileid) {
			if (!array_key_exists($profileid, $indexPermisions)) {
				$indexPermisions[$profileid] = [];
			}
		}
		foreach ($indexPermisions as $profileId => $actions) {
			$diffActions = array_diff_key($standardActions, $actions);
			if ($diffActions) {
				foreach ($diffActions as $actionId => $actionName) {
					$actions[$actionId] = [];
				}
			}
			foreach ($actions as $actionId => $tabsId) {
				$diff = array_diff($modulesId, $tabsId);
				foreach ($diff as $tabId) {
					$permission = in_array($standardActions[$actionId], $nonConfigurableActions) ? 0 : 1;
					$db->insert('vtiger_profile2standardpermissions', [
						'profileid' => $profileId,
						'tabid' => $tabId,
						'operation' => $actionId,
						'permissions' => $permission
					]);
				}
			}
		}
	}

	private function updatePack()
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = \PearDatabase::getInstance();
		$db->delete('vtiger_profile2tab', 'tabid = ?', [\vtlib\Functions::getModuleId('OSSMailScanner')]);
		$this->removeFields($this->getFieldsToRemove(1));
		$result = $db->query('SELECT 1 FROM u_yf_openstreetmap_address_updater');
		if (!$result->rowCount()) {
			$db->insert('u_yf_openstreetmap_address_updater', ['crmid' => 0]);
		}
		$this->cron($this->getCronData(1));
		$this->setFields($this->getFields(1));
		$this->setTrees($this->getTrees(1));
		$this->picklists($this->getPicklistsToAction(1));
		$db->update('vtiger_field', ['fieldname' => 'mid'], 'tablename = ? AND columnname = ?', [ 'vtiger_ossmailview', 'id']);
		$this->setAlterTables($this->getAlterTables(1));
		$db->update('vtiger_field', ['fieldlabel' => 'FL_CAMPAIGN_STATUS'], '((tablename = ? AND columnname = ?) OR (tablename = ? AND columnname = ?)) AND fieldlabel = ?', [ 'vtiger_campaign', 'campaignstatus', 'vtiger_campaignrelstatus', 'campaignrelstatus', 'Campaign Status']);
		$this->handler($this->getHandlerData(1));
		$db->delete('vtiger_ws_operation', 'handler_path = ?', ['modules/Mobile/api/wsapi.php']);
		$db->delete('vtiger_tab', 'name = ?', ['Mobile']);
		$db->update('vtiger_ossmailscanner_config', ['parameter' => 'changeTicketStatus', 'value' => 'noAction'], 'parameter = ?', [ 'change_ticket_status']);
		$pickValues = ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', 'PLL_RENEWED_VERIFICATION', 'PLL_NOT_RENEWED_VERIFICATION', '', 'PLL_RENEWED', 'PLL_NOT_RENEWED', 'PLL_NOT_APPLICABLE', 'PLL_NOT_APPLICABLE_VERIFICATION'];
		$db->update('vtiger_assets_renew', ['presence' => 0], 'assets_renew IN (' . $db->generateQuestionMarks($pickValues) . ')', $pickValues);
		$db->update('vtiger_osssoldservices_renew', ['presence' => 0], 'osssoldservices_renew IN (' . $db->generateQuestionMarks($pickValues) . ')', $pickValues);
		$modules = [\vtlib\Functions::getModuleId('HelpDesk'), \vtlib\Functions::getModuleId('Assets'), \vtlib\Functions::getModuleId('KnowledgeBase')];
		$db->update('vtiger_trees_templates', ['access' => 0], '`name` = ? AND  module IN (' . $db->generateQuestionMarks($modules) . ')', ['Category', $modules]);
		$db->delete('vtiger_tab_info', 'tabid NOT IN (SELECT tabid FROM vtiger_tab)');
		$db->update('vtiger_users', ['rowheight' => 'medium']);
		$this->updateFilter();
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private function updateFilter()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT cvid, columnindex, value FROM vtiger_cvadvfilter WHERE columnname LIKE "%vtiger_crmentity:smownerid:assigned_user_id%" AND comparator = "e" ');
		if ($result->rowCount()) {
			$check = true;
			while ($row = $db->getRow($result)) {
				$usersFilter[$row['cvid'] . '-' . $row['columnindex']] = $row['value'];
				$users[] = $row['value'];
				if ($check && $row['value']) {
					$checkVal = explode(',', $row['value']);
					if (is_numeric($checkVal[0])) {
						return false;
					}
					$check = false;
				}
			}
			$usersArr = explode(',', implode(',', $users));
			$valueSql = ' IN (' . $db->generateQuestionMarks($usersArr) . ') ';
			$entityFields = \includes\Modules::getEntityInfo('Users');
			if (count($entityFields['fieldnameArr']) > 1) {
				$columns = [];
				foreach ($entityFields['fieldnameArr'] as &$fieldname) {
					$columns[$fieldname] = $entityFields['tablename'] . '.' . $fieldname;
				}
				$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat($columns, 'Users');
				$fieldSql = "SELECT id,(trim($concatSql)) as display_value  FROM vtiger_users WHERE  (trim($concatSql) $valueSql )";
				$fieldSqlG = "SELECT groupid, groupname as display_value FROM vtiger_groups WHERE  vtiger_groups.groupname $valueSql";
			} else {
				$columnSql = $entityFields['tablename'] . '.' . $entityFields['fieldname'];
				$fieldSql = "SELECT id,$columnSql as display_value  FROM vtiger_users WHERE  $columnSql $valueSql )";
				$fieldSqlG = "SELECT groupid, groupname as display_value FROM vtiger_groups WHERE vtiger_groups.groupname $valueSql";
			}
			$result = $db->pquery($fieldSql, $usersArr);
			$resultG = $db->pquery($fieldSqlG, $usersArr);
			$data = [];
			while ($row = $db->getRow($result)) {
				$data[$row['id']] = $row['display_value'];
			}
			while ($row = $db->getRow($resultG)) {
				$data[$row['groupid']] = $row['display_value'];
			}
			foreach ($usersFilter as $filterId => $userName) {
				$userNameArr = explode(',', $userName);
				$replace = [];
				foreach ($userNameArr as $name) {
					if (in_array($name, $data)) {
						$replace[] = array_search($name, $data);
					}
				}
				$filterIdArr = explode('-', $filterId);
				$cvid = $filterIdArr[0];
				$columnIndex = $filterIdArr[1];
				if ($replace) {
					$db->update('vtiger_cvadvfilter', ['value' => implode(',', $replace)], ' cvid = ? AND columnname LIKE "%vtiger_crmentity:smownerid:assigned_user_id%"  AND comparator = "e" AND columnindex = ?', [$cvid, $columnIndex]);
				}
			}
		}
	}

	private function getTrees($index)
	{
		$trees = [];
		switch ($index) {
			case 1:
				$trees = [
					[
						'column' => 'category',
						'base' => ['17', 'Category', \vtlib\Functions::getModuleId('Partners'), '0'],
						'data' => [['17', 'LBL_NONE', 'T1', 'T1', '0', 'LBL_NONE', '', '']]
					]
				];
				break;
			default:
				break;
		}
		return $trees;
	}

	private function setTrees($trees)
	{
		$db = PearDatabase::getInstance();
		foreach ($trees as $tree) {
			$skipCheckData = false;
			$result = $db->pquery('SELECT templateid FROM vtiger_trees_templates WHERE module = ?;', [$tree['base'][2]]);
			if ($result->rowCount()) {
				$templateId = $db->getSingleValue($result);
			} else {
				$db->insert('vtiger_trees_templates', [
					'name' => $tree['base'][1],
					'module' => $tree['base'][2],
					'access' => $tree['base'][3]
				]);
				$templateId = $db->getLastInsertID();
				$db->update('vtiger_field', ['fieldparams' => $templateId], '`tabid` = ? AND columnname = ?;', [$tree['base'][2], $tree['column']]);
				$skipCheckData = true;
			}
			foreach ($tree['data'] as $data) {
				if (!$skipCheckData) {
					$result = $db->pquery('SELECT templateid FROM vtiger_trees_templates_data WHERE templateid = ? AND `name` = ?;', [$templateId, $data[1]]);
					if ($result->rowCount()) {
						continue;
					}
				}
				$db->insert('vtiger_trees_templates_data', [
					'templateid' => $templateId,
					'name' => $data[1],
					'tree' => $data[2],
					'parenttrre' => $data[3],
					'depth' => $data[4],
					'label' => $data[5],
					'state' => $data[6],
					'icon' => $data[7]
				]);
			}
		}
	}

	private function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => ['add', 'Key_name'], 'name' => 'profile2utility_profileid', 'table' => 'vtiger_profile2utility', 'sql' => "ALTER TABLE `vtiger_profile2utility` 
						ADD KEY `profile2utility_profileid`(`profileid`);"],
					['type' => ['remove', 'Key_name'], 'name' => 'profile2utility_profileid_tabid_activityid_idx', 'table' => 'vtiger_profile2utility', 'sql' => "ALTER TABLE `vtiger_profile2utility` 
						DROP KEY `profile2utility_profileid_tabid_activityid_idx`;"],
					['type' => ['add', 'Key_name'], 'name' => 'profile2utility_tabid_activityid_idx', 'table' => 'vtiger_profile2utility', 'sql' => "ALTER TABLE `vtiger_profile2utility` 
						ADD KEY `profile2utility_tabid_activityid_idx`(`tabid`,`activityid`) ; "],
					['type' => ['add', 'Key_name'], 'name' => 'turn_off', 'table' => 'vtiger_entityname', 'sql' => "ALTER TABLE `vtiger_entityname` 
						CHANGE `modulename` `modulename` varchar(25) NOT NULL after `tabid` , 
						CHANGE `tablename` `tablename` varchar(50) NOT NULL after `modulename` , 
						CHANGE `fieldname` `fieldname` varchar(100) NOT NULL after `tablename` , 
						CHANGE `entityidfield` `entityidfield` varchar(30) NOT NULL after `fieldname` , 
						CHANGE `entityidcolumn` `entityidcolumn` varchar(30) NOT NULL after `entityidfield` , 
						CHANGE `turn_off` `turn_off` tinyint(1) unsigned NOT NULL DEFAULT 1 after `searchcolumn` , 
						CHANGE `sequence` `sequence` smallint(3) unsigned NOT NULL DEFAULT 0 after `turn_off` , 
						DROP KEY `entityname_tabid_idx` , 
						ADD KEY `turn_off`(`turn_off`) ;"],
					['type' => ['change', 'Type'], 'name' => 'userid', 'validType' => 'smallint', 'table' => 'u_yf_crmentity_showners', 'sql' => "ALTER TABLE `u_yf_crmentity_showners` 
						CHANGE `userid` `userid` smallint(11) unsigned   NOT NULL after `crmid` ;"],
					['type' => ['change', 'Type'], 'name' => 'eventhandler_id', 'validType' => 'smallint', 'table' => 'vtiger_eventhandlers', 'sql' => "ALTER TABLE `vtiger_eventhandlers` 
						CHANGE `eventhandler_id` `eventhandler_id` smallint(11) unsigned   NOT NULL auto_increment first , 
						CHANGE `is_active` `is_active` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `cond` ;"],
					['type' => ['add', 'Key_name'], 'name' => 'tabid_3', 'table' => 'vtiger_field', 'sql' => "ALTER TABLE `vtiger_field` 
						ADD KEY `tabid_3`(`tabid`,`block`) ;"],
					['type' => ['add'], 'name' => 'one_pdf', 'table' => 'a_yf_pdf', 'sql' => "ALTER TABLE `a_yf_pdf` ADD COLUMN `one_pdf` tinyint(1) NULL;"],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setAlterTables($data)
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

	private function getHandlerData($index)
	{
		$handlers = [];
		switch ($index) {
			case 1:
				$handlers = [
					['type' => 'remove', 'data' => ['Assets_Renewal_Handler']],
					['type' => 'remove', 'data' => ['OSSSoldServices_Renewal_Handler']],
				];
				break;
			default:
				break;
		}
		return $handlers;
	}

	private function handler($handlers = [])
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ($handlers) {
			$db = \PearDatabase::getInstance();
			$em = new VTEventsManager($db);
			foreach ($handlers as $handler) {
				if (empty($handler)) {
					continue;
				}
				$handlerData = $handler['data'];
				if ($handler['type'] == 'add') {
					$em->registerHandler($handlerData[0], $handlerData[1], $handlerData[2], $handlerData[3], $handlerData[5]);
				} elseif ($handler['type'] == 'remove') {
					$em->unregisterHandler($handlerData[0]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private function getCronData($index)
	{
		$crons = [];
		switch ($index) {
			case 1:
				$crons = [
					['type' => 'add', 'data' => ['UpdaterCoordinates', 'modules/OpenStreetMap/cron/UpdaterCoordinates.php', '60', null, null, '0', 'OpenStreetMap', '22', '']],
					['type' => 'add', 'data' => ['UpdaterRecordsCoordinates', 'modules/OpenStreetMap/cron/UpdaterRecordsCoordinates.php', '300', null, null, '0', 'OpenStreetMap', '23', '']],
					['type' => 'add', 'data' => ['LBL_ADDRESS_BOOCK', 'cron/AddressBoock.php', '86400', null, null, '0', 'Vtiger', '24', '']],
				];
				break;
			default:
				break;
		}
		return $crons;
	}

	private function cron($crons = [])
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = \PearDatabase::getInstance();
		if ($crons) {
			foreach ($crons as $cron) {
				if (empty($cron)) {
					continue;
				}
				$cronData = $cron['data'];
				$result = $db->pquery('SELECT 1 FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;', [$cronData[0], $cronData[1]]);
				if ($db->getRowCount($result) == 0 && $cron['type'] == 'add') {
					\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
					$this->cron[] = $cronData[0];
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					['type' => 'add', 'name' => 'u_yf_openstreetmap_address_updater', 'sql' => '`u_yf_openstreetmap_address_updater` (`crmid` int(19) DEFAULT NULL)'],
					['type' => 'remove', 'name' => 'u_yf_openstreetmap'],
					['type' => 'add', 'name' => 'u_yf_openstreetmap', 'sql' => '`u_yf_openstreetmap` (
						`crmid` int(19) unsigned NOT NULL,
						`type` char(1) NOT NULL,
						`lat` decimal(10,7) DEFAULT NULL,
						`lon` decimal(10,7) DEFAULT NULL,
						KEY `u_yf_openstreetmap_lat_lon` (`lat`,`lon`),
						KEY `crmid_type` (`crmid`,`type`)
					  )'],
					['type' => 'remove', 'name' => 'u_yf_mail_autologin'],
					['type' => 'add', 'name' => 'u_yf_mail_autologin', 'sql' => '`u_yf_mail_autologin` (
						`ruid` smallint(11) unsigned NOT NULL,
						`key` varchar(50) NOT NULL,
						`cuid` smallint(11) unsigned NOT NULL,
						`params` text NOT NULL,
						KEY `ruid` (`ruid`),
						KEY `cuid` (`cuid`),
						KEY `key` (`key`)
					  )'],
					['type' => 'add', 'name' => 'u_yf_openstreetmap_record_updater', 'sql' => '`u_yf_openstreetmap_record_updater` (
						`crmid` int(19) NOT NULL,
						`type` char(1) NOT NULL,
						`address` text NOT NULL,
						KEY `crmid` (`crmid`,`type`)
					  )'],
					['type' => 'add', 'name' => 's_yf_mail_relation_updater', 'sql' => '`s_yf_mail_relation_updater` (
						`tabid` smallint(11) unsigned NOT NULL,
						`crmid` int(19) unsigned NOT NULL,
						KEY `tabid` (`tabid`)
					  )'],
					['type' => 'add', 'name' => 'u_yf_mail_address_boock', 'sql' => '`u_yf_mail_address_boock` (
						`id` int(19) NOT NULL,
						`email` varchar(100) NOT NULL,
						`name` varchar(255) NOT NULL,
						`users` text NOT NULL,
						PRIMARY KEY (`id`),
						KEY `email` (`email`,`name`),
						CONSTRAINT `u_yf_mail_address_boock_ibfk_1` FOREIGN KEY (`id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
					  )'],
					['type' => 'remove', 'name' => 'vtiger_accountbookmails'],
					['type' => 'remove', 'name' => 'vtiger_contactsbookmails'],
					['type' => 'remove', 'name' => 'vtiger_leadbookmails'],
					['type' => 'remove', 'name' => 'vtiger_ossemployeesbookmails'],
					['type' => 'remove', 'name' => 'vtiger_vendorbookmails'],
					['type' => 'remove', 'name' => 'vtiger_mobile_alerts'],
					['type' => 'add', 'name' => 'u_yf_openstreetmap_cache', 'sql' => '`u_yf_openstreetmap_cache` (
						`user_id` int(19) unsigned NOT NULL,
						`module_name` varchar(50) NOT NULL,
						`crmids` int(19) unsigned NOT NULL,
						KEY `u_yf_openstreetmap_cache_user_id_module_name_idx` (`user_id`,`module_name`)
					  )'],
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
			if (isset($table['beforeAction']) && $this->getExecuteQuery($table['beforeAction']['checkAction']) === true) {
				$db->query($table['beforeAction']['actions']);
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

	private function getExecuteQuery($data)
	{
		$db = \PearDatabase::getInstance();
		list($name, $table, $column) = $data;
		$result = '';
		switch ($name) {
			case 'checkColumnExists':
				if (\vtlib\Utils::CheckTable($table)) {
					$resultQ = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column';");
					$result = (bool) $resultQ->rowCount();
				}
				break;
			default:
				break;
		}
		return $result;
	}

	private function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$actions = [
					['type' => 'add', 'name' => 'CreateDashboardChartFilter'],
					['type' => 'add', 'name' => 'CreateCustomFilter', 'sql' => "SELECT tabid FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name NOT IN ('ModComments','Events','Emails','');"],
					['type' => 'add', 'name' => 'ReloadRelationRecord', 'tabsData' => [\vtlib\Functions::getModuleId('OSSMailView')]],
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	private function actionMapp($actions)
	{
		$log = \LoggerManager::getInstance();
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
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private function getMax($table, $field, $filter = '')
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		return $id;
	}

	private function addModule($module)
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $module . ') method ...');
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
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private function postInstalModule($moduleName)
	{
		
	}

	private function getPicklistsToAction($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'OSSMailView' => [
						['name' => 'ossmailview_sendtype', 'uitype' => '15', 'add_values' => ['Internal'], 'remove_values' => ['Spam', 'Trash']]
					],
					'Assets' => [
						['name' => 'assets_renew', 'uitype' => '15', 'add_values' => ['PLL_NOT_APPLICABLE_VERIFICATION', 'PLL_RENEWED_VERIFICATION', 'PLL_NOT_RENEWED_VERIFICATION'], 'remove_values' => ['PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE']]
					],
					'OSSSoldServices' => [
						['name' => 'osssoldservices_renew', 'uitype' => '15', 'add_values' => ['PLL_NOT_APPLICABLE_VERIFICATION', 'PLL_RENEWED_VERIFICATION', 'PLL_NOT_RENEWED_VERIFICATION'], 'remove_values' => ['PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE']]
					]
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function picklists($addPicklists)
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = \PearDatabase::getInstance();

		$rolesSelected = [];
		if (empty($this->roles)) {
			$roleRecordList = Settings_Roles_Record_Model::getAll();
			$rolesSelected = array_keys($roleRecordList);
			$this->roles = $rolesSelected;
		} else {
			$rolesSelected = $this->roles;
		}

		foreach ($addPicklists as $moduleName => $piscklists) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if (!$moduleModel)
				continue;
			foreach ($piscklists as $piscklist) {
				$fieldModel = Settings_Picklist_Field_Model::getInstance($piscklist['name'], $moduleModel);
				if (!$fieldModel)
					continue;
				$pickListValues = Vtiger_Util_Helper::getPickListValues($piscklist['name']);
				foreach ($piscklist['add_values'] as $newValue) {
					if (!in_array($newValue, $pickListValues)) {
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
					}
				}
				foreach ($piscklist['remove_values'] as $newValue) {
					if (!in_array($newValue, $pickListValues))
						continue;
					if ($piscklist['uitype'] != '16') {
						$deletePicklistValueId = self::getPicklistId($piscklist['name'], $newValue);
						if ($deletePicklistValueId)
							$db->delete('vtiger_role2picklist', 'picklistvalueid = ?', [$deletePicklistValueId]);
					}
					$db->delete('vtiger_' . $piscklist['name'], $piscklist['name'] . ' = ? ', [$newValue]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private static function getPicklistId($fieldName, $value)
	{
		if (\vtlib\Utils::CheckTable('vtiger_' . $fieldName)) {
			$db = PearDatabase::getInstance();
			$sql = 'SELECT picklist_valueid FROM vtiger_' . $fieldName . ' WHERE ' . $fieldName . ' = ? ;';
			$result = $db->pquery($sql, [$value]);
			if ($db->getRowCount($result) > 0) {
				return $db->getSingleValue($result);
			}
		}
		return false;
	}

	private function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['92', '2381', 'category', 'u_yf_partners', '1', '302', 'category', 'FL_CATEGORY', '1', '2', '', '100', '7', '299', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '0', NULL, "varchar(255) DEFAULT '' ", 'LBL_PARTNERS_INFORMATION', [], [], 'Partners'],
					['61', '2382', 'secondary_phone', 'vtiger_ossemployees', '1', '11', 'secondary_phone', 'FL_SECONDARY_PHONE', '1', '2', '', '100', '5', '152', '1', 'V~O', '1', null, 'BAS', '1', '', '0', '', null, "varchar(25)", 'LBL_CONTACTS', [], [], 'OSSEmployees'],
					['61', '2383', 'position', 'vtiger_ossemployees', '1', '1', 'position', 'FL_POSITION', '1', '2', '', '100', '17', '151', '1', 'V~O', '1', null, 'BAS', '1', '', '0', '', null, "varchar(255)", 'LBL_INFORMATION', [], [], 'OSSEmployees'],
					['61', '2384', 'rbh', 'vtiger_ossemployees', '1', '71', 'rbh', 'FL_RBH', '1', '2', '', '100', '18', '151', '1', 'N~O', '1', null, 'BAS', '1', '', '0', '', null, 'decimal(25,8)', 'LBL_INFORMATION', [], [], 'OSSEmployees'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setFields($fields)
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (!\vtlib\Functions::getModuleId($field[28]) || self::checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$log->debug(__CLASS__ . '::' . __METHOD__ . ' addField - ' . print_r($field[2], true));
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
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	private static function checkFieldExists($moduleName, $column, $table)
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

	private function getFieldsToRemove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$db = \PearDatabase::getInstance();
				$result = $db->pquery('SELECT columnname FROM vtiger_field WHERE tablename = ?;', ['u_yf_openstreetmap']);
				$fieldsByTable = $db->getArrayColumn($result);
				$fields = [
					'u_yf_openstreetmap' => $fieldsByTable
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function removeFields($fields)
	{
		$log = \LoggerManager::getInstance();
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		foreach ($fields as $tableName => $columnsName) {
			if (empty($columnsName) || !\vtlib\Utils::CheckTable($tableName)) {
				continue;
			}
			foreach ($columnsName as $columnName) {
				$result = $db->pquery("SELECT fieldid FROM vtiger_field WHERE columnname = ? AND tablename = ?;", [$columnName, $tableName]);
				if ($id = $db->getSingleValue($result)) {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					try {
						\vtlib\Profile::deleteForField($fieldInstance);
						$db->delete('vtiger_field', 'fieldid = ?', [$id]);
						$db->delete('vtiger_fieldmodulerel', 'fieldid = ?', [$id]);
					} catch (Exception $e) {
						$log->debug("ERROR " . __CLASS__ . "::" . __METHOD__ . ": code " . $e->getCode() . " message " . $e->getMessage());
					}
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}
}
