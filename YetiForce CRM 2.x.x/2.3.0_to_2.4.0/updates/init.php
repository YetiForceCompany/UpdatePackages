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
		'modules/PaymentsIn/schema.xml',
		'modules/PaymentsOut/schema.xml',
		'config.csrf-secret.php',
		'api/firefoxtoolbar.php',
		'api/thunderbirdplugin.php',
		'api/wordplugin.php',
		'layouts/vlayout/modules/OSSMail/resources/mailtemplate.js',
		'layouts/vlayout/modules/OSSMailTemplates/Config.tpl',
		'layouts/vlayout/skins/images/btnAdd.png',
		'libraries/adodb',
		'libraries/chartjs/Chartmin.js',
		'libraries/guidersjs',
		'libraries/jquery/datatables/bower.json',
		'libraries/jquery/datatables/composer.json',
		'libraries/jquery/datatables/dataTables.jquery.json',
		'libraries/jquery/datatables/extensions/ColReorder/Readme.txt',
		'libraries/jquery/datatables/extensions/ColVis/Readme.txt',
		'libraries/jquery/datatables/extensions/FixedColumns/Readme.txt',
		'libraries/jquery/datatables/media/images/back_disabled.png',
		'libraries/jquery/datatables/media/images/back_enabled.png',
		'libraries/jquery/datatables/media/images/back_enabled_hover.png',
		'libraries/jquery/datatables/media/images/forward_disabled.png',
		'libraries/jquery/datatables/media/images/forward_enabled.png',
		'libraries/jquery/datatables/media/images/forward_enabled_hover.png',
		'libraries/jquery/datatables/package.json',
		'libraries/jquery/jqplot/excanvas.js',
		'libraries/jquery/jqplot/jquery.jqplot.css',
		'libraries/jquery/jqplot/jquery.jqplot.js',
		'libraries/jquery/jquery-ui/css',
		'libraries/jquery/jquery-ui/js',
		'libraries/jquery/jquery-ui/README.md',
		'libraries/jquery/jquery-ui/third-party',
		'libraries/jquery/pnotify/jquery.pnotify.default.css',
		'libraries/jquery/pnotify/jquery.pnotify.js',
		'libraries/jquery/pnotify/jquery.pnotify.min.js',
		'libraries/jquery/pnotify/use for pines style icons/jquery.pnotify.default.icons.css',
		'libraries/jquery/select2/component.json',
		'libraries/jquery/select2/LICENSE',
		'libraries/jquery/select2/release.sh',
		'libraries/jquery/select2/select2.png',
		'libraries/jquery/select2/select2x2.png',
		'libraries/jquery/select2/spinner.gif',
		'modules/Accounts/actions',
		'modules/Contacts/actions/TransferOwnership.php',
		'modules/ModComments/actions/Delete.php',
		'modules/OSSMailTemplates/actions/GetListModule.php',
		'modules/OSSMailTemplates/actions/GetListTpl.php',
		'modules/RequirementCards/models/Module.php',
		'modules/Settings/BackUp/actions/CreateBackUp.php',
		'modules/Settings/BackUp/actions/CreateFileBackUp.php',
		'modules/Settings/BackUp/actions/SaveFTPConfig.php',
		'modules/Vtiger/resources/validator/EmailValidator.js',
		'layouts/vlayout/modules/OSSMailTemplates/Config.tpl',
		'layouts/vlayout/skins/images/btnAdd.png',
		'languages/de_de/Install.php',
		'languages/en_us/Install.php',
		'languages/pl_pl/Install.php',
		'languages/pt_br/Install.php',
		'languages/ru_ru/Install.php',
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
		$this->changeActivity();
		$this->deleteCustomView();
		$this->databaseSchema();
		$this->updateFiles();
		$this->enableTracking();
	}

	function postupdate()
	{
		return true;
	}

	public function changeActivity()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '1' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_CANCELLED','PLL_COMPLETED','PLL_OVERDUE'))");
		$adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '0' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_IN_REALIZATION','PLL_POSTPONED','PLL_PLANNED'))");
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function deleteCustomView()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT cvid FROM vtiger_customview WHERE entitytype = ?', ['Emails']);
		if ($result->rowCount() > 0) {
			$cvid = $adb->query_result($result, 0, 'cvid');
			$adb->delete('vtiger_customview', 'cvid = ?', [$cvid]);
			$adb->delete('vtiger_cvcolumnlist', 'cvid = ?', [$cvid]);
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function databaseSchema()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$adb = PearDatabase::getInstance();
		$result = $adb->query("SHOW COLUMNS FROM `s_yf_multireference` LIKE 'type';");
		if ($result->rowCount() == 0) {
			$adb->query("ALTER TABLE `s_yf_multireference` ADD COLUMN `type` TINYINT(1) DEFAULT 0 NOT NULL AFTER `lastid`;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `l_yf_sqltime` (
  `id` int(19) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `data` text,
  `date` datetime DEFAULT NULL,
  `qtime` decimal(20,3) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		$adb->query('DROP TABLE IF EXISTS `vtiger_sqltimelog`');

		$this->addHandler([['vtiger.entity.aftersave.final', 'modules/Vtiger/handlers/MultiReferenceUpdater.php', 'Vtiger_MultiReferenceUpdater_Handler', '', '1', '[]']]);

		$sql = 'UPDATE vtiger_relatedlists SET `related_tabid` = ?, `label` = ? WHERE `related_tabid` = ?';
		$adb->pquery($sql, [getTabid('OSSMailView'), 'OSSMailView', getTabid('Emails')]);

		$query = 'UPDATE vtiger_currencies_seq SET id = (SELECT currencyid FROM vtiger_currencies ORDER BY currencyid DESC LIMIT 1)';
		$adb->query($query);

		$uniqId = $adb->getUniqueID('vtiger_currencies');
		$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['CFP Franc']);

		if ($adb->num_rows($result) <= 0) {
			$adb->pquery('INSERT INTO vtiger_currencies VALUES (?,?,?,?)', [$uniqId, 'CFP Franc', 'XPF', 'F']);
		}

		$sortOrderResult = $adb->pquery("SELECT sortorderid FROM vtiger_time_zone WHERE time_zone = ?", ['Asia/Yakutsk']);
		if ($adb->num_rows($sortOrderResult)) {
			$sortOrderId = $adb->query_result($sortOrderResult, 0, 'sortorderid');
			$adb->pquery('UPDATE vtiger_time_zone SET sortorderid = (sortorderid + 1) WHERE sortorderid > ?', [$sortOrderId]);
			$adb->pquery('INSERT INTO vtiger_time_zone (time_zone, sortorderid, presence) VALUES (?, ?, ?)', ['Etc/GMT-11', ($sortOrderId + 1), 1]);
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function addHandler($addHandler = [])
	{
		$log = vglobal('log');
		$adb = PearDatabase::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ($addHandler) {
			$em = new VTEventsManager($adb);
			foreach ($addHandler as $handler) {
				$result = $adb->pquery('SELECT * FROM `vtiger_eventhandlers` WHERE event_name = ? AND handler_class = ?;', [$handler[0], $handler[2]]);
				if ($result->rowCount() == 0) {
					$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function updateFiles()
	{
		$log = vglobal('log');
		$root_directory = vglobal('root_directory');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if (!$root_directory)
			$root_directory = getcwd();
		$config = $root_directory . '/config/config.inc.php';
		if (file_exists($config)) {
			$configContent = file($config);
			$defaultLayout = true;
			foreach ($configContent as $key => $line) {
				if (strpos($line, 'defaultLayout') !== false) {
					$defaultLayout = false;
				}
			}
			$content = implode("", $configContent);
			if ($defaultLayout) {
				$content .= '
// Set the default layout 
$defaultLayout = \'vlayout\';

';
			}
			$file = fopen($config, "w+");
			fwrite($file, $content);
			fclose($file);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function enableTracking()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		include_once('modules/ModTracker/ModTracker.php');
		ModTracker::enableTrackingForModule(Vtiger_Functions::getModuleId('OSSTimeControl'));

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
