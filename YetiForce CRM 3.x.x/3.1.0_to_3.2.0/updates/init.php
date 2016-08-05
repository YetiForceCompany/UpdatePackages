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
		'libraries/timelineJS3',
		'config/modules/ModComments.php',
		'modules/ModComments/actions/TimelineAjax.php',
		'libraries/Smarty/libs/sysplugins/smarty_config_source.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_config.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_function_call_handler.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_get_include_path.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_utility.php',
		'layouts/basic/modules/Vtiger/RecentActivitiesTimeLine_1.tpl',
		'layouts/basic/modules/IGDN',
		'modules/IGDN/actions/',
		'modules/IGDN/models/DetailView.php',
		'modules/IGDN/views/EditStatus.php',
		'layouts/basic/modules/IGDNC',
		'modules/IGDNC/actions/',
		'modules/IGDNC/models/DetailView.php',
		'modules/IGDNC/views/EditStatus.php',
		'layouts/basic/modules/IIDN',
		'modules/IIDN/actions/',
		'modules/IIDN/models/DetailView.php',
		'modules/IIDN/views/EditStatus.php',
		'layouts/basic/modules/IPreOrder',
		'modules/IPreOrder/actions/',
		'modules/IPreOrder/models/DetailView.php',
		'modules/IPreOrder/views/EditStatus.php',
		'layouts/basic/modules/ISTDN',
		'modules/ISTDN/actions/',
		'modules/ISTDN/models/DetailView.php',
		'modules/ISTDN/views/EditStatus.php',
		'layouts/basic/modules/ISTRN',
		'modules/ISTRN/actions/',
		'modules/ISTRN/models/DetailView.php',
		'modules/ISTRN/views/EditStatus.php',
		'layouts/basic/modules/Vtiger/CommentModal.tpl',
		'languages/de_de/NewOrders.php',
		'languages/en_us/NewOrders.php',
		'languages/fr_fr/NewOrders.php',
		'languages/pl_pl/NewOrders.php',
		'languages/pt_br/NewOrders.php',
		'languages/ru_ru/NewOrders.php',
		'layouts/basic/modules/Settings/Vtiger/Announcement.tpl',
		'layouts/basic/modules/Settings/Vtiger/resources/Announcement.js',
		'layouts/basic/modules/Settings/Vtiger/resources/Announcement.min.js',
		'layouts/basic/skins/images/btnAnnounce.png',
		'layouts/basic/skins/images/btnAnnounceOff.png',
		'modules/NewOrders',
		'modules/Settings/Vtiger/actions/AnnouncementSaveAjax.php',
		'modules/Settings/Vtiger/models/Announcement.php',
		'modules/Settings/Vtiger/views/AnnouncementEdit.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_filter_handler.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_write_file.php',
		'layouts/basic/modules/OSSMail/resources/OSSMailBoxInfo.css',
		'layouts/basic/modules/Contacts/PopupSearchActions.tpl',
		'layouts/basic/modules/OSSPasswords/ViewRelatedList.tpl',
		'user_privileges/MultiOwner.php',
		'layouts/basic/modules/Reports/resources/Horizontalbarchart.min.js',
		'layouts/basic/modules/Reports/resources/Linechart.js',
		'layouts/basic/modules/Reports/resources/Linechart.min.js',
		'layouts/basic/modules/Reports/resources/Piechart.js',
		'layouts/basic/modules/Reports/resources/Piechart.min.js',
		'layouts/basic/modules/Reports/resources/Verticalbarchart.js',
		'layouts/basic/modules/Reports/resources/Verticalbarchart.min.js',
		'libraries/nusoap/changelog',
		'include/ComboUtil.php',
		'modules/OSSMailScanner/models/BaseScannerAction.php',
		'include/Zend',
		'libraries/jquery/jquery.sparkline.min.js',
		'layouts/basic/skins/twilight/style.min.css',
		'modules/Vtiger/RecordLabelUpdater.php',
		'layouts/basic/skins/twilight/images',
		'layouts/basic/modules/Reports/resources/ChartDetail.js',
		'modules/KnowledgeBase/views/Popup.php',
		'libraries/chartjs',
		'libraries/iavupload',
		'libraries/jquery/d3js',
		'modules/Settings/WidgetsManagement/actions/AddRss.php',
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
		$this->workflowTask($this->getWorkflowTask(1));
		$this->setTablesScheme($this->getTablesAction(1));
		$this->setAlterTables($this->getAlterTables(1));
		$this->setRecords($this->getRecords(1));
		$this->addCron([['LBL_SEND_NOTIFICATIONS', 'modules/Home/cron/Notifications.php', '900', NULL, NULL, '1', 'Home', '19', '']]);
		$this->actionMapp($this->getActionMapp(1));
		$this->setRelations($this->getRelations(1));
		$this->addHandler([['vtiger.entity.aftersave.final', 'modules/Accounts/handlers/SaveChanges.php', 'SaveChanges', '', '1', '[]']]);
		$this->setLink($this->getLink(1));
		$this->updatePack();
		$this->updateSettingMenu();
		$this->updateConfigurationFiles();
	}

	function postupdate()
	{
		$db = PearDatabase::getInstance();
		$siteUrl = vglobal('site_URL');
		define('ROOT_DIRECTORY', vglobal('root_directory'));
//		if ($this->updateLabelsByModule) {
//			Vtiger_Cache::set('module', $this->updateLabelsByModule, NULL);
//			Settings_Search_Module_Model::UpdateLabels(['tabid' => $this->updateLabelsByModule]);
//		}
		$menuRecordModel = new Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		$dirName = 'cache/updates';
		$result = true;
		$modulenode = $this->modulenode;
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
		Vtiger_Functions::recurseDelete($dirName);
		Vtiger_Functions::recurseDelete('cache/templates_c');
		if (headers_sent()) {
			die('<div class="well pushDown">System update completed: <a class="btn btn-success" href="' . $siteUrl . '">Return to the homepage</a></div>');
		} else {
			exit(header('Location: ' . $siteUrl, true, 301));
		}
	}

	public function getConfigurations()
	{
		return [
			['name' => 'config/config.inc.php', 'conditions' => [
					['type' => 'remove', 'search' => 'gsAmountResponse'],
					['type' => 'remove', 'search' => 'gsMinLength'],
					['type' => 'remove', 'search' => 'autocomplete global search'],
					['type' => 'remove', 'search' => 'gsAutocomplete'],
					['type' => 'remove', 'search' => 'Maximum number of displayed search results'],
					['type' => 'remove', 'search' => 'max_number_search_result'],
					['type' => 'remove', 'search' => 'root directory path'],
					['type' => 'remove', 'search' => '$root_directory'],
					['type' => 'remove', 'search' => 'full path to include directory including the trailing slash'],
					['type' => 'remove', 'search' => 'Change of logs directory with PHP errors'],
					['type' => 'remove', 'search' => 'HELPDESK_SUPPORT_EMAIL_REPLY_ID'],
					['type' => 'update', 'search' => '$HELPDESK_SUPPORT_EMAIL_ID', 'replace' => ['HELPDESK_SUPPORT_EMAIL_ID', 'HELPDESK_SUPPORT_EMAIL_REPLY']],
					['type' => 'add', 'search' => 'isVisibleLogoInFooter', 'checkInContents' => 'isVisibleUserInfoFooter', 'value' => "
// Show information about logged user in footer
\$isVisibleUserInfoFooter = true;"],
				]
			],
			['name' => 'config/modules/Assets.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_FIELD_IN_MODAL', 'addingType' => 'before', 'value' => "	'SHOW_FIELD_IN_MODAL' => [],
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_RELATION_IN_MODAL', 'addingType' => 'before', 'value' => "	'SHOW_RELATION_IN_MODAL' => ['relationField' => 'parent_id', 'module' => 'Accounts', 'relatedModule' => 'FInvoice'],
"],
				]
			],
			['name' => 'config/modules/Calendar.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SEND_REMINDER_INVITATION', 'addingType' => 'before', 'value' => "	// Send mail notification to participants
	'SEND_REMINDER_INVITATION' => true, // Boolean
"],
				]
			],
			['name' => 'config/modules/KnowledgeBase.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'allowedFileTypes', 'addingType' => 'before', 'value' => "	// Allowed types of files
	'allowedFileTypes' => ['img', 'audio', 'video'],
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'rename', 'addingType' => 'before', 'value' => "	// If 1 and filename exists, RENAME file, adding \"_NR\" to the end of filename (name_1.ext, name_2.ext, ..)
	// If 0, will OVERWRITE the existing file
	'rename' => 1,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'fileTypeSettings', 'addingType' => 'before', 'value' => "	'fileTypeSettings' => [
		// Image settings
		'img' => [
			// Path to uploaded images
			'dir' => '/storage/KnowledgeBase/Img/',
			// Maximum file size, in KiloBytes (2 MB)
			'maxsize' => 2000,
			// Maximum allowed width, in pixels
			'maxwidth' => 900,
			// Maximum allowed height, in pixels
			'maxheight' => 800,
			// Minimum allowed width, in pixels
			'minwidth' => 10,
			// Minimum allowed height, in pixels
			'minheight' => 10,
			// Allowed extensions
			'type' => ['bmp', 'gif', 'jpg', 'jpe', 'png']
		],
		// Audio settings
		'audio' => [
			// Path to uploaded audio
			'dir' => '/storage/KnowledgeBase/Audio/',
			// Maximum file size, in KiloBytes (20 MB)
			'maxsize' => 20000,
			// Allowed extensions
			'type' => ['mp3', 'ogg', 'wav']
		],
		// Video settings
		'video' => [
			// Path to uploaded videos
			'dir' => '/storage/KnowledgeBase/Video/',
			// Maximum file size, in KiloBytes (20 MB)
			'maxsize' => 20000, 
			// Allowed extensions
			'type' => ['mp4'],
			'tagclass' => 'responsiveVideo'
		],
	],
"],
				]
			],
			['name' => 'config/performance.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER', 'addingType' => 'before', 'value' => "	// In how many records should the global search permissions be updated in cron
	'CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER' => 1000,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'OWNER_MINIMUM_INPUT_LENGTH', 'addingType' => 'before', 'value' => "	// Minimum number of characters to search for record owner
	'OWNER_MINIMUM_INPUT_LENGTH' => 2,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'MAX_NUMBER_EXPORT_RECORDS', 'addingType' => 'before', 'value' => "	// Max number of exported records
	'MAX_NUMBER_EXPORT_RECORDS' => 500,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'SEARCH_OWNERS_BY_AJAX', 'addingType' => 'before', 'value' => "	// Search owners by AJAX. We recommend selecting the \"true\" value if there are numerous users in the system.
	'SEARCH_OWNERS_BY_AJAX' => false,
"],
					['type' => 'remove', 'search' => 'SORT_SEARCH_RESULTS'],
					['type' => 'remove', 'search' => 'All search results will be sorted'],
				]
			],
			['name' => 'config/secret_keys.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'encryptionMethod', 'addingType' => 'before', 'value' => "	// encryption method
	'encryptionMethod' => '',
"],
					['type' => 'add', 'search' => 'backupPassword', 'checkInContents' => 'encryptionPass', 'trim' => 'rtrim', 'value' => ",
	// Key to encrypt passwords, changing the key results in the loss of all encrypted data.
	'encryptionPass' => 'yeti',
"],
				]
			],
			['name' => 'modules/OSSMail/roundcube/config/config.inc.php', 'conditions' => [
					['type' => 'update', 'search' => "config['default_host']", 'replace' => ["'ssl://smtp.gmail.com' => 'ssl://smtp.gmail.com',", "'ssl://imap.gmail.com' => 'ssl://imap.gmail.com',"]],
					['type' => 'update', 'search' => 'root_directory', 'replace' => ['$root_directory', "ROOT_DIRECTORY . DIRECTORY_SEPARATOR"]],
				]
			],
			['name' => '.htaccess', 'conditions' => [
					['type' => 'add', 'search' => "RewriteEngine", 'value' => "	RewriteRule ^favicon.ico layouts/basic/skins/images/favicon.ico [L,NC]
", 'checkInContents' => 'favicon.ico'],
					['type' => 'update', 'search' => "session.gc_maxlifetime", 'value' => "	# 86400 = 3600*24
	php_value	session.gc_maxlifetime		86400 
	# 1440 = 60*24
	php_value	session.cache_expire		1440
"],
					['type' => 'add', 'search' => "session.gc_probability", 'value' => "
	php_value	session.save_path			cache/session
", 'checkInContents' => 'session.save_path'],
					['type' => 'add', 'search' => '<IfModule mod_autoindex.c>', 'checkInContents' => 'RedirectMatch', 'value' => "RedirectMatch 403 (?i).*\.log$

", 'addingType' => 'before'],
				]
			],
			['name' => 'config/modules/ModTracker.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'TEASER_TEXT_LENGTH', 'addingType' => 'before', 'value' => "	// Maximum length of text, only applies to text fields
	'TEASER_TEXT_LENGTH' => 400,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'UNREVIEWED_COUNT', 'addingType' => 'before', 'value' => "	// Displays the number of unreviewed changes in record.
	'UNREVIEWED_COUNT' => true,
"],
				]
			],
		];
	}

	public function updateConfigurationFiles()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$rootDirectory = getcwd();
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . DIRECTORY_SEPARATOR . $config['name'];
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
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function getTrimValue($trim, $value)
	{
		switch ($trim) {
			case 'rtrim':
				$value = rtrim($value);
				break;
			case 'ltrim':
				$value = ltrim($value);
				break;
			case 'trim':
				$value = trim($value);
				break;
			default:
				break;
		}
		return $value;
	}

	public function addModComments($moduleName)
	{
		$tabid = Vtiger_Functions::getModuleId($moduleName);
		if (!$moduleName || !$tabid) {
			return false;
		}
		$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
		if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
			include_once 'modules/ModComments/ModComments.php';
			if (class_exists('ModComments'))
				ModComments::addWidgetTo([$moduleName]);
		}
	}

	public function getFieldsToMove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'neworders_no' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'modifiedtime' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'smownerid' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'createdtime' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function move($data)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$move = [];
		$removeBlocks = [];
		foreach ($data as $columName => $info) {
			if (empty($info)) {
				continue;
			}
			$tabId = getTabid($info['moduleName']);
			if ($info['removeBlock']) {
				$removeBlocks[$tabId][] = $info['fromBlock'];
			}
			$result = $db->pquery('SELECT fieldid FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE columnname = ? AND vtiger_field.tabid = ? AND vtiger_blocks.`blocklabel` = ?;', [$columName, $tabId, $info['fromBlock']]);
			$id = $db->getSingleValue($result);
			if (empty($id)) {
				continue;
			}
			if (!empty($info['moveFor'])) {
				$result = $db->pquery('SELECT sequence,block FROM vtiger_field WHERE columnname = ? AND tabid = ?;', [$info['moveFor'], $tabId]);
			} elseif (!empty($info['toBlock'])) {
				$result = $db->pquery('SELECT MAX(vtiger_field.sequence) AS sequence,vtiger_blocks.`blockid` as block FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_field.tabid = ?', [$info['toBlock'], $tabId]);
				$checkBlock = $db->query_result($result, 0, 'block');
				if (!$checkBlock) {
					$result = $db->pquery('SELECT vtiger_blocks.sequence = 0 AS sequence,  vtiger_blocks.`blockid` AS block FROM vtiger_blocks WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_blocks.tabid = ?', [$info['toBlock'], $tabId]);
				}
			}
			if ($result) {
				$row = $db->getRow($result);
				$seq = $row['sequence'];
				$block = $row['block'];
			}
			if (!empty($block)) {
				$move[] = ['fieldid' => $id, 'sequence' => (int) $seq + 1, 'block' => $block];
			}
		}

		//This will update the fields sequence for the updated blocks
		if ($move) {
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($move);
		}
		if ($removeBlocks) {
//			$this->deleteBlocks($removeBlocks);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function addHandler($addHandler = [])
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($addHandler) {
			$em = new VTEventsManager($db);
			foreach ($addHandler as $handler) {
				$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function addCron($addCrons = [])
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($addCrons) {
			foreach ($addCrons as $cron) {
				$result = $db->pquery('SELECT * FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;', [$cron[0], $cron[1]]);
				if ($db->getRowCount($result) == 0) {
					Vtiger_Cron::register($cron[0], $cron[1], $cron[2], $cron[6], $cron[5], 0, $cron[8]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function setRecords($data)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		foreach ($data as $record) {
			if (empty($record)) {
				continue;
			}
			$result = $db->query($record['sql']);
			$num = $db->getRowCount($result);
			if (!$num && $record['type'] == 'add') {
				$recordModel = Vtiger_Record_Model::getCleanInstance($record['moduleName']);
				foreach ($record['data'] as $name => $value) {
					$recordModel->set($name, $value);
				}
				$recordModel->save();
				if ('OSSMailTemplates' == $record['moduleName'] && isset($record['data']['sysname']))
					$db->update('vtiger_ossmailtemplates', ['sysname' => 'SendNotificationsViaMail'], '`ossmailtemplatesid` = ?;', [$recordModel->getId()]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getRecords($index)
	{
		$tasks = [];
		switch ($index) {
			case 1:
				$tasks = [
					['type' => 'add', 'moduleName' => 'OSSMailTemplates', 'sql' => 'SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE sysname = "SendNotificationsViaMail";',
						'data' => [
							'name' => 'Send notifications',
							'sysname' => 'SendNotificationsViaMail',
							'oss_module_list' => 'System',
							'subject' => 'Notifications #s#CurrentDate#sEnd#',
							'content' => '#s#Notifications#sEnd#',
							'ossmailtemplates_type' => 'PLL_MODULE']],
				];
				break;
			default:
				break;
		}
		return $tasks;
	}

	function getWorkflowTask($index)
	{
		$tasks = [];
		switch ($index) {
			case 1:
				$tasks = [
					['moduleName' => 'Leads', 'summary' => 'Weryfikacja danych', 'changes' => ['summary' => 'Data verification', 'todo' => 'Data verification', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Zapoznanie się z aktualnościami na stronie', 'changes' => ['summary' => 'Red news on the website', 'todo' => 'Red news on the website', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Zapoznanie się z aktualnościami społecznościowymi', 'changes' => ['summary' => 'Read social networking news  ', 'todo' => 'Read social networking news  ', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Kontakt telefoniczny lub mailowy', 'changes' => ['summary' => 'Mail or call', 'todo' => 'Mail or call', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Określenie osoby decyzyjnej', 'changes' => ['summary' => 'Determine the decision maker ', 'todo' => 'Determine the decision maker ', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Prezentacja doświadczenia firmy', 'changes' => ['summary' => 'Present experience of company', 'todo' => 'Present experience of company', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Prezentacja produktów i usług', 'changes' => ['summary' => 'Present products and services', 'todo' => 'Present products and services', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Wstępna analiza potrzeb Klienta', 'changes' => ['summary' => "Preliminary analysis of the client's needs", 'todo' => "Preliminary analysis of the client's needs", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Usługi obce'", 'changes' => ['summary' => "Update: 'Outsourced services'", 'todo' => "Update: 'Outsourced services'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Produkty obce'", 'changes' => ['summary' => "Update: 'Outsourced products'", 'todo' => "Update: 'Outsourced products'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Uzupełnienie wstępnych ustaleń w systemie', 'changes' => ['summary' => 'Update preliminary agreements in the system', 'todo' => 'Update preliminary agreements in the system', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Uszczegółowienie potrzeb Klienta', 'changes' => ['summary' => "Specify client's needs", 'todo' => "Specify client's needs", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Zainteresowany usługami'", 'changes' => ['summary' => "Update information on: 'Interested in services' ", 'todo' => "Update information on: 'Interested in services' ", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Zainteresowany produktami'", 'changes' => ['summary' => "Update information on: 'Interested in products'", 'todo' => "Update information on: 'Interested in products'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
				];
				break;
			default:
				break;
		}
		return $tasks;
	}

	function workflowTask($data)
	{
		$db = PearDatabase::getInstance();
		foreach ($data as $taskData) {
			if (empty($taskData)) {
				continue;
			}
			$result = $db->pquery('SELECT task FROM `com_vtiger_workflowtasks` t INNER JOIN com_vtiger_workflows w ON w.workflow_id = t.workflow_id 
				WHERE t.summary = ? AND w.module_name = ?;', [$taskData['summary'], $taskData['moduleName']]);
			while ($task = $db->getSingleValue($result)) {
				$tm = new VTTaskManager($db);
				$task = $tm->unserializeTask($task);
				$save = false;
				foreach ($taskData['changes'] as $name => $value) {
					if (!isset($task->$name) || $task->$name != $value) {
						$task->$name = $value;
						$save = true;
					}
				}
				if ($save) {
					$tm->saveTask($task);
				}
			}
		}
	}

	public function updatePack()
	{
		$db = PearDatabase::getInstance();

		$db->update('vtiger_field', ['defaultvalue' => 'PLL_TO_APPROVAL'], '`tabid` = ? AND columnname = ?;', [getTabid('Reservations'), 'reservations_status']);
		$modules = ['ModTracker', 'Users', 'Mobile', 'Integration', 'WSAPP', 'ConfigEditor', 'FieldFormulas', 'VtigerBackup', 'CronTasks', 'Import', 'Tooltip', 'CustomerPortal', 'Home'];
		$db->pquery('DELETE p FROM vtiger_profile2tab p INNER JOIN vtiger_tab t ON t.`tabid` = p.`tabid` WHERE t.`name` IN (' . $db->generateQuestionMarks($modules) . ');', $modules);
		$result = $db->query('SHOW TABLE STATUS WHERE NAME LIKE "vtiger_neworders";');
		if ($result->rowCount()) {
			$this->renameModule();
		}
		$db->update('vtiger_account', ['active' => 1], 'parentid = ?', [0]);
		$result = $db->pquery('SELECT 1 FROM vtiger_ws_fieldtype WHERE uitype = ?;', ['309']);
		if (!$result->rowCount()) {
			$db->insert('vtiger_ws_fieldtype', ['uitype' => 309, 'fieldtype' => 'categoryMultipicklist']);
		}
		$result = $db->query('SELECT vtiger_fieldmodulerel.fieldid FROM `vtiger_fieldmodulerel` WHERE vtiger_fieldmodulerel.`fieldid` NOT IN (SELECT vtiger_field.`fieldid` FROM vtiger_field)  OR vtiger_fieldmodulerel.`fieldid` = (SELECT vtiger_field.`fieldid` FROM vtiger_field WHERE columnname = "linkto" AND tablename = "vtiger_osspasswords")');
		if ($result->rowCount()) {
			$fieldsId = $db->getArrayColumn($result);
			$db->delete('vtiger_fieldmodulerel', 'fieldid IN (' . implode(',', $fieldsId) . ')');
		}
		$db->update('vtiger_field', ['uitype' => 67], '`tabid` = ? AND columnname = ?;', [getTabid('OSSPasswords'), 'linkto']);
		$this->removeFields($this->getFieldsToRemove(1));
		$this->setTablesScheme($this->getTablesAction(3));
		$this->deleteBlocks([getTabid('HelpDesk') => ['LBL_COMMENTS']]);

		$this->addModComments('Campaigns');
		$this->setWidgetToSummary($this->getWidgetToSummary('Campaigns'));
		if (in_array('PLL_NEW_SALES', Vtiger_Util_Helper::getPickListValues('ssalesprocesses_type'))) {
			$db->update('vtiger_field', ['defaultvalue' => 'PLL_NEW_SALES'], '`tabid` = ? AND columnname = ?;', [getTabid('SSalesProcesses'), 'ssalesprocesses_type']);
		}
		$this->setAlterTables($this->getAlterTables(2));
		$this->moveColumnFromCrmentity();
		$db->update('vtiger_field', ['uitype' => 11], '`tabid` = ? AND columnname = ?;', [getTabid('Vendors'), 'phone']);
		$this->addCron([['RecordLabelUpdater', 'cron/LabelUpdater.php', '900', NULL, NULL, '1', 'Vtiger', '20', ''],
			['PrivilegesUpdater', 'cron/PrivilegesUpdater.php', '900', NULL, NULL, '1', 'Vtiger', '21', '']]);
		$db->update('vtiger_organizationdetails', ['logoname' => 'blue_yetiforce_logo.png'], '`logoname` = ? ;', ['white_logo_yetiforce.png']);
		$db->update('vtiger_field', ['fieldlabel' => 'LBL_ORGINAL_MAIL_CONTENT'], '`tabid` = ? AND columnname = ?;', [getTabid('OSSMailView'), 'orginal_mail']);
		$this->setAlterTables($this->getAlterTables(5));
		$this->setFields($this->getFields(2));
	}

	function moveColumnFromCrmentity()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$this->setTablesScheme($this->getTablesAction(4));
//		$result = $db->query('SHOW COLUMNS FROM `vtiger_crmentity` LIKE "searchlabel";');
//		if ($result->rowCount()) {
//			$query = 'INSERT INTO u_yf_crmentity_search_label (crmid, searchlabel) SELECT crmid, searchlabel FROM vtiger_crmentity;';
//			$db->query($query);
		$this->setAlterTables($this->getAlterTables(3));
//		}
//		$result = $db->query('SHOW COLUMNS FROM `vtiger_crmentity` LIKE "label";');
//		if ($result->rowCount()) {
//			$query = 'INSERT INTO u_yf_crmentity_label (crmid, label) SELECT crmid, label FROM vtiger_crmentity;';
//			$db->query($query);
		$this->setAlterTables($this->getAlterTables(4));
//		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getWidgetToSummary($moduleName)
	{
		$widgets = [];
		switch ($moduleName) {
			case 'Campaigns':
				$widgets = [
					['163', 'Campaigns', 'Updates', 'LBL_UPDATES', '1', '1', '0', '[]'],
					['162', 'Campaigns', 'RelatedModule', 'Documents', '2', '2', '0', '{"relatedmodule":"8","limit":"5","columns":"3","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}'],
					['161', 'Campaigns', 'Comments', 'ModComments', '2', '3', '0', '{"relatedmodule":"ModComments","limit":"5"}'],
					['160', 'Campaigns', 'Summary', NULL, '1', '0', '0', '[]']
				];
				break;
			default:
				break;
		}
		return $widgets;
	}

	public function setWidgetToSummary($widgets)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' (' . $module . ') method ...');
		$db = PearDatabase::getInstance();
		foreach ($widgets as $widget) {
			if (empty($widget)) {
				continue;
			}
			list($ID, $moduleName, $type, $label, $wcol, $sequence, $nomargin, $data) = $widget;
			$tabid = getTabid($moduleName);
			if ($type == 'RelatedModule') {
				$arrayData = Zend_Json::decode($data);
				$ralModule = $arrayData['relatedmodule'];
				$result = $db->pquery('SELECT 1 FROM vtiger_widgets WHERE tabid = ? AND type = ? AND `data` LIKE ?;', [$tabid, $type, '%"relatedmodule":"' . $ralModule . '"%']);
			} else {
				$result = $db->pquery('SELECT 1 FROM vtiger_widgets WHERE tabid = ? AND type = ?;', [$tabid, $type]);
			}
			if (!$db->getRowCount($result)) {
				$db->insert('vtiger_widgets', [
					'tabid' => $tabid,
					'type' => $type,
					'label' => $label,
					'wcol' => $wcol,
					'sequence' => $sequence,
					'nomargin' => $nomargin,
					'data' => $data
				]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function renameModule()
	{
		$db = PearDatabase::getInstance();
		$tabId = getTabid('NewOrders');
		$this->move($this->getFieldsToMove(1));
		$this->setFields($this->getFields(1));
		$result = $db->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE module_name = ?', ['NewOrders']);
		while ($recordId = $db->getSingleValue($result)) {
			$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$db->query('ALTER TABLE `vtiger_neworderscf` DROP FOREIGN KEY `fk_1_vtiger_neworderscf`;');
		$db->query('ALTER TABLE `vtiger_neworders` DROP KEY `vtiger_neworderscf` , DROP FOREIGN KEY `vtiger_neworderscf`;');
		$db->query('DROP TABLE IF EXISTS vtiger_announcement;');
		$db->query('RENAME TABLE vtiger_neworders TO `u_yf_announcement`;');
		$db->query('ALTER TABLE `u_yf_announcement` CHANGE `newordersid` `announcementid` INT(11) NOT NULL,
					CHANGE `neworders_no` `announcement_no` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NULL,
					ADD PRIMARY KEY (`announcementid`);');
		$db->query('RENAME TABLE vtiger_neworderscf TO `u_yf_announcementcf`;');
		$db->query('ALTER TABLE `u_yf_announcementcf` 
					CHANGE `newordersid` `announcementid` INT(11) NOT NULL, 
					DROP KEY `PRIMARY`, ADD PRIMARY KEY(`announcementid`) ;');
		$db->query('ALTER TABLE `u_yf_announcement`
					ADD CONSTRAINT `fk_1_u_yf_announcement` 
					FOREIGN KEY (`announcementid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;');
		$db->query('ALTER TABLE `u_yf_announcementcf`
					ADD CONSTRAINT `fk_1_u_yf_announcementcf` 
					FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE ;');
		$db->update('vtiger_blocks', ['blocklabel' => 'LBL_ANNOUNCEMENTS_INFORMATION'], 'blocklabel = ? AND tabid = ?', ['LBL_NEWORDERS_INFORMATION', $tabId]);
		$db->update('vtiger_customview', ['entitytype' => 'Announcements'], 'entitytype = ?', ['NewOrders']);
		$db->delete('vtiger_customview', 'entitytype = ? AND presence <> ?', ['Announcements', 0]);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'u_yf_announcement:subject:subject:Announcements_LBL_SUBJECT:V'], 'columnname = ?', ['vtiger_neworders:subject:subject:NewOrders_LBL_SUBJECT:V']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:smownerid:assigned_user_id:Announcements_Assigned_To:V'], 'columnname = ?', ['vtiger_crmentity:smownerid:assigned_user_id:NewOrders_Assigned_To:V']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:createdtime:createdtime:Announcements_Created_Time:DT'], 'columnname = ?', ['vtiger_crmentity:createdtime:createdtime:NewOrders_Created_Time:DT']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:modifiedtime:modifiedtime:Announcements_Modified_Time:DT'], 'columnname = ?', ['vtiger_crmentity:modifiedtime:modifiedtime:NewOrders_Modified_Time:DT']);
		$db->delete('vtiger_cvcolumnlist', 'columnname LIKE "%NewOrders_%"');
		$db->update('vtiger_entityname', ['modulename' => 'Announcements', 'tablename' => 'u_yf_announcement', 'entityidfield' => 'announcementid', 'entityidcolumn' => 'announcementid', 'searchcolumn' => 'subject'], 'modulename = ?', ['NewOrders']);
		$db->update('vtiger_field', ['tablename' => 'u_yf_announcement'], 'tablename = ?', ['vtiger_neworders']);
		$db->update('vtiger_field', ['columnname' => 'announcement_no', 'fieldname' => 'announcement_no'], 'columnname = ? AND tabid = ?', ['neworders_no', $tabId]);
		$db->update('vtiger_field', ['quickcreate' => 2], 'columnname = ? AND tabid = ?', ['description', $tabId]);
		$db->update('vtiger_modentity_num', ['semodule' => 'Announcements'], 'semodule = ?', ['NewOrders']);
		$db->update('vtiger_ws_entity', ['name' => 'Announcements'], 'name = ?', ['NewOrders']);
		$db->update('vtiger_crmentity', ['setype' => 'Announcements'], 'setype = ?', ['NewOrders']);
		$this->setTablesScheme($this->getTablesAction(2));
		$result = $db->pquery('SELECT dataaccessid FROM vtiger_dataaccess WHERE module_name = ?', ['NewOrders']);
		if ($result->rowCount()) {
			$ids = $db->getArrayColumn($result, 'dataaccessid');
			$db->delete('vtiger_dataaccess', 'module_name = ?', ['NewOrders']);
			$db->delete('vtiger_dataaccess_cnd', 'dataaccessid IN (' . $db->generateQuestionMarks($ids) . ')', $ids);
		}
		$db->delete('vtiger_settings_field', '`name` = ? AND linkto = ?', ['LBL_ANNOUNCEMENT', 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit']);
		$db->update('vtiger_tab', ['name' => 'Announcements', 'tablabel' => 'Announcements'], '`name` = ?', ['NewOrders']);
		$db->update('vtiger_announcementstatus', ['presence' => 0], '`announcementstatus` = ?', ['PLL_PUBLISHED']);
		$db->update('vtiger_field', ['quickcreatesequence' => '1'], '`tabid` = ? AND columnname = ?;', [$tabId, 'subject']);
		$db->update('vtiger_field', ['quickcreatesequence' => '2'], '`tabid` = ? AND columnname = ?;', [$tabId, 'smownerid']);
		$db->update('vtiger_field', ['quickcreatesequence' => '3'], '`tabid` = ? AND columnname = ?;', [$tabId, 'description']);
		$db->update('vtiger_field', ['quickcreatesequence' => '4'], '`tabid` = ? AND columnname = ?;', [$tabId, 'announcementstatus']);
		ModTracker::enableTrackingForModule($tabId);
//		$this->updateLabelsByModule = $tabId;
	}

	function getRelations($index)
	{
		$ralations = [];
		switch ($index) {
			case 1:
				$ralations = [
					['type' => 'remove', 'data' => ['229', 'Assets', 'OSSPasswords', 'get_dependents_list', '4', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['189', 'Assets', 'OSSTimeControl', 'get_dependents_list', '3', 'OSSTimeControl', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['230', 'Accounts', 'OSSPasswords', 'get_dependents_list', '11', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['233', 'Products', 'OSSPasswords', 'get_dependents_list', '16', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['234', 'Services', 'OSSPasswords', 'get_dependents_list', '12', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['235', 'HelpDesk', 'OSSPasswords', 'get_dependents_list', '12', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'remove', 'data' => ['236', 'Vendors', 'OSSPasswords', 'get_dependents_list', '6', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['229', 'Contacts', 'OSSPasswords', 'get_dependents_list', '4', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['230', 'Accounts', 'OSSPasswords', 'get_dependents_list', '11', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['233', 'Leads', 'OSSPasswords', 'get_dependents_list', '16', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['234', 'Partners', 'OSSPasswords', 'get_dependents_list', '12', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['235', 'Competition', 'OSSPasswords', 'get_dependents_list', '12', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['236', 'Vendors', 'OSSPasswords', 'get_dependents_list', '6', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['505', 'HelpDesk', 'OSSPasswords', 'get_related_list', '19', 'OSSPasswords', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['506', 'SSalesProcesses', 'OSSPasswords', 'get_related_list', '17', 'OSSPasswords', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['507', 'Project', 'OSSPasswords', 'get_related_list', '10', 'OSSPasswords', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['508', 'OSSEmployees', 'OSSPasswords', 'get_dependents_list', '9', 'OSSPasswords', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['509', 'Campaigns', 'Documents', 'get_attachments', '3', 'Documents', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'update', 'data' => ['109', 'HelpDesk', 'Services', 'get_related_list', '6', 'Services', '1', 'SELECT', '0', '0', '0']],
					['type' => 'update', 'data' => ['213', 'HelpDesk', 'Assets', 'get_related_list', '11', 'Assets', '1', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['510', 'HelpDesk', 'Products', 'get_related_list', '20', 'Products', '1', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['511', 'HelpDesk', 'OSSSoldServices', 'get_related_list', '21', 'OSSSoldServices', '1', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['512', 'OSSSoldServices', 'HelpDesk', 'get_related_list', '2', 'HelpDesk', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => [8, 'SSalesProcesses', 'OSSTimeControl', 'get_dependents_list', 18, 'OSSTimeControl', 0, 'ADD', 0, 0, 0]],
				];
				break;
			default:
				break;
		}
		return $ralations;
	}

	function setRelations($data)
	{
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $relation) {
				if (empty($relation)) {
					continue;
				}
				list($id, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment) = $relation['data'];
				$tabid = getTabid($moduleName);
				$relTabid = getTabid($relModuleName);
				$result = $db->pquery("SELECT 1 FROM `vtiger_relatedlists` WHERE tabid=? AND related_tabid = ? AND name = ? AND label = ?;", [$tabid, $relTabid, $name, $label]);
				if ($result->rowCount() == 0 && $relation['type'] == 'add') {
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
				} elseif ($result->rowCount() > 0 && $relation['type'] == 'remove') {
					$db->delete('vtiger_relatedlists', '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
				} elseif ($relation['type'] == 'update') {
					if ($result->rowCount() > 0) {
						$db->update('vtiger_relatedlists', [
							'sequence' => $sequence,
							'presence' => $presence,
							'actions' => $actions,
							'favorites' => $favorites,
							'creator_detail' => $creatorDetail,
							'relation_comment' => $relationComment], '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
					} else {
						$relation['type'] = 'add';
						$this->setRelations([$relation]);
					}
				}
			}
		}
	}

	function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$modules = Settings_PDF_Module_Model::getSupportedModules();
				$supportedModuleModels = [];
				foreach ($modules as $tabId => $moduleModel) {
					if (!in_array($moduleModel->getName(), Settings_Profiles_Module_Model::getNonVisibleModulesList()) && !in_array($moduleModel->getName(), ['SMSNotifier', 'ModComments', 'PBXManager', 'Events', 'Emails', ''])) {
						$supportedModuleModels[$tabId] = $moduleModel;
					}
				}
				$actions = [
					['type' => 'add', 'name' => 'ReceivingMailNotifications', 'tabsData' => [getTabid('Dashboard')]],
					['type' => 'add', 'name' => 'WatchingRecords', 'tabsData' => [getTabid('ModComments')]],
					['type' => 'add', 'name' => 'WatchingModule', 'tabsData' => [getTabid('ModComments')]],
					['type' => 'add', 'name' => 'ReviewingUpdates'],
					['type' => 'add', 'name' => 'ExportPdf', 'tabsData' => array_keys($supportedModuleModels)],
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
					['83', '2355', 'announcementstatus', 'vtiger_neworders', '1', '15', 'announcementstatus', 'FL_STATUS', '1', '2', 'PLL_DRAFT', '100', '2', '258', '1', 'V~M', '2', NULL, 'BAS', '1', '', '1', '', '0', "varchar(255) DEFAULT '' ", 'LBL_NEWORDERS_INFORMATION', ['PLL_DRAFT', 'PLL_FOR_ACCEPTANCE', 'PLL_PUBLISHED'], [], 'NewOrders'],
					['83', '2356', 'interval', 'vtiger_neworders', '1', '7', 'interval', 'FL_INTERVAL', '1', '2', '', '100', '3', '258', '1', 'I~O', '1', NULL, 'BAS', '1', 'Edit,Detail,QuickCreateAjax', '0', '', NULL, "smallint(5) DEFAULT NULL", 'LBL_NEWORDERS_INFORMATION', [], [], 'NewOrders'],
				];
				break;
			case 2:
				$fields = [
					['14', '2357', 'category_multipicklist', 'vtiger_products', '1', '309', 'category_multipicklist', 'LBL_CATEGORY_MULTIPICKLIST', '1', '2', NULL, '100', '31', '31', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '13', NULL, 'text', 'LBL_PRODUCT_INFORMATION', [], [], 'Products'],
					['89', '2358', 'valid_until', 'u_yf_squotes', '1', '5', 'valid_until', 'FL_VALID_UNTIL', '1', '2', '', '100', '10', '280', '1', 'D~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'date', 'LBL_SQUOTES_INFORMATION', [], [], 'SQuotes'],
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
			if (!getTabid($field[28]) || self::checkFieldExists($field[28], $field[2], $field[3])) {
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
					['type' => ['add'], 'name' => 'last_reviewed_users', 'table' => 'vtiger_modtracker_basic', 'sql' => "ALTER TABLE `vtiger_modtracker_basic` 
	ADD COLUMN `last_reviewed_users` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `status` ;"],
					['type' => ['add'], 'name' => 'color', 'table' => 'vtiger_customview', 'sql' => "ALTER TABLE `vtiger_customview` 
	ADD COLUMN `color` varchar(10)  COLLATE utf8_general_ci NULL DEFAULT '' after `sort` ;"],
					['type' => ['add'], 'name' => 'assignedmultiowner', 'table' => 'vtiger_role', 'sql' => "ALTER TABLE `vtiger_role` 
	ADD COLUMN `assignedmultiowner` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `globalsearchadv` ;"],
					['type' => ['change', 'Type'], 'name' => 'description', 'validType' => 'varbinary', 'table' => 'vtiger_settings_field', 'sql' => "ALTER TABLE `vtiger_settings_field` CHANGE `description` `description` varchar(250)  COLLATE utf8_general_ci NULL;"],
					['type' => ['change', 'Type'], 'name' => 'sourceuuid', 'validType' => 'varchar', 'table' => 'vtiger_pbxmanager', 'sql' => "ALTER TABLE `vtiger_pbxmanager` CHANGE `sourceuuid` `sourceuuid` varchar(100)  COLLATE utf8_general_ci NULL after `recordingurl` ;"],
					['type' => ['add'], 'name' => 'filetype', 'table' => 'vtiger_schedulereports', 'sql' => "ALTER TABLE `vtiger_schedulereports` ADD COLUMN `filetype` varchar(20)  COLLATE utf8_general_ci NULL;"],
				];
				break;
			case 2:
				$fields = [
					['type' => ['add'], 'name' => 'maxlengthtext', 'table' => 'vtiger_field', 'sql' => "ALTER TABLE `vtiger_field` 
	ADD COLUMN `maxlengthtext` smallint(3) unsigned   NULL DEFAULT 0 after `header_field`;"],
					['type' => ['add'], 'name' => 'maxwidthcolumn', 'table' => 'vtiger_field', 'sql' => "ALTER TABLE `vtiger_field` 
	ADD COLUMN `maxwidthcolumn` smallint(3) unsigned   NULL DEFAULT 0 after `maxlengthtext` ;"],
					['type' => ['change', 'Type'], 'validType' => 'tinyint', 'name' => 'converted', 'table' => 'vtiger_leaddetails', 'sql' => "ALTER TABLE `vtiger_leaddetails` CHANGE `converted` `converted` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `leadsource` , ADD KEY `converted`(`converted`) ;"],
					['type' => ['change', 'Type'], 'validType' => 'varchar(50)', 'name' => 'fieldname', 'table' => 'u_yf_crmentity_last_changes', 'sql' => "ALTER TABLE `u_yf_crmentity_last_changes` CHANGE `fieldname` `fieldname` varchar(50) NOT NULL after `crmid` , ADD KEY `crmid`(`crmid`,`fieldname`),ADD CONSTRAINT `u_yf_crmentity_last_changes_ibfk_1` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;"],
					['type' => ['add', 'Key_name'], 'name' => 'status', 'table' => 'vtiger_users', 'sql' => "ALTER TABLE `vtiger_users` ADD KEY `status`(`status`) ; "],
					['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'relation_id', 'table' => 'vtiger_relatedlists', 'sql' => "ALTER TABLE `vtiger_relatedlists` CHANGE `relation_id` `relation_id` smallint(19) unsigned   NOT NULL first , 
					CHANGE `tabid` `tabid` smallint(10) unsigned   NOT NULL after `relation_id` , 
					CHANGE `related_tabid` `related_tabid` smallint(10) unsigned   NOT NULL after `tabid` , 
					CHANGE `name` `name` varchar(50)  COLLATE utf8_general_ci NULL after `related_tabid` , 
					CHANGE `sequence` `sequence` tinyint(5) unsigned   NOT NULL after `name` , 
					CHANGE `label` `label` varchar(50)  COLLATE utf8_general_ci NOT NULL after `sequence` , 
					CHANGE `presence` `presence` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `label` , 
					CHANGE `favorites` `favorites` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `actions` , 
					CHANGE `creator_detail` `creator_detail` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `favorites` , 
					CHANGE `relation_comment` `relation_comment` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `creator_detail` ;"],
					['type' => ['change', 'Type'], 'validType' => 'tinyint', 'name' => 'presence', 'table' => 'vtiger_tab', 'sql' => "ALTER TABLE `vtiger_tab` CHANGE `presence` `presence` tinyint(19) unsigned   NOT NULL DEFAULT 1 after `name` , 
					CHANGE `tabsequence` `tabsequence` smallint(5)   NOT NULL DEFAULT 0 after `presence` , 
					CHANGE `modifiedby` `modifiedby` smallint(5)   NULL after `tablabel` , 
					CHANGE `customized` `customized` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `modifiedtime` , 
					CHANGE `ownedby` `ownedby` tinyint(1)   NOT NULL DEFAULT 0 after `customized` , 
					CHANGE `isentitytype` `isentitytype` tinyint(1)   NOT NULL DEFAULT 1 after `ownedby` , 
					CHANGE `coloractive` `coloractive` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `color` , 
					CHANGE `type` `type` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `coloractive` , 
					ADD KEY `presence`(`presence`) ;"],
					['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'smcreatorid', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` CHANGE `smcreatorid` `smcreatorid` smallint(5) unsigned   NOT NULL DEFAULT 0 after `crmid` , 
					CHANGE `smownerid` `smownerid` smallint(5) unsigned   NOT NULL DEFAULT 0 after `smcreatorid` , 
					CHANGE `modifiedby` `modifiedby` smallint(5) unsigned   NOT NULL DEFAULT 0 after `shownerid` , 
					CHANGE `version` `version` int(19) unsigned   NOT NULL DEFAULT 0 after `status` , 
					CHANGE `presence` `presence` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `version` , 
					CHANGE `deleted` `deleted` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `presence` , 
					CHANGE `was_read` `was_read` tinyint(1)   NULL DEFAULT 0 after `deleted`, 
					ADD KEY `crmid_2`(`crmid`,`setype`);"],
				];
				break;
			case 3:
				$fields = [
					['type' => ['remove'], 'name' => 'searchlabel', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` DROP COLUMN `searchlabel` , DROP KEY `searchlabel`,DROP KEY `setype`, ADD KEY `setype`(`setype`,`deleted`), ADD KEY `setype_2`(`setype`);"],
				];
				break;
			case 4:
				$fields = [
					['type' => ['remove'], 'name' => 'label', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity`	DROP COLUMN `label` , DROP KEY `vtiger_crmentity_labelidx` ;"],
				];
				break;
			case 5:
				$fields = [
					['type' => ['remove'], 'name' => 'width', 'table' => 'a_yf_notification_type', 'sql' => "ALTER TABLE `a_yf_notification_type` DROP COLUMN `width`, DROP COLUMN `height`;"],
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
					['type' => 'add', 'name' => 'u_yf_watchdog_schedule', 'sql' => '`u_yf_watchdog_schedule`(
						`userid` int(11) NOT NULL  , 
						`frequency` smallint(6) NOT NULL  , 
						`last_execution` datetime NULL  , 
						PRIMARY KEY (`userid`) , 
						CONSTRAINT `u_yf_watchdog_schedule_ibfk_1` 
						FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE 
					)'],
					['type' => 'add', 'name' => 'u_yf_crmentity_last_changes', 'sql' => '`u_yf_crmentity_last_changes` (
						`crmid` int(11) NOT NULL,
						`fieldname` varchar(255) NOT NULL,
						`user_id` int(11) NOT NULL,
						`date_updated` datetime NOT NULL
					  )'],
					['type' => 'remove', 'name' => 'vtiger_invitees'],
					['type' => 'add', 'name' => 'u_yf_activity_invitation', 'sql' => "`u_yf_activity_invitation` (
						`inviteesid` int(19) unsigned NOT NULL AUTO_INCREMENT,
						`activityid` int(19) NOT NULL,
						`crmid` int(19) NOT NULL DEFAULT '0',
						`email` varchar(100) NOT NULL DEFAULT '',
						`status` tinyint(1) DEFAULT '0',
						`time` datetime DEFAULT NULL,
						PRIMARY KEY (`inviteesid`),
						KEY `activityid` (`activityid`),
						CONSTRAINT `u_yf_activity_invitation_ibfk_1` FOREIGN KEY (`activityid`) REFERENCES `vtiger_activity` (`activityid`) ON DELETE CASCADE
					  )"],
					['type' => 'add', 'name' => 'l_yf_settings_tracker_basic', 'sql' => "`l_yf_settings_tracker_basic` (
						`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`user_id` int(11) unsigned NOT NULL,
						`type` tinyint(1) NOT NULL,
						`action` varchar(50) NOT NULL,
						`record_id` int(11) DEFAULT NULL,
						`module_name` varchar(50) NOT NULL,
						`date` datetime NOT NULL,
						PRIMARY KEY (`id`)
					  )"],
					['type' => 'add', 'name' => 'l_yf_settings_tracker_detail', 'sql' => "`l_yf_settings_tracker_detail` (
						`id` int(11) unsigned NOT NULL,
						`prev_value` varchar(255) NOT NULL DEFAULT '',
						`post_value` varchar(255) NOT NULL DEFAULT '',
						`field` varchar(255) NOT NULL,
						KEY `id` (`id`)
					  )"],
					['type' => 'add', 'name' => 's_yf_privileges_updater', 'sql' => "`s_yf_privileges_updater` (
						`module` varchar(30) NOT NULL DEFAULT '',
						`crmid` int(19) NOT NULL DEFAULT '0',
						`priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
						`type` tinyint(1) NOT NULL DEFAULT '0',
						UNIQUE KEY `module` (`module`,`crmid`,`type`),
						KEY `crmid` (`crmid`)
					  )"],
				];
				break;
			case 2:
				$tables = [
					['type' => 'add', 'name' => 'u_yf_announcement_mark', 'sql' => "`u_yf_announcement_mark` (
						`announcementid` int(19) NOT NULL,
						`userid` int(19) NOT NULL,
						`date` datetime NOT NULL,
						`status` tinyint(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (`announcementid`,`userid`),
						KEY `userid` (`userid`,`status`),
						KEY `announcementid` (`announcementid`,`userid`,`date`,`status`),
						CONSTRAINT `u_yf_announcement_mark_ibfk_1` FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE
					  )"],
				];
				break;
			case 3:
				$tables = [
					['type' => 'remove', 'name' => 'vtiger_ticketcomments'],
					['type' => 'add', 'name' => 'a_yf_encryption', 'sql' => "`a_yf_encryption` (
						`method` varchar(40) NOT NULL,
						`pass` varchar(16) NOT NULL
					  )"],
				];
				break;
			case 4:
				$tables = [
					['type' => 'exception', 'name' => 'u_yf_crmentity_search_label', 'sql' => "CREATE TABLE IF NOT EXISTS `u_yf_crmentity_search_label` (
						`crmid` int(19) unsigned NOT NULL,
						`searchlabel` varchar(255) NOT NULL,
						`setype` varchar(30) NOT NULL,
						`userid` text NOT NULL,
						PRIMARY KEY (`crmid`),
						KEY `searchlabel` (`searchlabel`),
						KEY `searchlabel_2` (`searchlabel`,`setype`)
					  ) ENGINE=MyISAM DEFAULT CHARSET=utf8"],
					['type' => 'exception', 'name' => 'u_yf_crmentity_label', 'sql' => "CREATE TABLE IF NOT EXISTS `u_yf_crmentity_label` (
						`crmid` int(11) unsigned NOT NULL,
						`label` varchar(255) DEFAULT NULL,
						PRIMARY KEY (`crmid`)
					  ) ENGINE=MyISAM DEFAULT CHARSET=utf8"],
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

	public function updateSettingMenu()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();

		$maxFieldId = $this->getMax('vtiger_settings_field', 'fieldid');
		$db->update('vtiger_settings_field_seq', ['id' => $maxFieldId - 1]);

		$this->updateSettingBlocks();
		$blocks = [];
		$fieldsToDelete = [
			['LBL_USER_MANAGEMENT', 'LBL_RECORDALLOCATION', NULL, 'LBL_RECORDALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings', '9', '0', '0']
		];
		foreach ($fieldsToDelete as $row) {
			$result = $db->pquery('SELECT fieldid FROM `vtiger_settings_field` INNER JOIN vtiger_settings_blocks ON vtiger_settings_blocks.blockid = vtiger_settings_field.blockid WHERE vtiger_settings_field.`name` = ? AND vtiger_settings_blocks.label = ?', [$row[1], $row[0]]);
			if ($result->rowCount()) {
				$db->delete('vtiger_settings_field', 'fieldid = ?', [$db->getSingleValue($result)]);
			}
		}

		$menu = [
			['LBL_USER_MANAGEMENT', 'LBL_USERS', 'adminIcon-user', 'LBL_USER_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=List', '1', '0', '1'],
			['LBL_USER_MANAGEMENT', 'LBL_ROLES', 'adminIcon-roles', 'LBL_ROLE_DESCRIPTION', 'index.php?module=Roles&parent=Settings&view=Index', '2', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_PROFILES', 'adminIcon-profiles', 'LBL_PROFILE_DESCRIPTION', 'index.php?module=Profiles&parent=Settings&view=List', '3', '0', '0'],
			['LBL_USER_MANAGEMENT', 'USERGROUPLIST', 'adminIcon-groups', 'LBL_GROUP_DESCRIPTION', 'index.php?module=Groups&parent=Settings&view=List', '4', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_SHARING_ACCESS', 'adminIcon-module-access', 'LBL_SHARING_ACCESS_DESCRIPTION', 'index.php?module=SharingAccess&parent=Settings&view=Index', '5', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_FIELDS_ACCESS', 'adminIcon-special-access', 'LBL_SHARING_FIELDS_DESCRIPTION', 'index.php?module=FieldAccess&parent=Settings&view=Index', '6', '0', '0'],
			['LBL_LOGS', 'LBL_LOGIN_HISTORY_DETAILS', 'adminIcon-users-login', 'LBL_LOGIN_HISTORY_DESCRIPTION', 'index.php?module=LoginHistory&parent=Settings&view=List', '3', '0', '0'],
			['LBL_STUDIO', 'VTLIB_LBL_MODULE_MANAGER', 'adminIcon-modules-installation', 'VTLIB_LBL_MODULE_MANAGER_DESCRIPTION', 'index.php?module=ModuleManager&parent=Settings&view=List', '1', '0', '1'],
			['LBL_STUDIO', 'LBL_PICKLIST_EDITOR', 'adminIcon-fields-picklists', 'LBL_PICKLIST_DESCRIPTION', 'index.php?parent=Settings&module=Picklist&view=Index', '9', '0', '1'],
			['LBL_STUDIO', 'LBL_PICKLIST_DEPENDENCY_SETUP', 'adminIcon-fields-picklists-relations', 'LBL_PICKLIST_DEPENDENCY_DESCRIPTION', 'index.php?parent=Settings&module=PickListDependency&view=List', '10', '0', '0'],
			['LBL_COMPANY', 'NOTIFICATIONSCHEDULERS', '', 'LBL_NOTIF_SCHED_DESCRIPTION', 'index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings', '4', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'INVENTORYNOTIFICATION', '', 'LBL_INV_NOTIF_DESCRIPTION', 'index.php?module=Settings&view=listinventorynotifications&parenttab=Settings', '1', '0', '0'],
			['LBL_COMPANY', 'LBL_COMPANY_DETAILS', 'adminIcon-company-detlis', 'LBL_COMPANY_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails', '2', '0', '0'],
			['LBL_MAIL_TOOLS', 'LBL_MAIL_SERVER_SETTINGS', 'adminIcon-mail-configuration', 'LBL_MAIL_SERVER_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail', '5', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_CURRENCY_SETTINGS', 'adminIcon-currencies', 'LBL_CURRENCY_DESCRIPTION', 'index.php?parent=Settings&module=Currency&view=List', '4', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_SWITCH_USERS', 'adminIcon-users', 'LBL_SWITCH_USERS_DESCRIPTION', 'index.php?module=Users&view=SwitchUsers&parent=Settings', '11', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_SYSTEM_INFO', 'adminIcon-server-configuration', 'LBL_SYSTEM_DESCRIPTION', 'index.php?module=Settings&submodule=Server&view=ProxyConfig', '6', '1', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_DEFAULT_MODULE_VIEW', 'adminIcon-standard-modules', 'LBL_DEFAULT_MODULE_VIEW_DESC', 'index.php?module=Settings&action=DefModuleView&parenttab=Settings', '2', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_TERMS_AND_CONDITIONS', 'adminIcon-terms-and-conditions', 'LBL_INV_TANDC_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit', '3', '0', '0'],
			['LBL_STUDIO', 'LBL_CUSTOMIZE_RECORD_NUMBERING', 'adminIcon-recording-control', 'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering', '6', '0', '0'],
			['LBL_AUTOMATION', 'LBL_LIST_WORKFLOWS', 'adminIcon-triggers', 'LBL_LIST_WORKFLOWS_DESCRIPTION', 'index.php?module=Workflows&parent=Settings&view=List', '1', '0', '1'],
			['LBL_SYSTEM_TOOLS', 'LBL_CONFIG_EDITOR', 'adminIcon-system-tools', 'LBL_CONFIG_EDITOR_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail', '7', '0', '0'],
			['LBL_AUTOMATION', 'Scheduler', 'adminIcon-cron', 'LBL_SCHEDULER_DESCRIPTION', 'index.php?module=CronTasks&parent=Settings&view=List', '3', '0', '0'],
			['LBL_AUTOMATION', 'LBL_WORKFLOW_LIST', 'adminIcon-workflow', 'LBL_AVAILABLE_WORKLIST_LIST', 'index.php?module=com_vtiger_workflow&action=workflowlist', '1', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'ModTracker', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_DESCRIPTION', 'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker', '9', '0', '0'],
			['LBL_INTEGRATION', 'LBL_PBXMANAGER', 'adminIcon-pbx-manager', 'LBL_PBXMANAGER_DESCRIPTION', 'index.php?module=PBXManager&parent=Settings&view=Index', '22', '0', '0'],
			['LBL_INTEGRATION', 'LBL_CUSTOMER_PORTAL', 'adminIcon-customer-portal', 'PORTAL_EXTENSION_DESCRIPTION', 'index.php?module=CustomerPortal&action=index&parenttab=Settings', '3', '0', '0'],
			['LBL_INTEGRATION', 'Webforms', 'adminIcon-online-forms', 'LBL_WEBFORMS_DESCRIPTION', 'index.php?module=Webforms&action=index&parenttab=Settings', '4', '0', '0'],
			['LBL_STUDIO', 'LBL_EDIT_FIELDS', 'adminIcon-modules-fields', 'LBL_LAYOUT_EDITOR_DESCRIPTION', 'index.php?module=LayoutEditor&parent=Settings&view=Index', '2', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_PDF', 'adminIcon-modules-pdf-templates', 'LBL_PDF_DESCRIPTION', 'index.php?module=PDF&parent=Settings&view=List', '10', '0', '0'],
			['LBL_SECURITY_MANAGEMENT', 'LBL_PASSWORD_CONF', 'adminIcon-passwords-configuration', 'LBL_PASSWORD_DESCRIPTION', 'index.php?module=Password&parent=Settings&view=Index', '1', '0', '0'],
			['LBL_STUDIO', 'LBL_MENU_BUILDER', 'adminIcon-menu-configuration', 'LBL_MENU_BUILDER_DESCRIPTION', 'index.php?module=Menu&view=Index&parent=Settings', '14', '0', '1'],
			['LBL_STUDIO', 'LBL_ARRANGE_RELATED_TABS', 'adminIcon-modules-relations', 'LBL_ARRANGE_RELATED_TABS', 'index.php?module=LayoutEditor&parent=Settings&view=Index&mode=showRelatedListLayout', '4', '0', '1'],
			['LBL_MAIL_TOOLS', 'Mail Scanner', 'adminIcon-mail-scanner', 'LBL_MAIL_SCANNER_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=Index', '3', '0', '0'],
			['LBL_LOGS', 'Mail Logs', 'adminIcon-mail-download-history', 'LBL_MAIL_LOGS_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=logs', '4', '0', '0'],
			['LBL_MAIL_TOOLS', 'Mail View', 'adminIcon-oss_mailview', 'LBL_MAIL_VIEW_DESCRIPTION', 'index.php?module=OSSMailView&parent=Settings&view=index', '21', '0', '0'],
			['LBL_AUTOMATION', 'Document Control', 'adminIcon-workflow', 'LBL_DOCUMENT_CONTROL_DESCRIPTION', 'index.php?module=OSSDocumentControl&parent=Settings&view=Index', '4', '0', '0'],
			['LBL_AUTOMATION', 'Project Templates', 'adminIcon-document-templates', 'LBL_PROJECT_TEMPLATES_DESCRIPTION', 'index.php?module=OSSProjectTemplates&parent=Settings&view=Index', '5', '0', '0'],
			['LBL_About_YetiForce', 'License', 'adminIcon-license', 'LBL_LICENSE_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=License', '4', '0', '0'],
			['LBL_SECURITY_MANAGEMENT', 'OSSPassword Configuration', 'adminIcon-passwords-encryption', 'LBL_OSSPASSWORD_CONFIGURATION_DESCRIPTION', 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings', '3', '0', '0'],
			['LBL_AUTOMATION', 'LBL_DATAACCESS', 'adminIcon-recording-control', 'LBL_DATAACCESS_DESCRIPTION', 'index.php?module=DataAccess&parent=Settings&view=Index', '2', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LangManagement', 'adminIcon-languages-and-translations', 'LBL_LANGMANAGEMENT_DESCRIPTION', 'index.php?module=LangManagement&parent=Settings&view=Index', '1', '0', '0'],
			['LBL_USER_MANAGEMENT', 'GlobalPermission', 'adminIcon-special-access', 'LBL_GLOBALPERMISSION_DESCRIPTION', 'index.php?module=GlobalPermission&parent=Settings&view=Index', '7', '0', '0'],
			['LBL_SEARCH_AND_FILTERS', 'Search Setup', 'adminIcon-search-configuration', 'LBL_SEARCH_SETUP_DESCRIPTION', 'index.php?module=Search&parent=Settings&view=Index', '1', '0', '0'],
			['LBL_SEARCH_AND_FILTERS', 'CustomView', 'adminIcon-filters-configuration', 'LBL_CUSTOMVIEW_DESCRIPTION', 'index.php?module=CustomView&parent=Settings&view=Index', '2', '0', '0'],
			['LBL_STUDIO', 'Widgets', 'adminIcon-modules-widgets', 'LBL_WIDGETS_DESCRIPTION', 'index.php?module=Widgets&parent=Settings&view=Index', '3', '0', '1'],
			['LBL_About_YetiForce', 'Credits', 'adminIcon-contributors', 'LBL_CREDITS_DESCRIPTION', 'index.php?module=Vtiger&view=Credits&parent=Settings', '3', '0', '0'],
			['LBL_STUDIO', 'LBL_QUICK_CREATE_EDITOR', 'adminIcon-fields-quick-create', 'LBL_QUICK_CREATE_EDITOR_DESCRIPTION', 'index.php?module=QuickCreateEditor&parent=Settings&view=Index', '8', '0', '0'],
			['LBL_INTEGRATION', 'LBL_API_ADDRESS', 'adminIcon-address', 'LBL_API_ADDRESS_DESCRIPTION', 'index.php?module=ApiAddress&parent=Settings&view=Configuration', '5', '0', '0'],
			['LBL_SECURITY_MANAGEMENT', 'LBL_BRUTEFORCE', 'adminIcon-brute-force', 'LBL_BRUTEFORCE_DESCRIPTION', 'index.php?module=BruteForce&parent=Settings&view=Show', '2', '0', '0'],
			['LBL_LOGS', 'LBL_UPDATES_HISTORY', 'adminIcon-server-updates', 'LBL_UPDATES_HISTORY_DESCRIPTION', 'index.php?parent=Settings&module=Updates&view=Index', '2', '0', '0'],
			['LBL_SECURITY_MANAGEMENT', 'Backup', 'adminIcon-backup', 'LBL_BACKUP_DESCRIPTION', 'index.php?parent=Settings&module=BackUp&view=Index', '4', '0', '0'],
			['LBL_LOGS', 'LBL_CONFREPORT', 'adminIcon-server-configuration', 'LBL_CONFREPORT_DESCRIPTION', 'index.php?parent=Settings&module=ConfReport&view=Index', '1', '0', '0'],
			['LBL_CALENDAR_LABELS_COLORS', 'LBL_ACTIVITY_TYPES', 'adminIcon-calendar-types', 'LBL_ACTIVITY_TYPES_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=ActivityTypes', '1', '0', '0'],
			['LBL_STUDIO', 'LBL_WIDGETS_MANAGEMENT', 'adminIcon-widgets-configuration', 'LBL_WIDGETS_MANAGEMENT_DESCRIPTION', 'index.php?module=WidgetsManagement&parent=Settings&view=Configuration', '15', '0', '0'],
			['LBL_INTEGRATION', 'LBL_MOBILE_KEYS', 'adminIcon-mobile-applications', 'LBL_MOBILE_KEYS_DESCRIPTION', 'index.php?parent=Settings&module=MobileApps&view=MobileKeys', '6', '0', '0'],
			['LBL_STUDIO', 'LBL_TREES_MANAGER', 'adminIcon-field-folders', 'LBL_TREES_MANAGER_DESCRIPTION', 'index.php?module=TreesManager&parent=Settings&view=List', '11', '0', '0'],
			['LBL_STUDIO', 'LBL_MODTRACKER_SETTINGS', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_SETTINGS_DESCRIPTION', 'index.php?module=ModTracker&parent=Settings&view=List', '5', '0', '0'],
			['LBL_STUDIO', 'LBL_HIDEBLOCKS', 'adminIcon-filed-hide-bloks', 'LBL_HIDEBLOCKS_DESCRIPTION', 'index.php?module=HideBlocks&parent=Settings&view=List', '12', '0', '0'],
			['LBL_CALENDAR_LABELS_COLORS', 'LBL_PUBLIC_HOLIDAY', 'adminIcon-calendar-holidys', 'LBL_PUBLIC_HOLIDAY_DESCRIPTION', 'index.php?module=PublicHoliday&view=Configuration&parent=Settings', '3', '0', '0'],
			['LBL_CALENDAR_LABELS_COLORS', 'LBL_CALENDAR_CONFIG', 'adminIcon-calendar-configuration', 'LBL_CALENDAR_CONFIG_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=UserColors', '2', '0', '0'],
			['LBL_PROCESSES', 'LBL_SALES_PROCESSES', 'adminIcon-sales', 'LBL_SALES_PROCESSES_DESCRIPTION', 'index.php?module=SalesProcesses&view=Index&parent=Settings', '2', '0', '0'],
			['LBL_INTEGRATION', 'LBL_DAV_KEYS', 'adminIcon-dav-applications', 'LBL_DAV_KEYS_DESCRIPTION', 'index.php?parent=Settings&module=Dav&view=Keys', '7', '0', '0'],
			['LBL_MAIL_TOOLS', 'LBL_AUTOLOGIN', 'adminIcon-mail-auto-login', 'LBL_AUTOLOGIN_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Autologin', '4', '0', '0'],
			['LBL_MAIL_TOOLS', 'LBL_MAIL_GENERAL_CONFIGURATION', 'adminIcon-mail-smtp-server', 'LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Config', '1', '0', '0'],
			['LBL_PROCESSES', 'LBL_SUPPORT_PROCESSES', 'adminIcon-support ', 'LBL_SUPPORT_PROCESSES_DESCRIPTION', 'index.php?module=SupportProcesses&view=Index&parent=Settings', '6', '0', '0'],
			['LBL_CALENDAR_LABELS_COLORS', 'LBL_COLORS', 'adminIcon-colors', 'LBL_COLORS_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=Colors', '4', '0', '0'],
			['LBL_PROCESSES', 'LBL_REALIZATION_PROCESSES', 'adminIcon-realization', 'LBL_REALIZATION_PROCESSES_DESCRIPTION', 'index.php?module=RealizationProcesses&view=Index&parent=Settings', '3', '0', '0'],
			['LBL_PROCESSES', 'LBL_MARKETING_PROCESSES', 'adminIcon-marketing', 'LBL_MARKETING_PROCESSES_DESCRIPTION', 'index.php?module=MarketingProcesses&view=Index&parent=Settings', '1', '0', '0'],
			['LBL_PROCESSES', 'LBL_FINANCIAL_PROCESSES', 'adminIcon-finances', 'LBL_FINANCIAL_PROCESSES_DESCRIPTION', 'index.php?module=FinancialProcesses&view=Index&parent=Settings', '5', '0', '0'],
			['LBL_INTEGRATION', 'LBL_AUTHORIZATION', 'adminIcon-automation', 'LBL_AUTHORIZATION_DESCRIPTION', 'index.php?module=Users&view=Auth&parent=Settings', '1', '0', '0'],
			['LBL_PROCESSES', 'LBL_TIMECONTROL_PROCESSES', 'adminIcon-logistics', 'LBL_TIMECONTROL_PROCESSES_DESCRIPTION', 'index.php?module=TimeControlProcesses&parent=Settings&view=Index', '7', '0', '0'],
			['LBL_STUDIO', 'LBL_CUSTOM_FIELD_MAPPING', 'adminIcon-filed-mapping', 'LBL_CUSTOM_FIELD_MAPPING_DESCRIPTION', 'index.php?parent=Settings&module=Leads&view=MappingDetail', '13', '0', '0'],
			['LBL_INTEGRATION', 'LBL_CURRENCY_UPDATE', 'adminIcon-currencies', 'LBL_CURRENCY_UPDATE_DESCRIPTION', 'index.php?module=CurrencyUpdate&view=Index&parent=Settings', '2', '0', '0'],
			['LBL_ADVANCED_MODULES', 'LBL_CREDITLIMITS', 'adminIcon-credit-limit-base_2', 'LBL_CREDITLIMITS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=CreditLimits', '5', '0', '0'],
			['LBL_ADVANCED_MODULES', 'LBL_TAXES', 'adminIcon-taxes-rates', 'LBL_TAXES_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Taxes', '1', '0', '0'],
			['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTS', 'adminIcon-discount-base', 'LBL_DISCOUNTS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Discounts', '3', '0', '0'],
			['LBL_ADVANCED_MODULES', 'LBL_TAXCONFIGURATION', 'adminIcon-taxes-caonfiguration', 'LBL_TAXCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=TaxConfiguration', '4', '0', '0'],
			['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTCONFIGURATION', 'adminIcon-discount-configuration', 'LBL_DISCOUNTCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=DiscountConfiguration', '2', '0', '0'],
			['LBL_MAIL_TOOLS', 'Mail', 'adminIcon-mail-download-history', 'LBL_OSSMAIL_DESCRIPTION', 'index.php?module=OSSMail&parent=Settings&view=index', '2', '0', '0'],
			['LBL_STUDIO', 'LBL_MAPPEDFIELDS', 'adminIcon-mapped-fields', 'LBL_MAPPEDFIELDS_DESCRIPTION', 'index.php?module=MappedFields&parent=Settings&view=List', '16', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_LOCKS', 'adminIcon-locks', 'LBL_LOCKS_DESCRIPTION', 'index.php?module=Users&view=Locks&parent=Settings', '8', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_TYPE_NOTIFICATIONS', 'adminIcon-TypeNotification', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=List&parent=Settings', '12', '0', '0'],
			['LBL_SYSTEM_TOOLS', 'LBL_NOTIFICATIONS_CONFIGURATION', 'adminIcon-NotificationConfiguration', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=Configuration&parent=Settings', '13', '0', '0'],
			['LBL_INTEGRATION', 'LBL_POS', NULL, NULL, 'index.php?module=POS&view=Index&parent=Settings', '10', '0', '0'],
			['LBL_INTEGRATION', 'LBL_WEBSERVICE_APPS', NULL, NULL, 'index.php?module=WebserviceApps&view=Index&parent=Settings', '11', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_OWNER_ALLOCATION', 'adminIcon-owner', 'LBL_OWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=owner', '9', '0', '0'],
			['LBL_USER_MANAGEMENT', 'LBL_MULTIOWNER_ALLOCATION', 'adminIcon-shared-owner', 'LBL_MULTIOWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=sharedOwner', '10', '0', '0']
		];
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
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getLink($index)
	{
		$links = [];
		switch ($index) {
			case 1:
				$links = [
					['type' => 'add', 'data' => ['278', getTabid('Home'), 'DASHBOARDWIDGET', 'LBL_ALL_TIME_CONTROL', 'index.php?module=OSSTimeControl&view=ShowWidget&name=AllTimeControl', NULL, '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['279', getTabid('Home'), 'DASHBOARDWIDGET', 'LBL_NEW_ACCOUNTS', 'index.php?module=Accounts&view=ShowWidget&name=NewAccounts', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['280', getTabid('Home'), 'DASHBOARDWIDGET', 'LBL_NEGLECTED_ACCOUNTS', 'index.php?module=Accounts&view=ShowWidget&name=NeglectedAccounts', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['281', getTabid('Home'), 'DASHBOARDWIDGET', 'Chart', 'index.php?module=Reports&view=ShowWidget&name=Charts', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['283', getTabid('Home'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'update', 'data' => ['191', getTabid('Project'), 'LIST_VIEW_HEADER', 'LBL_GENERATE_FROM_TEMPLATE', 'index.php?module=OSSProjectTemplates&view=GenerateProject', 'userIcon-Project', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['284', getTabid('Accounts'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['285', getTabid('Contacts'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['286', getTabid('Leads'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['287', getTabid('HelpDesk'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['288', getTabid('OSSMailView'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['289', getTabid('OSSEmployees'), 'DASHBOARDWIDGET', 'ChartFilter', 'index.php?module=Home&view=ShowWidget&name=ChartFilter', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['290', getTabid('Home'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['291', getTabid('Accounts'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['292', getTabid('Contacts'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['293', getTabid('Leads'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['294', getTabid('HelpDesk'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['295', getTabid('OSSMailView'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['296', getTabid('OSSEmployees'), 'DASHBOARDWIDGET', 'Rss', 'index.php?module=Home&view=ShowWidget&name=Rss', '', '0', NULL, NULL, NULL, NULL]],
					['type' => 'remove', 'data' => ['116', getTabid('SMSNotifier'), 'HEADERSCRIPT', 'SMSNotifierCommonJS', 'modules/SMSNotifier/SMSNotifierCommon.js', '', '0', NULL, NULL, NULL]],
				];
				break;
			default:
				break;
		}
		return $links;
	}

	public function setLink($links)
	{
		$db = PearDatabase::getInstance();
		if (!empty($links)) {
			foreach ($links as $link) {
				list($id, $tabid, $type, $label, $url, $iconpath, $sequence, $path, $class, $method, $params) = $link['data'];
				$handlerInfo = ['path' => $path, 'class' => $class, 'method' => $method];
				if ($link['type'] == 'add') {
					$result = $db->pquery('SELECT 1 FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=?;', [$tabid, $type, $label, $url]);
					if (!$db->getRowCount($result))
						Vtiger_Link::addLink($tabid, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
				} elseif ($link['type'] == 'remove') {
					Vtiger_Link::deleteLink($tabid, $type, $label, $url);
				} elseif ($link['type'] == 'update') {
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

	public function getFieldsToRemove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'vtiger_ticketcomments' => ['comments']
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function removeFields($fields)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		foreach ($fields as $tableName => $columnsName) {
			if (empty($columnsName) || !Vtiger_Utils::CheckTable($tableName)) {
				continue;
			}
			foreach ($columnsName as $columnName) {
				$result = $db->pquery("SELECT fieldid FROM vtiger_field WHERE columnname = ? AND tablename = ?;", [$columnName, $tableName]);
				if ($id = $db->getSingleValue($result)) {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					try {
						$fieldInstance->delete();
						$db->delete('vtiger_fieldmodulerel', 'fieldid = ?', [$id]);
					} catch (Exception $e) {
						$log->debug("ERROR " . __CLASS__ . "::" . __METHOD__ . ": code " . $e->getCode() . " message " . $e->getMessage());
					}
				}
				$result = $db->query("SHOW COLUMNS FROM `$tableName` LIKE '$columnName';");
				if ($result->rowCount() == 1) {
					$db->query("ALTER TABLE `$tableName` DROP COLUMN `$columnName` ;");
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function deleteBlocks($blocks)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		foreach ($blocks as $tabId => $blocksModule) {
			if (empty($blocksModule)) {
				continue;
			}
			foreach ($blocksModule as $block) {
				$result = $db->pquery('SELECT 1 FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_field.tabid = ? LIMIT 1', [$block, $tabId]);
				if (!$result->rowCount()) {
					$db->delete('vtiger_blocks', 'blocklabel = ? AND `tabid` = ?', [$block, $tabId]);
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function updateSettingBlocks()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();

		$maxFieldId = $this->getMax('vtiger_settings_blocks', 'blockid');
		$db->update('vtiger_settings_blocks_seq', ['id' => $maxFieldId - 1]);

		$settingMenu = [['1', 'LBL_USER_MANAGEMENT', '2', 'adminIcon-permissions', '0', NULL],
			['2', 'LBL_STUDIO', '3', 'adminIcon-standard-modules', '0', NULL],
			['3', 'LBL_COMPANY', '13', 'adminIcon-company-information', '0', NULL],
			['4', 'LBL_SYSTEM_TOOLS', '12', 'adminIcon-system-tools', '0', NULL],
			['5', 'LBL_INTEGRATION', '9', 'adminIcon-integration', '0', NULL],
			['6', 'LBL_PROCESSES', '14', 'adminIcon-processes', '0', NULL],
			['7', 'LBL_SECURITY_MANAGEMENT', '7', 'adminIcon-security', '0', NULL],
			['8', 'LBL_MAIL_TOOLS', '11', 'adminIcon-mail-tools', '0', NULL],
			['9', 'LBL_About_YetiForce', '15', 'adminIcon-about-yetiforce', '0', NULL],
			['11', 'LBL_ADVANCED_MODULES', '4', 'adminIcon-advenced-modules', '0', NULL],
			['12', 'LBL_CALENDAR_LABELS_COLORS', '5', 'adminIcon-calendar-labels-colors', '0', NULL],
			['13', 'LBL_SEARCH_AND_FILTERS', '6', 'adminIcon-search-and-filtres', '0', NULL],
			['14', 'LBL_LOGS', '8', 'adminIcon-logs', '0', NULL],
			['15', 'LBL_AUTOMATION', '10', 'adminIcon-automation', '0', NULL],
			['16', 'LBL_MENU_SUMMARRY', '0', 'userIcon-Home', '1', 'index.php?module=Vtiger&parent=Settings&view=Index'],
			['17', 'LBL_YETIFORCE_SHOP', '1', 'adminIcon-yetiforce-shop', '1', 'https://shop.yetiforce.com/']];

		$removeSettingsMenu = ['LBL_OTHER_SETTINGS', 'LBL_MAIL', 'LBL_CUSTOMIZE_TRANSLATIONS', 'LBL_EXTENDED_MODULES'];
		$db->delete('vtiger_settings_blocks', '`label` IN (?,?,?,?) ', $removeSettingsMenu);

		foreach ($settingMenu as $row) {
			$result = $db->pquery('SELECT 1 FROM vtiger_settings_blocks WHERE label = ?', [$row[1]]);
			if ($result->rowCount() > 0) {
				$db->update('vtiger_settings_blocks', ['sequence' => $row[2], 'icon' => $row[3], 'type' => $row[4], 'linkto' => $row[5]], '`label` = ?', [$row[1]]);
			} else {
				$blockid = $db->getUniqueID('vtiger_settings_blocks');
				$db->insert('vtiger_settings_blocks', ['blockid' => $blockid, 'label' => $row[1], 'sequence' => $row[2], 'icon' => $row[3], 'type' => $row[4], 'linkto' => $row[5]]);
			}
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
