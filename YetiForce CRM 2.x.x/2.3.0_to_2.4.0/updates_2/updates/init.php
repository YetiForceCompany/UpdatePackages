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
		'languages/pl_pl/Settings/EmailTemplate.php',
		'languages/ru_ru/Settings/EmailTemplate.php',
		'languages/ru_ru/Settings/MenuEditor.php',
		'storage/vtiger.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/Interchange/Namespace.php',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Attr.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/AutoFormat.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/AutoFormatParam.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Cache.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Core.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/CSS.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Filter.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/FilterParam.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/HTML.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Output.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/Test.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/URI.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/Lexer/PEARSax3.php',
		'cache/vtlib/HTML/3.3.0,aacfe2e21b552364077576cd0a636b92,1.ser',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/AutoFormatParam.PurifierLinkifyDocURL.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/FilterParam.ExtractStyleBlocksEscaping.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/FilterParam.ExtractStyleBlocksScope.txt',
		'libraries/htmlpurifier/library/HTMLPurifier/ConfigSchema/schema/FilterParam.ExtractStyleBlocksTidyImpl.txt',
		'modules/FBookkeeping/schema.xml',
		'modules/FInvoice/schema.xml',
		'modules/FInvoiceProforma/schema.xml',
		'modules/IGDN/schema.xml',
		'modules/IGIN/schema.xml',
		'modules/IGRN/schema.xml',
		'modules/IIDN/schema.xml',
		'modules/IPreOrder/schema.xml',
		'modules/IStorages/schema.xml',
		'modules/KnowledgeBase/schema.xml',
		'modules/ISTDN/schema.xml',
		'modules/ISTRN/schema.xml',
		'modules/ISTN/schema.xml',
		'modules/FCorectingInvoice/schema.xml',
		'modules/Home/js/HelpMeNow.js',
		'layouts/basic/modules/Settings/CustomView/resources/CustomView.js',
		'layouts/basic/modules/Assets/ListViewRecordActions.tpl',
		'layouts/basic/modules/Assets/RelatedListActions.tpl',
		'layouts/basic/modules/Assets/resources/EditStatus.min.js',
		'modules/Assets/models/DetailView.php',
		'layouts/basic/modules/Assets/ListViewRecordActions.tpl',
		'layouts/basic/modules/Assets/RelatedListActions.tpl',
		'layouts/basic/modules/OSSSoldServices/ListViewRecordActions.tpl',
		'layouts/basic/modules/OSSSoldServices/RelatedListActions.tpl',
		'modules/Assets/models/DetailView.php',
		'modules/OSSSoldServices/models/DetailView.php',
		'layouts/basic/modules/IGIN/EditStatus.tpl',
		'layouts/basic/modules/IGIN/ListViewRecordActions.tpl',
		'layouts/basic/modules/IGIN/RelatedListActions.tpl',
		'layouts/basic/modules/IGIN/resources/EditStatus.js',
		'layouts/basic/modules/IGIN/resources/EditStatus.min.js',
		'layouts/basic/modules/IGRN/EditStatus.tpl',
		'layouts/basic/modules/IGRN/ListViewRecordActions.tpl',
		'layouts/basic/modules/IGRN/RelatedListActions.tpl',
		'layouts/basic/modules/IGRN/resources/EditStatus.js',
		'layouts/basic/modules/IGRN/resources/EditStatus.min.js',
		'layouts/basic/modules/SQuoteEnquiries/ListViewRecordActions.tpl',
		'layouts/basic/modules/SQuoteEnquiries/Modal.tpl',
		'layouts/basic/modules/SQuoteEnquiries/RelatedListActions.tpl',
		'layouts/basic/modules/SQuoteEnquiries/resources/Modal.js',
		'layouts/basic/modules/SQuoteEnquiries/resources/Modal.min.js',
		'layouts/basic/modules/SQuotes/ListViewRecordActions.tpl',
		'layouts/basic/modules/SQuotes/Modal.tpl',
		'layouts/basic/modules/SQuotes/RelatedListActions.tpl',
		'layouts/basic/modules/SQuotes/resources/Modal.js',
		'layouts/basic/modules/SQuotes/resources/Modal.min.js',
		'modules/IGIN/models/DetailView.php',
		'modules/IGIN/views/EditStatus.php',
		'modules/IGRN/actions/SaveAjax.php',
		'modules/IGRN/models/DetailView.php',
		'modules/IGRN/views/EditStatus.php',
		'modules/SQuoteEnquiries/actions/UpdateStatus.php',
		'modules/SQuoteEnquiries/models/DetailView.php',
		'modules/SQuoteEnquiries/views/Modal.php',
		'modules/SQuotes/models/DetailView.php',
		'modules/SQuotes/views/Modal.php',
		'layouts/basic/modules/SCalculations/ListViewRecordActions.tpl',
		'layouts/basic/modules/SCalculations/Modal.tpl',
		'layouts/basic/modules/SCalculations/RelatedListActions.tpl',
		'layouts/basic/modules/SCalculations/resources/Modal.js',
		'layouts/basic/modules/SCalculations/resources/Modal.min.js',
		'layouts/basic/modules/SRecurringOrders/ListViewRecordActions.tpl',
		'layouts/basic/modules/SRecurringOrders/Modal.tpl',
		'layouts/basic/modules/SRecurringOrders/RelatedListActions.tpl',
		'layouts/basic/modules/SRecurringOrders/resources/Modal.js',
		'layouts/basic/modules/SRequirementsCards/ListViewRecordActions.tpl',
		'layouts/basic/modules/SRequirementsCards/Modal.tpl',
		'layouts/basic/modules/SRequirementsCards/RelatedListActions.tpl',
		'layouts/basic/modules/SRequirementsCards/resources/Modal.js',
		'layouts/basic/modules/SRequirementsCards/resources/Modal.min.js',
		'layouts/basic/modules/SSingleOrders/ListViewRecordActions.tpl',
		'layouts/basic/modules/SSingleOrders/Modal.tpl',
		'layouts/basic/modules/SSingleOrders/RelatedListActions.tpl',
		'layouts/basic/modules/SSingleOrders/resources/Modal.js',
		'layouts/basic/modules/SSingleOrders/resources/Modal.min.js',
		'modules/SCalculations/models/DetailView.php',
		'modules/SCalculations/views/Modal.php',
		'modules/SRecurringOrders/models/DetailView.php',
		'modules/SRecurringOrders/views/Modal.php',
		'modules/SRequirementsCards/models/DetailView.php',
		'modules/SRequirementsCards/views/Modal.php',
		'modules/SSingleOrders/models/DetailView.php',
		'modules/SSingleOrders/views/Modal.php',
		'layouts/basic/modules/Settings/Notifications/resources/Notifications.min.js',
		'modules/SCalculations/actions/UpdateStatus.php',
		'modules/Settings/Github/views/AddIssueAJAX.php',
		'modules/SQuotes/actions/UpdateStatus.php',
		'modules/SRecurringOrders/actions/UpdateStatus.php',
		'modules/SRequirementsCards/actions/UpdateStatus.php',
		'modules/SSingleOrders/actions/UpdateStatus.php',
		'modules/Campaigns/models/ListView.php',
		'modules/IGDNC/schema.xml',
		'modules/IGRNC/schema.xml',
		'modules/Accounts/handlers',
		'cron/modules/SalesOrder',
		'modules/Campaigns/dashboards',
		'modules/PaymentsIn/workflow',
		'modules/ServiceContracts/models',
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
		$this->setTablesScheme($this->getTablesAction(1));
		$this->addModules();
		$this->setAlterTables($this->getAlterTables(1));
		$this->setLink($this->getLink(1));
		$this->setRelations($this->getRelations(1));
		$this->setWidget($this->getWidget(1));
		$this->setBlocks($this->getBlocks(1));
		$this->setFields($this->getFields(1));
		$this->setPicklists($this->getPicklists(1));
		$this->setInventoryFields($this->getInventoryFields(1));
		$this->setWorkflowTaskType($this->getWorkflowTaskType(1));
		$this->addHandler([['vtiger.entity.aftersave.final', 'modules/IStorages/handlers/RecalculateStockHandler.php', 'RecalculateStockHandler', '', '1', '[]']]);
		$this->setDataAccess($this->getDataAccess(1));
		$this->removeFields($this->getFieldsToRemove(1));
		$this->move($this->getFieldsToMove(1));
		$this->updateInventoryFields();
		$this->updatePack();
		$this->addActionMap();
		$this->setAlterTables($this->getAlterTables(2));
		$this->updatePack2();
		$this->mappedFields();
		$this->setPicklistDependncy($this->getPicklistDependncy(1));
		$this->addStandardPremissions();
		$this->move($this->getFieldsToMove(2));
		$this->addHandler([['vtiger.view.detail.before', 'modules/ModTracker/handlers/ModTrackerHandler.php', 'ModTrackerHandler', '', '1', '[]']]);
		$this->changeTicketCategory();
//		$this->removeFields($this->getFieldsToRemove(2));
//		$this->setFields($this->getFields(2));
		//...
		$this->updateMenu();
		$this->updateSettingsMenu();
		$this->updateSettingFieldsMenu();
		$menuRecordModel = new Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
	}

	public function changeTicketCategory()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_field WHERE tablename = ? AND fieldname = ? LIMIT 1;', ['vtiger_troubletickets', 'ticketcategories']);
		$row = $db->getRow($result);
		if ($row && $row['uitype'] != 302) {
			$typeofdata = $row['typeofdata'];
			$fieldname = $row['fieldname'];
			$oldfieldlabel = $row['fieldlabel'];
			$tablename = $row['tablename'];
			$columnname = $row['columnname'];
			$uitype = $row['uitype'];
			$colName = $row['columnname'];
			$tablica = $row['tablename'];
			$fieldtype = explode("~", $typeofdata);

			$deltablequery = 'DROP TABLE IF EXISTS vtiger_' . $fieldname;
			$db->pquery($deltablequery, []);
			$deltablequeryseq = 'DROP TABLE IF EXISTS vtiger_' . $fieldname . '_seq';
			$db->pquery($deltablequeryseq, []);
			$db->pquery("delete from  vtiger_picklist_dependency where sourcefield=? or targetfield=?", array($fieldname, $fieldname));

			$fieldquery = 'select * from vtiger_picklist where name = ?';
			$res = $db->pquery($fieldquery, array($fieldname));
			$picklistid = $db->query_result($res, 0, 'picklistid');
			$db->pquery("delete from vtiger_picklist where name = ?", array($fieldname));
			$db->pquery("delete from vtiger_role2picklist where picklistid = ?", array($picklistid));
			$db->update('vtiger_troubletickets', ['category' => '']);
			$db->update('vtiger_field', ['uitype' => 302], 'tablename = ? AND fieldname = ? ', ['vtiger_troubletickets', 'ticketcategories']);
			$this->setTrees($this->getTrees(1));
		}
	}

	public function getTrees($index)
	{
		$trees = [];
		switch ($index) {
			case 1:
				$trees = [
					[
						'column' => 'category',
						'base' => ['17', 'Category', getTabid('HelpDesk'), '1'],
						'data' => [['17', 'none', 'T1', 'T1', '0', 'none', '', '']]
					]
				];
				break;
			default:
				break;
		}
		return $trees;
	}

	public function setTrees($trees)
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

	public function addStandardPremissions()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$actions = [7 => 'CreateView'];
		$result = $db->query('SELECT actionid,actionname FROM vtiger_actionmapping;');
		$rows = $result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
		foreach ($actions as $key => $action) {
			if (!in_array($action, $rows)) {
				if (array_key_exists($key, $rows)) {
					$key = $this->getMax('vtiger_actionmapping', 'actionid');
				}
				$db->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?, ?);", [$key, $action, 0]);
				$result = $db->query('SELECT DISTINCT tabid, profileid, vtiger_actionmapping.`actionname`,permissions  FROM vtiger_profile2standardpermissions LEFT JOIN `vtiger_actionmapping` ON vtiger_actionmapping.`actionid` = vtiger_profile2standardpermissions.`operation` WHERE vtiger_actionmapping.`actionname` = "EditView" ');
				while ($row = $db->getRow($result)) {
					$db->insert('vtiger_profile2standardpermissions', [
						'profileid' => $row['profileid'],
						'tabid' => $row['tabid'],
						'operation' => $key,
						'permissions' => $row['permissions']
					]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function setPicklistDependncy($data)
	{
		$db = PearDatabase::getInstance();
		foreach ($data as $row) {
			$result = $db->pquery('SELECT id FROM vtiger_picklist_dependency WHERE  tabid=? AND sourcefield=? AND targetfield=? AND sourcevalue =? ;', [$row[1], $row[2], $row[3], $row[4]]);
			if (!$db->getRowCount($result)) {
				$db->insert('vtiger_picklist_dependency', [
					'id' => $db->getUniqueID('vtiger_picklist_dependency'),
					'tabid' => $row[1],
					'sourcefield' => $row[2],
					'targetfield' => $row[3],
					'sourcevalue' => $row[4],
					'targetvalues' => $row[5],
					'criteria' => $row[6]
				]);
			}
		}
	}

	public function getPicklistDependncy($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['13', getTabid('Products'), 'usageunit', 'subunit', 'pcs', '[""]', NULL],
					['14', getTabid('Products'), 'usageunit', 'subunit', 'pack', '[""]', NULL],
					['15', getTabid('Products'), 'usageunit', 'subunit', 'kg', '["50g","100g","300g","500g"]', NULL],
					['16', getTabid('Products'), 'usageunit', 'subunit', 'm', '[""]', NULL],
					['17', getTabid('Products'), 'usageunit', 'subunit', 'l', '["100ml","250ml","330ml","500ml"]', NULL]
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function mappedFields()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$rootDir = vglobal('root_directory');
		$dirName = 'cache/updates/MappedFields';
		if (is_dir($dirName)) {
			$dirBase = 'cache/updates/files/';
			$loc = 'modules/Settings/MappedFields/models';
			Vtiger_Functions::recurseCopy($dirBase . $loc, $loc, true);
			Vtiger_Functions::recurseDelete($dirBase . $loc);
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

	public function addActionMap()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$actions = ['FavoriteRecords', 'WatchingRecords', 'WatchingModule', 'OpenRecord'];
		foreach ($actions as $action) {
			$result = $db->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=? LIMIT 1;', [$action]);
			if (!$db->getRowCount($result)) {
				$securitycheck = 0;
				$key = $this->getMax('vtiger_actionmapping', 'actionid');
				$db->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?, ?);", [$key, $action, $securitycheck]);
			} else {
				$key = $db->getSingleValue($result);
			}
			$permission = 0;
			if ($action == 'FavoriteRecords') {
				$sql = "SELECT tabid, `name`  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND `name` IN ('SSingleOrders','SRequirementsCards','SRecurringOrders','SQuotes','SQuoteEnquiries','SCalculations');";
			} elseif ($action == 'OpenRecord') {
				$sql = "SELECT tabid, name  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name IN ('Assets','OSSSoldServices');";
			} else {
				$sql = "SELECT tabid, name  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name NOT IN ('SMSNotifier','ModComments','PBXManager','Events','Emails','');";
			}

			$result = $db->query($sql);
			$tabsData = $db->getArray($result);

			$resultP = $db->query("SELECT profileid FROM vtiger_profile;");
			while ($profileId = $db->getSingleValue($resultP)) {
				foreach ($tabsData as $tabData) {
					$tabid = $tabData['tabid'];
					$resultC = $db->pquery("SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;", [$profileId, $tabid, $key]);
					if ($db->getRowCount($resultC) == 0) {
						$db->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", [$profileId, $tabid, $key, $permission]);
					}
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function updateInventoryFields()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT colspan FROM `u_yf_squoteenquiries_invfield` WHERE `columnname` = ?', ['name']);
		$colSpan = $db->getSingleValue($result);
		if ((string) $colSpan === '1') {
			$data = [
				'u_yf_squoteenquiries_invfield' => [
					'colspan' => ['name' => '60', 'qty' => '40', 'comment1' => '0'],
					'sequence' => ['name' => '1', 'qty' => '2', 'comment1' => '3']
				],
				'u_yf_scalculations_invfield' => [
					'colspan' => ['name' => '40', 'qty' => '10', 'comment1' => '0', 'price' => '10', 'total' => '10', 'purchase' => '10', 'marginp' => '10', 'margin' => '10'],
					'sequence' => ['name' => '1', 'qty' => '2', 'comment1' => '3', 'price' => '4', 'total' => '5', 'purchase' => '6', 'marginp' => '7', 'margin' => '8']
				],
				'u_yf_squotes_invfield' => [
					'colspan' => ['name' => '20', 'qty' => '7', 'discount' => '7', 'marginp' => '10', 'margin' => '7', 'comment1' => '0', 'price' => '7', 'total' => '7', 'purchase' => '7', 'tax' => '7', 'gross' => '7', 'discountmode' => '1', 'taxmode' => '1', 'currency' => '1', 'net' => '7'],
					'sequence' => ['name' => '0', 'qty' => '1', 'discount' => '4', 'marginp' => '7', 'margin' => '8', 'comment1' => '6', 'price' => '2', 'total' => '3', 'purchase' => '6', 'tax' => '9', 'gross' => '10', 'discountmode' => '10', 'taxmode' => '11', 'currency' => '12', 'net' => '5']
				],
				'u_yf_ssingleorders_invfield' => [
					'colspan' => ['name' => '27', 'qty' => '7', 'discount' => '7', 'marginp' => '10', 'margin' => '7', 'tax' => '7', 'comment1' => '0', 'price' => '7', 'total' => '7', 'net' => '7', 'purchase' => '7', 'gross' => '7', 'discountmode' => '1', 'taxmode' => '1', 'currency' => '1'],
					'sequence' => ['name' => '0', 'qty' => '1', 'discount' => '4', 'marginp' => '7', 'margin' => '8', 'tax' => '9', 'comment1' => '7', 'price' => '2', 'total' => '3', 'net' => '5', 'purchase' => '6', 'gross' => '10', 'discountmode' => '11', 'taxmode' => '12', 'currency' => '13']
				],
				'u_yf_srecurringorders_invfield' => [
					'colspan' => ['name' => '50', 'qty' => '10', 'discount' => '10', 'marginp' => '10', 'margin' => '10', 'tax' => '10', 'comment1' => '0'],
					'sequence' => ['name' => '1', 'qty' => '2', 'discount' => '3', 'marginp' => '4', 'margin' => '5', 'tax' => '6', 'comment1' => '7']
				],
				'u_yf_srequirementscards_invfield' => [
					'colspan' => ['name' => '60', 'qty' => '40', 'comment1' => '0'],
					'sequence' => ['name' => '1', 'qty' => '2', 'comment1' => '3']
				],
				'u_yf_finvoice_invfield' => [
					'sequence' => ['name' => '0', 'qty' => '1', 'discount' => '4', 'comment1' => '11', 'currency' => '12', 'discountmode' => '13', 'taxmode' => '14', 'price' => '2', 'gross' => '8', 'net' => '5', 'tax' => '7', 'total' => '3']
				],
				'u_yf_finvoiceproforma_invfield' => [
					'sequence' => ['currency' => '1', 'discountmode' => '2', 'taxmode' => '3', 'name' => '4', 'qty' => '5', 'price' => '6', 'total' => '7', 'discount' => '8', 'net' => '9', 'tax' => '10', 'gross' => '11', 'comment1' => '12']
				],
				'u_yf_igdn_invfield' => [
					'sequence' => ['name' => '1', 'qty' => '4', 'price' => '5', 'total' => '6', 'comment1' => '5', '-' => '0', 'unit' => '3', 'ean' => '2']
				],
				'u_yf_igin_invfield' => [
					'sequence' => ['name' => '1', 'qty' => '4', 'price' => '5', 'total' => '6', 'comment1' => '5', '-' => '0', 'unit' => '3', 'ean' => '2']
				],
				'u_yf_igrn_invfield' => [
					'sequence' => ['name' => '1', 'qty' => '4', 'price' => '5', 'total' => '6', 'comment1' => '5', '-' => '0', 'unit' => '3', 'ean' => '2']
				],
				'u_yf_iidn_invfield' => [
					'sequence' => ['name' => '1', 'qty' => '4', 'comment1' => '5', '-' => '0', 'unit' => '3', 'price' => '5', 'total' => '6', 'ean' => '2']
				],
			];
			foreach ($data as $tabel => $cols) {
				$sql = '';
				foreach ($cols as $set => $colNames) {
					$sql .= empty($sql) ? "UPDATE $tabel SET $set = CASE " : ", $set = CASE ";
					foreach ($colNames as $name => $value) {
						$sql .= " WHEN columnname = '$name' THEN $value";
					}
					$sql .= ' ELSE ' . $set . ' END';
				}
				$db->query($sql);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function updateSettingFieldsMenu()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();

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
			['LBL_SYSTEM_TOOLS', 'LBL_ANNOUNCEMENT', 'adminIcon-company-information', 'LBL_ANNOUNCEMENT_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit', '2', '0', '0'],
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
			['LBL_SYSTEM_TOOLS', 'LBL_TYPE_NOTIFICATIONS', 'adminIcon-TypeNotification', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=List&parent=Settings', 12, 0, 0],
			['LBL_SYSTEM_TOOLS', 'LBL_NOTIFICATIONS_CONFIGURATION', 'adminIcon-NotificationConfiguration', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=Configuration&parent=Settings', 13, 0, 0]
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
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function updateSettingsMenu()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$settingMenu = [['1', 'LBL_USER_MANAGEMENT', '1', 'adminIcon-permissions', '0', NULL],
			['2', 'LBL_STUDIO', '2', 'adminIcon-standard-modules', '0', NULL],
			['3', 'LBL_COMPANY', '12', 'adminIcon-company-information', '0', NULL],
			['4', 'LBL_SYSTEM_TOOLS', '11', 'adminIcon-system-tools', '0', NULL],
			['5', 'LBL_INTEGRATION', '8', 'adminIcon-integration', '0', NULL],
			['6', 'LBL_PROCESSES', '13', 'adminIcon-processes', '0', NULL],
			['7', 'LBL_SECURITY_MANAGEMENT', '6', 'adminIcon-security', '0', NULL],
			['8', 'LBL_MAIL_TOOLS', '10', 'adminIcon-mail-tools', '0', NULL],
			['9', 'LBL_About_YetiForce', '26', 'adminIcon-about-yetiforce', '0', NULL],
			['11', 'LBL_ADVANCED_MODULES', '3', 'adminIcon-advenced-modules', '0', NULL],
			['12', 'LBL_CALENDAR_LABELS_COLORS', '4', 'adminIcon-calendar-labels-colors', '0', NULL],
			['13', 'LBL_SEARCH_AND_FILTERS', '5', 'adminIcon-search-and-filtres', '0', NULL],
			['14', 'LBL_LOGS', '7', 'adminIcon-logs', '0', NULL],
			['15', 'LBL_AUTOMATION', '9', 'adminIcon-automation', '0', NULL],
			['16', 'LBL_MENU_SUMMARRY', '0', 'userIcon-Home', '1', 'index.php?module=Vtiger&parent=Settings&view=Index']];

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

	public function updateMenu()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		//$columns = ['id', 'parentid', 'type', 'sequence', 'module', 'label', 'icon'];
		$menu = [
			['44', '0', '2', '0', NULL, 'MEN_VIRTUAL_DESK', 'userIcon-VirtualDesk'],
			['45', '44', '0', '0', 'Home', 'Home page', 'userIcon-Home'],
			['46', '44', '0', '1', 'Calendar', NULL, ''],
			['47', '0', '2', '1', NULL, 'MEN_COMPANIES_CONTACTS', 'userIcon-CompaniesAndContact'],
			['48', '47', '0', '0', 'Leads', NULL, ''],
			['49', '47', '0', '5', 'Contacts', NULL, ''],
			['50', '47', '0', '3', 'Vendors', NULL, ''],
			['51', '47', '0', '1', 'Accounts', NULL, ''],
			['52', '0', '2', '2', NULL, 'MEN_MARKETING', 'userIcon-Campaigns'],
			['54', '52', '0', '0', 'Campaigns', NULL, ''],
			['62', '118', '0', '7', 'PriceBooks', NULL, ''],
			['63', '0', '2', '5', NULL, 'MEN_SUPPORT', 'userIcon-Support'],
			['64', '63', '0', '0', 'HelpDesk', NULL, ''],
			['65', '63', '0', '1', 'ServiceContracts', NULL, ''],
			['66', '63', '0', '2', 'Faq', NULL, ''],
			['67', '0', '2', '4', NULL, 'MEN_PROJECTS', 'userIcon-Project'],
			['68', '67', '0', '0', 'Project', NULL, ''],
			['69', '67', '0', '1', 'ProjectMilestone', NULL, ''],
			['70', '67', '0', '2', 'ProjectTask', NULL, ''],
			['71', '0', '2', '6', NULL, 'MEN_ACCOUNTING', 'userIcon-Bookkeeping'],
			['72', '71', '0', '5', 'PaymentsIn', NULL, ''],
			['73', '71', '0', '4', 'PaymentsOut', NULL, ''],
			['76', '0', '2', '8', NULL, 'MEN_HUMAN_RESOURCES', 'userIcon-HumanResources'],
			['77', '76', '0', '0', 'OSSEmployees', NULL, ''],
			['78', '76', '0', '1', 'OSSTimeControl', NULL, ''],
			['79', '76', '0', '2', 'HolidaysEntitlement', NULL, ''],
			['80', '0', '2', '9', NULL, 'MEN_SECRETARY', 'userIcon-Secretary'],
			['81', '80', '0', '0', 'LettersIn', NULL, ''],
			['82', '80', '0', '1', 'LettersOut', NULL, ''],
			['83', '80', '0', '2', 'Reservations', NULL, ''],
			['84', '0', '2', '10', NULL, 'MEN_DATABESES', 'userIcon-Database'],
			['85', '84', '2', '0', NULL, 'MEN_PRODUCTBASE', NULL],
			['86', '84', '0', '1', 'Products', NULL, ''],
			['87', '84', '0', '2', 'OutsourcedProducts', NULL, ''],
			['88', '84', '0', '3', 'Assets', NULL, ''],
			['89', '84', '3', '4', NULL, NULL, NULL],
			['90', '84', '2', '5', NULL, 'MEN_SERVICESBASE', NULL],
			['91', '84', '0', '6', 'Services', NULL, ''],
			['92', '84', '0', '7', 'OSSOutsourcedServices', NULL, ''],
			['93', '84', '0', '8', 'OSSSoldServices', NULL, ''],
			['94', '84', '3', '9', NULL, NULL, NULL],
			['95', '84', '2', '10', NULL, 'MEN_LISTS', NULL],
			['96', '84', '0', '11', 'OSSMailView', NULL, ''],
			['97', '84', '0', '12', 'SMSNotifier', NULL, ''],
			['98', '84', '0', '13', 'PBXManager', NULL, ''],
			['99', '84', '0', '14', 'OSSMailTemplates', NULL, ''],
			['100', '84', '0', '15', 'Documents', NULL, ''],
			['106', '84', '0', '17', 'CallHistory', NULL, ''],
			['107', '84', '3', '18', NULL, NULL, NULL],
			['108', '84', '0', '23', 'NewOrders', NULL, ''],
			['109', '84', '0', '16', 'OSSPasswords', NULL, ''],
			['111', '44', '0', '3', 'Ideas', NULL, ''],
			['113', '44', '0', '2', 'OSSMail', NULL, ''],
			['114', '84', '0', '22', 'Reports', NULL, ''],
			['115', '84', '0', '19', 'Rss', NULL, ''],
			['116', '84', '0', '20', 'Portal', NULL, ''],
			['117', '84', '3', '21', NULL, NULL, NULL],
			['118', '0', '2', '3', NULL, 'MEN_SALES', 'userIcon-Sales'],
			['119', '118', '0', '0', 'SSalesProcesses', NULL, ''],
			['120', '118', '0', '1', 'SQuoteEnquiries', NULL, ''],
			['121', '118', '0', '2', 'SRequirementsCards', NULL, ''],
			['122', '118', '0', '3', 'SCalculations', NULL, ''],
			['123', '118', '0', '4', 'SQuotes', NULL, ''],
			['124', '118', '0', '5', 'SSingleOrders', NULL, ''],
			['125', '118', '0', '6', 'SRecurringOrders', NULL, ''],
			['126', '47', '0', '2', 'Partners', NULL, ''],
			['127', '47', '0', '4', 'Competition', NULL, ''],
			['128', '71', '0', '0', 'FBookkeeping', NULL, ''],
			['129', '71', '0', '1', 'FInvoice', NULL, ''],
			['130', '63', '0', '3', 'KnowledgeBase', NULL, ''],
			['131', '0', '2', '7', NULL, 'MEN_LOGISTICS', 'userIcon-VendorsAccounts'],
			['132', '131', '0', '10', 'IStorages', NULL, ''],
			['133', '131', '0', '1', 'IGRN', NULL, ''],
			['134', '71', '0', '2', 'FInvoiceProforma', NULL, ''],
			['135', '131', '0', '4', 'IGDN', NULL, ''],
			['136', '131', '0', '5', 'IIDN', NULL, ''],
			['137', '131', '0', '6', 'IGIN', NULL, ''],
			['138', '131', '0', '7', 'IPreOrder', NULL, ''],
			['139', '131', '0', '9', 'ISTDN', NULL, ''],
			['140', '131', '0', '0', 'ISTN', NULL, ''],
			['141', '131', '0', '8', 'ISTRN', NULL, ''],
			['142', '71', '0', '3', 'FCorectingInvoice', NULL, ''],
			['143', '131', '0', '2', 'IGRNC', 'IGRNC', ''],
			['144', '131', '0', '3', 'IGDNC', 'IGDNC', '']
		];
		$db->delete('yetiforce_menu', '`role` = ? ', [0]);
		$parents = [];
		foreach ($menu as $row) {
			$parent = $row[1] != 0 ? $parents[$row[1]] : 0;
			$module = $row[2] == 0 ? getTabid($row[4]) : $row[4];
			if ($row[2] == 0 && !vtlib_isModuleActive($row[4])) {
				continue;
			}
			$result = $db->insert('yetiforce_menu', ['role' => 0, 'parentid' => $parent, 'type' => $row[2], 'sequence' => $row[3], 'module' => $module, 'label' => $row[5], 'icon' => $row[6]]);
			if (is_array($result) && $row[1] == 0) {
				$parents[$row[0]] = $result['id'];
			}
		}

		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function updatePack()
	{
		$db = PearDatabase::getInstance();
		$db->update('vtiger_field', ['fieldlabel' => 'SINGLE_Accounts'], '`tabid` = ? AND columnname = ? AND fieldlabel = ?', [getTabid('SSalesProcesses'), 'related_to', 'Accounts']);
		$db->update('com_vtiger_workflows', ['test' => '[{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]'], '`module_name` = ? AND summary = ?', ['SQuoteEnquiries', 'Block edition']);
		$db->update('com_vtiger_workflows', ['test' => '[{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]'], '`module_name` = ? AND summary = ?', ['SRequirementsCards', 'Block edition']);
		$db->update('com_vtiger_workflows', ['test' => '[{"fieldname":"scalculations_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"scalculations_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]'], '`module_name` = ? AND summary = ?', ['SCalculations', 'Block edition']);
		$db->update('com_vtiger_workflows', ['test' => '[{"fieldname":"squotes_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squotes_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]'], '`module_name` = ? AND summary = ?', ['SQuotes', 'Block edition']);
		$db->update('com_vtiger_workflows', ['test' => '[{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]'], '`module_name` = ? AND summary = ?', ['SSingleOrders', 'Block edition']);
		$db->update('vtiger_field', ['displaytype' => 1], '`columnname` IN (?,?,?,?,?)', ['squoteenquiries_status', 'squotes_status', 'srequirementscards_status', 'scalculations_status', 'ssingleorders_status']);
		$db->update('vtiger_field', ['displaytype' => 10], '`columnname` = ? AND tabid = ?', ['qtyinstock', getTabid('Products')]);
		$db->update('vtiger_entityname', ['searchcolumn' => 'productname,ean'], '`modulename` = ?;', ['Products']);
		$db->update('vtiger_field', ['fieldlabel' => 'FL_TOTAL_TIME_H'], '`columnname` = ? AND tabid = ? AND fieldlabel = ?;', ['sum_time', getTabid('Project'), 'Total time [Project]']);
		$db->update('vtiger_knowledgebase_view', ['presence' => 0]);
		$db->update('vtiger_igdn_status', ['presence' => 0], '`igdn_status` = ?;', ['PLL_ACCEPTED']);
		$db->update('vtiger_igin_status', ['presence' => 0], '`igin_status` = ?;', ['PLL_ACCEPTED']);
		$db->update('vtiger_igrn_status', ['presence' => 0], '`igrn_status` = ?;', ['PLL_ACCEPTED']);
		$db->update('vtiger_iidn_status', ['presence' => 0], '`iidn_status` = ?;', ['PLL_ACCEPTED']);
		$db->update('vtiger_ipreorder_status', ['presence' => 0], '`ipreorder_status` = ?;', ['PLL_ACCEPTED']);
		$db->delete('vtiger_links', 'linklabel = ?', ['View History']);
		$columnNames = ['scalculations_no', 'squoteenquiries_no', 'squotes_no', 'ssalesprocesses_no', 'competition_no', 'partners_no', 'srecurringorders_no', 'ssingleorders_no'];
		$db->update('vtiger_field', ['displaytype' => 1], '`columnname` IN (' . $db->generateQuestionMarks($columnNames) . ') AND `uitype` = ?', array_merge($columnNames, [4]));
		$db->update('vtiger_field', ['generatedtype' => 1], '`columnname` IN (?,?) AND `tabid` = ?', ['description', 'email', getTabid('Competition')]);
		$db->update('vtiger_field', ['generatedtype' => 1], '`columnname` = ?  AND `tabid` IN (?,?)', ['ssalesprocessesid', getTabid('OSSOutsourcedServices'), getTabid('OSSSoldServices'), getTabid('OutsourcedProducts')]);
		$db->update('vtiger_field', ['generatedtype' => 1], '`columnname` = ? AND `tabid` = ?', ['email', getTabid('Partners')]);
		$db->update('vtiger_modentity_num', ['active' => 1], '`semodule` = ? AND `prefix` = ?', ['SQuotes', 'S-Q']);
	}

	public function updatePack2()
	{
		$db = PearDatabase::getInstance();
		$db->update('vtiger_field', ['uitype' => 300], '`tabid` IN (?, ?) AND columnname = ? AND uitype = ?', [getTabid('Calendar'), getTabid('Events'), 'description', 19]);
		$result = $db->query("SELECT * FROM `vtiger_user_module_preferences`;");
		while ($row = $db->getRow($result)) {
			if (!is_numeric($row['userid'])) {
				break;
			}
			$db->update('vtiger_user_module_preferences', ['userid' => 'Users:' . $row['userid']], '`tabid` = ? AND default_cvid = ? AND userid = ?', [$row['tabid'], $row['default_cvid'], $row['userid']]);
		}
		$db->update('vtiger_istdn_status', ['presence' => 0], 'istdn_status = ?', ['PLL_ACCEPTED']);
		$db->update('vtiger_istrn_status', ['presence' => 0], 'istrn_status = ?', ['PLL_ACCEPTED']);
		$db->update('vtiger_ssalesprocesses_status', ['presence' => 0], 'ssalesprocesses_status IN (?,?,?);', ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']);
		$db->update('vtiger_osssoldservices_renew', ['presence' => 0], 'osssoldservices_renew IN (?,?);', ['PLL_RENEWED', 'PLL_NOT_RENEWED']);
		$db->update('vtiger_assets_renew', ['presence' => 0], 'assets_renew IN (?,?);', ['PLL_RENEWED', 'PLL_NOT_RENEWED']);
		$db->update('vtiger_field', ['fieldlabel' => 'SINGLE_SSalesProcesses'], '`tabid` IN (?, ?, ?, ?) AND columnname = ? AND fieldlabel = ?', [getTabid('Assets'), getTabid('OSSOutsourcedServices'), getTabid('OSSSoldServices'), getTabid('OutsourcedProducts'), 'ssalesprocessesid', 'SSalesProcesses']);
		$db->update('vtiger_usageunit', ['usageunit' => 'pack'], 'usageunit = ?', ['Pack']);
		$db->update('vtiger_usageunit', ['usageunit' => 'm'], 'usageunit = ?', ['M']);
		$db->update('vtiger_field', ['presence' => 0], '`tabid` = ? AND columnname IN (?,?,?,?);', [getTabid('Assets'), 'asset_no', 'assetstatus', 'dateinservice', 'smownerid']);
		$db->update('vtiger_field', ['generatedtype' => 1], '`tabid` = ? AND columnname IN (?,?);', [getTabid('Assets'), 'ssalesprocessesid', 'parent_id']);
		$db->update('vtiger_field', ['generatedtype' => 1], '`tabid` = ? AND columnname IN (?);', [getTabid('OSSOutsourcedServices'), 'ssalesprocessesid']);
		$db->update('vtiger_field', ['generatedtype' => 1], '`tabid` = ? AND columnname IN (?,?,?,?,?,?,?,?,?,?);', [getTabid('OSSSoldServices'), 'dateinservice', 'datesold', 'invoice', 'ordertime', 'parent_id', 'productname', 'pscategory', 'serviceid', 'ssalesprocessesid', 'ssservicesstatus']);
		$db->update('vtiger_field', ['presence' => 0], '`tabid` = ? AND columnname IN (?);', [getTabid('OSSSoldServices'), 'ssservicesstatus']);
		$db->update('vtiger_field', ['generatedtype' => 1], '`tabid` = ? AND columnname IN (?);', [getTabid('OutsourcedProducts'), 'ssalesprocessesid']);
		$db->update('vtiger_field', ['displaytype' => 1], '`tabid` = ? AND columnname IN (?);', [getTabid('SRequirementsCards'), 'srequirementscards_no']);
		$db->update('vtiger_istn_type', ['presence' => 0]);
		// remove 
		$db->update('vtiger_field', ['defaultvalue' => 'PLL_DRAFT'], '`tabid` = ? AND columnname IN (?) AND defaultvalue = ?;', [getTabid('SQuoteEnquiries'), 'squoteenquiries_status', 'LBL_DRAFT']);
		$result = $db->query("SELECT 1 FROM `a_yf_notification_type`;");
		if (!$db->getRowCount($result)) {
			$db->insert('a_yf_notification_type', [
				'id' => 0,
				'name' => 'LBL_MESSAGES_FROM_USERS',
				'role' => 0,
				'width' => 3,
				'height' => 3,
				'icon' => NULL,
				'presence' => 0
			]);
		}
		// update customView
		$modules = [];
		$modulesDone = [];
		$result = $db->query("SELECT * FROM `vtiger_customview` ORDER BY presence;");
		while ($row = $db->getRow($result)) {
			if ($row['presence'] == 1 && !in_array($row['entitytype'], $modulesDone) && $row['viewname'] == 'All') {
				$db->update('vtiger_customview', ['presence' => 0], 'cvid = ? AND `viewname` = ?', [$row['cvid'], 'All']);
				$modulesDone[] = $row['entitytype'];
			} elseif ($row['presence'] == 0) {
				$modulesDone[] = $row['entitytype'];
			}
			if ($row['viewname'] == 'All') {
				$modules[$row['entitytype']] = $row['cvid'];
			}
			if (!array_key_exists($row['entitytype'], $modules)) {
				$modules[$row['entitytype']] = $row['cvid'];
			}
		}
		$diff = array_diff_key($modules, array_flip($modulesDone));
		if ($diff) {
			$db->update('vtiger_customview', ['presence' => 0], 'cvid IN (' . generateQuestionMarks($diff) . ');', [$diff]);
		}
		//
		$db->delete('vtiger_ws_fieldtype', 'uitype = ? ', [78]);
		$db->update('vtiger_field', ['summaryfield' => 0], '`tabid` = ? AND columnname IN (?,?,?);', [getTabid('Products'), 'manufacturer', 'sales_end_date', 'smownerid']);
		$db->update('vtiger_field', ['summaryfield' => 1], '`tabid` = ? AND columnname IN (?);', [getTabid('Products'), 'qtyinstock']);
		$db->update('vtiger_assets_renew', ['presence' => 0], '`assets_renew` = ?;', ['PLL_NOT_APPLICABLE']);
		$db->update('vtiger_osssoldservices_renew', ['presence' => 0], '`osssoldservices_renew` = ?;', ['PLL_NOT_APPLICABLE']);
		$db->update('vtiger_istn_status', ['presence' => 0], '`istn_status` = ?;', ['PLL_ACCEPTED']);
		$db->update('vtiger_knowledgebase_status', ['presence' => 0], '`knowledgebase_status` IN (?,?,?);', ['PLL_CANCELLED', 'PLL_ACCEPTED', 'PLL_ARCHIVES']);
		$this->updateByCase($this->getDataByCase(1));
		$this->updateByCase($this->getDataByCase(2));
		$db->update('vtiger_field', ['displaytype' => 10], '`columnname` = ? AND tabid = ?;', ['used_units', getTabid('ServiceContracts')]);
		$result = $db->pquery("SELECT 1 FROM `vtiger_field` WHERE fieldname = ? AND uitype IN (?,?,?);", ['servicecategory', 15, 16, 33]);
		if (!$db->getRowCount($result)) {
			$this->setTablesScheme($this->getTablesAction(2));
		}
		$db->update('vtiger_users', ['defaulteventstatus' => 'PLL_PLANNED'], '`defaulteventstatus` IN (?,?);', ['Held', 'Not Held']);
		$result = $db->query("SELECT 1 FROM `u_yf_github` LIMIT 1;");
		if (!$db->getRowCount($result)) {
			$db->insert('u_yf_github', [
				'client_id' => '',
				'token' => '',
				'username' => ''
			]);
		}
		$db->update('vtiger_field', ['fieldlabel' => 'FL_SERVICE_CONTRACTS'], '`columnname` = ? AND tabid = ?;', ['servicecontractsid', getTabid('HelpDesk')]);
	}

	public function getDataByCase($index)
	{
		$data = [];
		switch ($index) {
			case 1:
				$data = [
					'vtiger_field' => [
						[
							'set' => 'quickcreatesequence',
							'when' => 'columnname',
							'data' => ['subject' => '1', 'smownerid' => '5', 'content' => '6', 'category' => '3', 'knowledgebase_view' => '4', 'knowledgebase_status' => '2']
						],
						'where' => " WHERE columnname IN ('smownerid', 'content', 'category', 'subject', 'parentid', 'knowledgebase_view') AND tabid = " . getTabid('KnowledgeBase')
					]
				];
				break;
			case 2:
				$data = [
					'vtiger_field' => [
						[
							'set' => 'quickcreatesequence',
							'when' => 'columnname',
							'data' => ['subject' => '1', 'parentid' => '2', 'smownerid' => '3']
						],
						'where' => " WHERE columnname IN ('subject', 'parentid', 'smownerid') AND tabid = " . getTabid('IStorages')
					]
				];
				break;
			default:
				break;
		}
		return $data;
	}

	public function updateByCase($data)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		foreach ($data as $tabel => $dataCase) {
			$sql = '';
			$where = $dataCase['where'];
			unset($dataCase['where']);
			foreach ($dataCase as $case) {
				$sql .= empty($sql) ? "UPDATE $tabel SET " . $case['set'] . ' = CASE ' : ', ' . $case['set'] . ' = CASE ';
				foreach ($case['data'] as $name => $value) {
					$sql .= ' WHEN ' . $case['when'] . " = '$name' THEN $value";
				}
				$sql .= ' ELSE ' . $case['set'] . ' END';
			}
			$db->query($sql . $where);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
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
				$result = $db->pquery('SELECT sequence,block FROM vtiger_field WHERE columnname = ? AND tabid = ?;', [$info['moveFor'], getTabid($info['moduleName'])]);
			} elseif (!empty($info['toBlock'])) {
				$result = $db->pquery('SELECT MAX(vtiger_field.sequence) AS sequence,vtiger_blocks.`blockid` as block FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_field.tabid = ?', [$info['toBlock'], $tabId]);
				if(!$result->rowCount()){
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
			$this->deleteBlocks($removeBlocks);
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

	public function getFieldsToMove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'sum_time' => ['moduleName' => 'Project', 'moveFor' => 'progress', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_SUMMARY', 'removeBlock' => true],
				];
				break;
			case 2:
				$fields = [
					'ticket_no' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'createdtime' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'smcreatorid' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'modifiedtime' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'shownerid' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'closedtime' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'crmactivity' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'was_read' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'ordertime' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
					'sum_time' => ['moduleName' => 'HelpDesk', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_TICKET_INFORMATION'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function getFieldsToRemove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'vtiger_project' => ['sum_time_pt', 'sum_time_h', 'sum_time_all'],
					'vtiger_troubletickets' => ['projectid'],
					'vtiger_assets' => ['tagnumber', 'shippingmethod'],
					'vtiger_leaddetails' => ['emailoptout']
				];
				break;
			case 2:
				$fields = [
					'vtiger_troubletickets' => ['category']
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function inactiveFields($fields)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		foreach ($fields as $moduleName => $columnsName) {
			if (empty($columnsName)) {
				continue;
			}
			foreach ($columnsName as $columnName) {
				$db->update('vtiger_field', ['presence' => 1], '`columnname` = ? AND tabid = ? ;', [$columnName, getTabid($moduleName)]);
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
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

	public function getAlterTables($index)
	{
		$field = [];
		switch ($index) {
			case 1:
				$field = [
					['type' => ['add'], 'name' => 'referer', 'table' => 'o_yf_access_for_admin', 'sql' => 'ALTER TABLE `o_yf_access_for_admin` ADD COLUMN `referer` varchar(300) DEFAULT NULL ;'],
					['type' => ['add'], 'name' => 'referer', 'table' => 'o_yf_access_for_user', 'sql' => 'ALTER TABLE `o_yf_access_for_user` ADD COLUMN `referer` varchar(300) DEFAULT NULL ;'],
					['type' => ['add'], 'name' => 'referer', 'table' => 'o_yf_access_to_record', 'sql' => 'ALTER TABLE `o_yf_access_to_record` ADD COLUMN `referer` varchar(300) DEFAULT NULL ;'],
					['type' => ['add'], 'name' => 'presence', 'table' => 'vtiger_dataaccess', 'sql' => 'ALTER TABLE `vtiger_dataaccess` ADD COLUMN `presence` tinyint(1) NULL DEFAULT 1 ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_scalculations_invmap',
						'sql' => 'ALTER TABLE `u_yf_scalculations_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_squoteenquiries_invmap',
						'sql' => 'ALTER TABLE `u_yf_squoteenquiries_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_squotes_invmap',
						'sql' => 'ALTER TABLE `u_yf_squotes_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_srecurringorders_invmap',
						'sql' => 'ALTER TABLE `u_yf_srecurringorders_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_srequirementscards_invmap',
						'sql' => 'ALTER TABLE `u_yf_srequirementscards_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
					['type' => ['add', 'Column_name'], 'name' => 'tofield', 'table' => 'u_yf_ssingleorders_invmap',
						'sql' => 'ALTER TABLE `u_yf_ssingleorders_invmap` 
							CHANGE `field` `field` varchar(50) NOT NULL after `module` , 
							CHANGE `tofield` `tofield` varchar(50) NOT NULL after `field` , 
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`module`,`field`,`tofield`) ;'],
				];
				break;
			case 2: $field = [
					['type' => ['add'], 'name' => 'header_height', 'table' => 'a_yf_pdf', 'sql' => 'ALTER TABLE `a_yf_pdf` 
						ADD COLUMN `header_height` smallint(2) unsigned NOT NULL after `margin_right` , 
						ADD COLUMN `footer_height` smallint(2) unsigned NOT NULL after `header_height`;'],
					['type' => ['change', 'Type'], 'name' => 'time_start', 'validType' => 'time', 'table' => 'vtiger_activity', 'sql' => 'ALTER TABLE `vtiger_activity` 
						CHANGE `time_start` `time_start` time   NULL after `due_date` , 
						CHANGE `time_end` `time_end` time   NULL after `time_start` ;'],
					['type' => ['change', 'Type'], 'name' => 'permissionsrelatedfield', 'validType' => 'varchar', 'table' => 'vtiger_role', 'sql' => "ALTER TABLE `vtiger_role` CHANGE `permissionsrelatedfield` `permissionsrelatedfield` varchar(10) NOT NULL DEFAULT '0' after `editrelatedrecord` ;"],
					['type' => ['add', 'Key_name'], 'name' => 'actionname', 'table' => 'vtiger_actionmapping', 'sql' => 'ALTER TABLE `vtiger_actionmapping` ADD KEY `actionname`(`actionname`) ;'],
					['type' => ['add'], 'name' => 'content', 'table' => 'l_yf_sqltime', 'sql' => 'ALTER TABLE `l_yf_sqltime` 
						CHANGE `id` `id` int(19) NOT NULL first , 
						CHANGE `data` `content` text NULL after `type` , 
						CHANGE `date` `date` datetime NULL after `content` , 
						ADD COLUMN `group` int(19) NULL after `qtime` , 
						ADD KEY `type`(`type`), 
						ADD KEY `group`(`group`) ;'],
					['type' => ['add', 'Key_name'], 'name' => 'quickcreate', 'table' => 'vtiger_field', 'sql' => 'ALTER TABLE `vtiger_field` ADD KEY `quickcreate`(`quickcreate`) ;'],
					['type' => ['add', 'Key_name'], 'name' => 'name', 'table' => 'vtiger_tab', 'sql' => 'ALTER TABLE `vtiger_tab` ADD KEY `name`(`name`,`presence`) ;'],
					['type' => ['add'], 'name' => 'featured', 'table' => 'vtiger_customview', 'sql' => 'ALTER TABLE `vtiger_customview` 
						ADD COLUMN `featured` tinyint(1)   NULL DEFAULT 0 after `privileges` , 
						ADD COLUMN `sequence` int(11)   NULL after `featured` , 
						ADD COLUMN `presence` tinyint(1)   NULL DEFAULT 1 after `sequence` , 
						ADD COLUMN `description` text  NULL after `presence` , 
						ADD COLUMN `sort` varchar(30)  NULL DEFAULT "" after `description` ;'],
					['type' => ['change', 'Type'], 'name' => 'userid', 'validType' => 'varchar', 'table' => 'vtiger_user_module_preferences', 'sql' => 'ALTER TABLE `vtiger_user_module_preferences` CHANGE `userid` `userid` varchar(30) NOT NULL first ; '],
					['type' => ['change', 'Type'], 'name' => 'template_members', 'validType' => 'text', 'table' => 'a_yf_pdf', 'sql' => 'ALTER TABLE `a_yf_pdf` CHANGE `template_members` `template_members` text NOT NULL after `watermark_image` ;'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_scalculations_inventory', 'sql' => 'ALTER TABLE `u_yf_scalculations_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_squoteenquiries_inventory', 'sql' => 'ALTER TABLE `u_yf_squoteenquiries_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_squotes_inventory', 'sql' => 'ALTER TABLE `u_yf_squotes_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_srecurringorders_inventory', 'sql' => 'ALTER TABLE `u_yf_srecurringorders_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_srequirementscards_inventory', 'sql' => 'ALTER TABLE `u_yf_srequirementscards_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['add'], 'name' => 'qtyparam', 'table' => 'u_yf_ssingleorders_inventory', 'sql' => 'ALTER TABLE `u_yf_ssingleorders_inventory` 
						ADD COLUMN `qtyparam` tinyint(1) NOT NULL DEFAULT "0";'],
					['type' => ['change', 'Type'], 'name' => 'time_start', 'validType' => 'time', 'table' => 'vtiger_activity', 'sql' => 'ALTER TABLE `vtiger_activity` 
						CHANGE `time_start` `time_start` time   NULL after `due_date` , 
						CHANGE `time_end` `time_end` time   NULL after `time_start` , 
						CHANGE `subprocess` `subprocess` int(19)   NULL after `process` , 
						CHANGE `followup` `followup` int(19)   NULL after `subprocess` ;'],
					['type' => ['add'], 'name' => 'presence', 'table' => 'a_yf_notification_type', 'sql' => 'ALTER TABLE `a_yf_notification_type` 
						CHANGE `id` `id` int(19) unsigned   NOT NULL first , 
						ADD COLUMN `presence` tinyint(1)   NOT NULL DEFAULT 1 after `icon` ;'],
					['type' => ['add'], 'name' => 'title', 'table' => 'l_yf_notification', 'sql' => 'ALTER TABLE `l_yf_notification` 
						ADD COLUMN `title` varchar(255) NULL after `userid` , 
						CHANGE `message` `message` text NULL after `title` , 
						CHANGE `reletedid` `reletedid` int(11)   NULL after `message` , 
						ADD COLUMN `reletedmodule` varchar(30) NULL after `reletedid` , 
						CHANGE `time` `time` datetime   NULL after `reletedmodule` ;'],
					['type' => ['add'], 'name' => 'title', 'table' => 'l_yf_notification_archive', 'sql' => 'ALTER TABLE `l_yf_notification_archive` 
						ADD COLUMN `title` varchar(255)  NULL after `userid` , 
						CHANGE `message` `message` text  NULL after `title` , 
						CHANGE `reletedid` `reletedid` int(11)   NULL after `message` , 
						ADD COLUMN `reletedmodule` varchar(30)   NULL after `reletedid` , 
						CHANGE `time` `time` datetime   NULL after `reletedmodule` , 
						CHANGE `mark_user` `mark_user` int(11)   NULL DEFAULT 0 after `time` , 
						CHANGE `mark_time` `mark_time` datetime   NULL after `mark_user` ;'],
					['type' => ['change', 'Default'], 'name' => 'assetstatus', 'validType' => 'PLL_DRAFT', 'table' => 'vtiger_assets', 'sql' => 'ALTER TABLE `vtiger_assets` 
						CHANGE `assetstatus` `assetstatus` varchar(200) NULL DEFAULT "PLL_DRAFT" after `dateinservice`;'],
					['type' => ['change', 'Default'], 'name' => 'defaulteventstatus', 'validType' => null, 'table' => 'vtiger_users', 'sql' => 'ALTER TABLE `vtiger_users` 
	CHANGE `defaulteventstatus` `defaulteventstatus` varchar(50) NULL after `rowheight`;'],
				];
				break;
			default:
				break;
		}
		return $field;
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
					default:
						$checkSql = 'SHOW COLUMNS FROM `' . $alter['table'] . '` LIKE "' . $alter['name'] . '";';
						break;
				}
				$result = $db->query($checkSql);
				$num = $result->rowCount();
				if (( $num == 0 && $alter['type'][0] == 'add') || ($num > 0 && $alter['type'][0] == 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && $alter['type'][0] == 'change') {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	function getDataAccess($index)
	{
		$data = [];
		switch ($index) {
			case 1:
				$data = [
					['base' => ['14', 'IStorages', 'Check for parent storage', 'a:1:{i:0;a:2:{s:2:"cf";b:0;s:2:"an";s:20:"IStorages!!checkType";}}', '0'],
						'cnd' => [], 'type' => 'add'],
					['base' => ['15', 'IStorages', 'Prevents parents loop', 'a:1:{i:0;a:2:{s:2:"cf";b:0;s:2:"an";s:25:"IStorages!!checkHierarchy";}}', '0'],
						'cnd' => [], 'type' => 'add'],
				];
				break;
			default:
				break;
		}
		return $data;
	}

	function setDataAccess($data)
	{
		$db = PearDatabase::getInstance();
		foreach ($data as $dataAccess) {
			$result = $db->pquery("SELECT dataaccessid FROM vtiger_dataaccess WHERE module_name = ? AND `summary` = ?;", [$dataAccess['base'][1], $dataAccess['base'][2]]);
			$id = $db->getSingleValue($result);
			if ($id && $dataAccess['type'] == 'remove') {
				$db->delete('vtiger_dataaccess_cnd', 'dataaccessid = ?', [$id]);
				$db->delete('vtiger_dataaccess', 'dataaccessid = ?', [$id]);
			} elseif (!$id && $dataAccess['type'] == 'add') {
				array_shift($dataAccess['base']);
				$db->pquery('INSERT INTO vtiger_dataaccess (module_name,summary,data,presence) VALUES(?, ?, ?, ?)', $dataAccess['base']);
				$id = $db->getLastInsertID();
				foreach ($dataAccess['cnd'] as $values) {
					$values[0] = $id;
					$db->pquery('INSERT INTO vtiger_dataaccess_cnd (dataaccessid,fieldname,comparator,val,required,field_type) VALUES(?, ?, ?, ?, ?, ?)', $values);
				}
			}
		}
	}

	public function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					['type' => 'add', 'sql' => '`u_yf_istorages_products`(
						`crmid` int(19) NULL  , 
						`relcrmid` int(19) NULL  , 
						`qtyinstock` decimal(25,3) NULL  , 
						KEY `crmid`(`crmid`) , 
						KEY `relcrmid`(`relcrmid`) , 
						CONSTRAINT `u_yf_istorages_products_ibfk_1` 
						FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE , 
						CONSTRAINT `u_yf_istorages_products_ibfk_2` 
						FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE 
					) '],
					['type' => 'add', 'sql' => '`l_yf_notification` (
						`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
						`type` tinyint(1) unsigned NOT NULL DEFAULT "0",
						`userid` int(11) unsigned NOT NULL,
						`message` varchar(300) DEFAULT NULL,
						`reletedid` int(11) DEFAULT NULL,
						`time` datetime DEFAULT NULL,
						PRIMARY KEY (`id`)
					) '],
					['type' => 'add', 'sql' => '`l_yf_notification_archive` (
						`id` int(11) unsigned NOT NULL,
						`type` tinyint(1) unsigned NOT NULL DEFAULT "0",
						`userid` int(11) unsigned NOT NULL,
						`message` varchar(300) DEFAULT NULL,
						`reletedid` int(11) DEFAULT NULL,
						`time` datetime DEFAULT NULL,
						`mark_user` int(11) DEFAULT "0",
						`mark_time` datetime DEFAULT NULL,
						PRIMARY KEY (`id`)
					) '],
					['type' => 'add', 'sql' => '`u_yf_watchdog_module` (
						`userid` int(11) unsigned NOT NULL,
						`module` int(11) unsigned NOT NULL,
						PRIMARY KEY (`userid`,`module`),
						KEY `userid` (`userid`)
					) '],
					['type' => 'add', 'sql' => '`u_yf_watchdog_record` (
						`userid` int(11) unsigned NOT NULL,
						`record` int(11) NOT NULL,
						`state` tinyint(1) unsigned NOT NULL DEFAULT "0",
						PRIMARY KEY (`userid`,`record`),
						KEY `userid` (`userid`),
						KEY `record` (`record`),
						KEY `userid_2` (`userid`,`record`,`state`),
						CONSTRAINT `u_yf_watchdog_record_ibfk_1` FOREIGN KEY (`record`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
					) '],
					['type' => 'add', 'sql' => '`a_yf_featured_filter` (
						`user` varchar(30) NOT NULL,
						`cvid` int(19) NOT NULL,
						PRIMARY KEY (`user`,`cvid`),
						KEY `cvid` (`cvid`),
						CONSTRAINT `a_yf_featured_filter_ibfk_1` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
					) '],
					['type' => 'add', 'sql' => '`a_yf_notification_type` (
						`id` int(19) unsigned NOT NULL AUTO_INCREMENT,
						`name` varchar(50) NOT NULL,
						`role` tinyint(5) unsigned NOT NULL DEFAULT "0",
						`width` tinyint(2) NOT NULL DEFAULT "3",
						`height` tinyint(2) NOT NULL DEFAULT "3",
						`icon` varchar(20) DEFAULT NULL,
						PRIMARY KEY (`id`)) '],
				];
				break;
			case 2: $tables = [
					['type' => 'remove', 'sql' => 'vtiger_servicecategory_seq'],
					['type' => 'remove', 'sql' => 'vtiger_servicecategory'],
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
			if ($table['type'] == 'add') {
				$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE=InnoDB DEFAULT CHARSET="utf8";');
			} elseif ($table['type'] == 'remove') {
				$db->query('DROP TABLE IF EXISTS ' . $table['sql'] . ';');
			}
		}
	}

	function getWorkflowTaskType($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => 'add', 'data' => ['15', 'VTUpdateRelatedFieldTask', 'LBL_UPDATE_RELATED_FIELD', 'VTUpdateRelatedFieldTask', 'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.inc', 'com_vtiger_workflow/taskforms/VTUpdateRelatedFieldTask.tpl', '{"include":[],"exclude":[]}', '']],
					['type' => 'add', 'data' => ['16', 'VTWatchdog', 'LBL_NOTIFICATIONS', 'VTWatchdog', 'modules/com_vtiger_workflow/tasks/VTWatchdog.inc', 'com_vtiger_workflow/taskforms/VTWatchdog.tpl', '{"include":[],"exclude":[]}', NULL]],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function setWorkflowTaskType($types)
	{
		$db = PearDatabase::getInstance();
		foreach ($types as $type) {
			if (empty($type)) {
				continue;
			}
			list($id, $taskTypeName, $label, $className, $classPath, $templatePath, $modules, $sourceModule) = $type['data'];
			$result = $db->pquery('SELECT 1 FROM `com_vtiger_workflow_tasktypes` WHERE `classname` = ?;', [$className]);
			if ($result->rowCount() == 0 && $type['type'] == 'add') {
				$db->insert('com_vtiger_workflow_tasktypes', [
					'id' => $db->getUniqueID("com_vtiger_workflow_tasktypes"),
					'tasktypename' => $taskTypeName,
					'label' => $label,
					'classname' => $className,
					'classpath' => $classPath,
					'templatepath' => $templatePath,
					'modules' => $modules,
					'sourcemodule' => $sourceModule
				]);
			}
		}
	}

	function getInventoryFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => 'add', 'moduleName' => 'SCalculations', 'data' => ['5', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '4', '1', '0', NULL, '10']],
					['type' => 'add', 'moduleName' => 'SCalculations', 'data' => ['6', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '5', '1', '0', NULL, '10']],
					['type' => 'add', 'moduleName' => 'SCalculations', 'data' => ['7', 'purchase', 'LBL_PURCHASE', 'Purchase', '0', '0', '6', '1', '0', NULL, '10']],
					['type' => 'add', 'moduleName' => 'SCalculations', 'data' => ['8', 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', '0', '0', '7', '1', '0', NULL, '10']],
					['type' => 'add', 'moduleName' => 'SCalculations', 'data' => ['9', 'margin', 'LBL_MARGIN', 'Margin', '0', '0', '8', '1', '0', NULL, '10']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['7', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '2', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['8', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '3', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['9', 'purchase', 'LBL_PURCHASE', 'Purchase', '0', '0', '6', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['10', 'tax', 'LBL_TAX', 'Tax', '0', '0', '8', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['12', 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', '0', '0', '10', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['13', 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', '0', '0', '11', '0', '0', NULL, '1']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['14', 'taxmode', 'LBL_TAX_MODE', 'TaxMode', '0', '0', '12', '0', '0', NULL, '1']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['15', 'currency', 'LBL_CURRENCY', 'Currency', '0', '', '13', '0', '0', NULL, '1']],
					['type' => 'add', 'moduleName' => 'SQuotes', 'data' => ['16', 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', '0', '0', '5', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['8', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '2', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['9', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '3', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['10', 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', '0', '0', '5', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['11', 'purchase', 'LBL_PURCHASE', 'Purchase', '0', '0', '6', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['12', 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', '0', '0', '10', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['13', 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', '0', '0', '11', '0', '0', NULL, '1']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['14', 'taxmode', 'LBL_TAX_MODE', 'TaxMode', '0', '0', '12', '0', '0', NULL, '1']],
					['type' => 'add', 'moduleName' => 'SSingleOrders', 'data' => ['15', 'currency', 'LBL_CURRENCY', 'Currency', '0', '', '13', '0', '0', NULL, '1']],
				];
				break;
			case 'FInvoice':
				$fields = [
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '0', '1', '0', '{"modules":["Products","Services"],"limit":" "}', '30']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '1', '1', '0', '{}', '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['3', 'discount', 'LBL_DISCOUNT', 'Discount', '0', '0', '4', '1', '0', '{}', '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['4', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '11', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['5', 'currency', 'LBL_CURRENCY', 'Currency', '0', '', '12', '0', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['6', 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', '0', '0', '13', '0', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['7', 'taxmode', 'LBL_TAX_MODE', 'TaxMode', '0', '0', '14', '0', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['8', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '2', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['9', 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', '0', '0', '8', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['10', 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', '0', '0', '5', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['12', 'tax', 'LBL_TAX', 'Tax', '0', '0', '7', '1', '0', NULL, '7']],
					['type' => 'add', 'moduleName' => 'FInvoice', 'data' => ['13', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '3', '1', '0', NULL, '7']],
				];
				break;
			case 'IGRN':
				$fields = [
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', NULL, '5']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', NULL, '12']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', NULL, '15']],
					['type' => 'add', 'moduleName' => 'IGRN', 'data' => [9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10]],
				];
				break;
			case 'FInvoiceProforma':
				$fields = [
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['1', 'currency', 'LBL_CURRENCY', 'Currency', '0', '', '1', '0', '0', '', '1']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['2', 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', '0', '0', '2', '0', '0', '', '1']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['3', 'taxmode', 'LBL_TAX_MODE', 'TaxMode', '0', '0', '3', '0', '0', '', '1']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['4', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '4', '1', '0', '{"modules":["Products","Services"],"limit":" "}', '30']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['5', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['6', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['7', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['8', 'discount', 'LBL_DISCOUNT', 'Discount', '0', '0', '8', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['9', 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', '0', '0', '9', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['10', 'tax', 'LBL_TAX', 'Tax', '0', '0', '10', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['11', 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', '0', '0', '11', '1', '0', '{}', '10']],
					['type' => 'add', 'moduleName' => 'FInvoiceProforma', 'data' => ['12', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '12', '2', '0', '{}', '0']],
				];
				break;
			case 'IGDN':
				$fields = [
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', NULL, '5']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', NULL, '12']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', NULL, '15']],
					['type' => 'add', 'moduleName' => 'IGDN', 'data' => ['9', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', NULL, '10']],
				];
				break;
			case 'IIDN':
				$fields = [
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', NULL, '5']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', NULL, '12']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', NULL, '15']],
					['type' => 'add', 'moduleName' => 'IIDN', 'data' => [14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10]],
				];
				break;
			case 'IGIN':
				$fields = [
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', NULL, '5']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', NULL, '12']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', NULL, '15']],
					['type' => 'add', 'moduleName' => 'IGIN', 'data' => [9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10]],
				];
				break;
			case 'IPreOrder':
				$fields = [
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['3', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['4', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', '{}', '5']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['6', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['7', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['8', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['9', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IPreOrder', 'data' => ['10', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', NULL, '10']],
				];
				break;
			case 'ISTDN':
				$fields = [
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', '', '5']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', '', '12']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', '', '15']],
					['type' => 'add', 'moduleName' => 'ISTDN', 'data' => ['9', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', NULL, '10']],
				];
				break;
			case 'ISTRN':
				$fields = [
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', '', '5']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', '', '12']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', '', '15']],
					['type' => 'add', 'moduleName' => 'ISTRN', 'data' => ['9', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', NULL, '10']],
				];
				break;
			case 'FCorectingInvoice':
				$fields = [
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '0', '1', '0', '{"modules":["Products","Services"],"limit":" "}', '30']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '1', '1', '0', '{}', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['3', 'discount', 'LBL_DISCOUNT', 'Discount', '0', '0', '4', '1', '0', '{}', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['4', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '11', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['5', 'currency', 'LBL_CURRENCY', 'Currency', '0', '', '12', '0', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['6', 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', '0', '0', '13', '0', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['7', 'taxmode', 'LBL_TAX_MODE', 'TaxMode', '0', '0', '14', '0', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['8', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '2', '1', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['9', 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', '0', '0', '8', '1', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['10', 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', '0', '0', '5', '1', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['11', 'tax', 'LBL_TAX', 'Tax', '0', '0', '7', '1', '0', '', '7']],
					['type' => 'add', 'moduleName' => 'FCorectingInvoice', 'data' => ['12', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '3', '1', '0', '', '7']],
				];
				break;
			case 'IGRNC':
				$fields = [
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', '', '5']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', '', '12']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', '', '15']],
					['type' => 'add', 'moduleName' => 'IGRNC', 'data' => ['9', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', '', '10']],
				];
				break;
			case 'IGDNC':
				$fields = [
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['1', 'name', 'LBL_ITEM_NAME', 'Name', '0', '', '1', '1', '0', '{"modules":"Products","limit":" "}', '29']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['2', 'qty', 'LBL_QUANTITY', 'Quantity', '0', '1', '5', '1', '0', '{}', '15']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['3', 'price', 'LBL_UNIT_PRICE', 'UnitPrice', '0', '0', '6', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['4', 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', '0', '0', '7', '1', '0', '{}', '12']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['5', 'comment1', 'LBL_COMMENT', 'Comment', '0', '', '5', '2', '0', '{}', '0']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['6', '-', 'LBL_ITEM_NUMBER', 'ItemNumber', '0', '', '0', '1', '0', '', '5']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['7', 'unit', 'LBL_UNIT', 'Value', '0', '', '3', '1', '10', '', '12']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['8', 'ean', 'LBL_EAN', 'Value', '0', '', '2', '1', '10', '', '15']],
					['type' => 'add', 'moduleName' => 'IGDNC', 'data' => ['9', 'subunit', 'FL_SUBUNIT', 'Value', '0', '', '4', '1', '10', '', '10']],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function setInventoryFields($fields)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$colums = ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultValue', 'sequence', 'block', 'displayType', 'params', 'colSpan'];
		foreach ($fields as $field) {
			if (empty($field)) {
				continue;
			}
			$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method add inventory fields to module: ' . print_r($field['moduleName'], true));
			$inventoryField = Vtiger_InventoryField_Model::getInstance($field['moduleName']);
			if ($inventoryField) {
				$table = $inventoryField->getTableName('fields');
				$fieldData = $field['data'];
				$result = $db->pquery('SELECT `id` FROM `' . $table . '` WHERE `columnname` = ?;', [$fieldData[1]]);
				$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method check if columnname exist: ' . print_r($fieldData[1], true));
				if ($result->rowCount() == 0 && $field['type'] == 'add') {
					if (in_array($fieldData[1], ['unit', 'ean', 'subunit', '-'])) {
						$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method add columnname: ' . print_r($fieldData, true));
						$db->insert($table, [
							'columnname' => $fieldData[1],
							'label' => $fieldData[2],
							'invtype' => $fieldData[3],
							'defaultvalue' => $fieldData[5],
							'sequence' => $fieldData[6],
							'block' => $fieldData[7],
							'displaytype' => $fieldData[8],
							'params' => $fieldData[9],
							'colspan' => $fieldData[10],
						]);
					} else {
						$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method add columnname: ' . print_r(array_combine($colums, $fieldData), true));
						$inventoryField->addField($fieldData[3], array_combine($colums, $fieldData));
					}
				} elseif ($result->rowCount() > 0 && $field['type'] == 'remove') {
					$id = $db->getSingleValue($result);
					$params['id'] = $id;
					$params['column'] = $fieldData[1];
					$params['module'] = $field['moduleName'];
					$params['name'] = $fieldData[3];
					$inventoryField->delete($params);
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
				$picklists['SQuoteEnquiries'] = [
					['name' => 'squoteenquiries_status', 'uitype' => '16', 'add_values' => ['PLL_IN_REALIZATION', 'PLL_FOR_VERIFICATION', 'zero' => 'PLL_CANCELLED', 'zero1' => 'PLL_COMPLETED'], 'remove_values' => ['PLL_REQUIRES_TO_BE_COMPLEMENTED', 'PLL_REQUIRES_CONSULTATION', 'PLL_WAITING_FOR_APPROVAL', 'PLL_DISCARDED', 'PLL_ACCEPTED']]
				];
				$picklists['SRequirementsCards'] = [
					['name' => 'srequirementscards_status', 'uitype' => '16', 'add_values' => ['PLL_IN_REALIZATION', 'PLL_FOR_VERIFICATION', 'zero' => 'PLL_CANCELLED', 'zero1' => 'PLL_COMPLETED'], 'remove_values' => ['PLL_REQUIRES_TO_BE_COMPLEMENTED', 'PLL_REQUIRES_CONSULTATION', 'PLL_WAITING_FOR_APPROVAL', 'PLL_DISCARDED', 'PLL_ACCEPTED']]
				];
				$picklists['SCalculations'] = [
					['name' => 'scalculations_status', 'uitype' => '16', 'add_values' => ['PLL_IN_REALIZATION', 'PLL_FOR_VERIFICATION', 'zero' => 'PLL_CANCELLED', 'zero1' => 'PLL_COMPLETED'], 'remove_values' => ['PLL_REQUIRES_TO_BE_COMPLEMENTED', 'PLL_REQUIRES_CONSULTATION', 'PLL_WAITING_FOR_APPROVAL', 'PLL_DISCARDED', 'PLL_ACCEPTED']]
				];
				$picklists['SQuotes'] = [
					['name' => 'squotes_status', 'uitype' => '16', 'add_values' => ['PLL_IN_REALIZATION', 'PLL_FOR_VERIFICATION', 'PLL_AWAITING_DECISION', 'PLL_NEGOTIATIONS', 'zero' => 'PLL_CANCELLED'], 'remove_values' => ['PLL_REQUIRES_TO_BE_COMPLEMENTED', 'PLL_REQUIRES_CONSULTATION', 'PLL_WAITING_FOR_SHIPPING', 'PLL_WAITING_FOR_SIGNATURE', 'PLL_DISCARDED']]
				];
				$picklists['SSingleOrders'] = [
					['name' => 'ssingleorders_status', 'uitype' => '16', 'add_values' => ['PLL_FOR_VERIFICATION', 'PLL_AWAITING_SIGNATURES', 'zero1' => 'PLL_CANCELLED', 'zero' => 'PLL_ACCEPTED'], 'remove_values' => ['PLL_REQUIRES_TO_BE_COMPLEMENTED', 'PLL_REQUIRES_CONSULTATION', 'PLL_WAITING_FOR_SHIPPING', 'PLL_WAITING_FOR_SIGNATURE', 'PLL_WAITING_FOR_REALIZATION', 'PLL_UNREALIZED', 'PLL_REALIZED']]
				];
				$picklists['Accounts'] = [
					['name' => 'campaignrelstatus', 'uitype' => '16', 'add_values' => ['zero1' => 'Message sent'], 'remove_values' => []]
				];
				$picklists['Assets'] = [
					['name' => 'assetstatus', 'uitype' => '15', 'add_values' => ['PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE', 'zero1' => 'PLL_ACCEPTED', 'zero2' => 'PLL_CANCELLED'], 'remove_values' => ['PLL_WARRANTY_SUPPORT', 'PLL_POST_WARRANTY_SUPPORT', 'PLL_NO_SUPPORT']]
				];
				$picklists['OSSSoldServices'] = [
					['name' => 'ssservicesstatus', 'uitype' => '15', 'add_values' => ['PLL_DRAFT', 'PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE', 'zero1' => 'PLL_ACCEPTED', 'zero2' => 'PLL_CANCELLED'], 'remove_values' => ['Individual Agreement', 'In service', 'Finished support']]
				];
				$picklists['Products'] = [
					['name' => 'usageunit', 'uitype' => '15', 'add_values' => ['pcs', 'kg', 'l'], 'remove_values' => ['Box', 'Carton', 'Dozen', 'Each', 'Hours', 'Impressions', 'Lb', 'Pages', 'Pieces', 'Quantity', 'Reams', 'Sheet', 'Spiral Binder', 'Sq Ft']]
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

	function getPicklistId($fieldName, $value)
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

	function getBlocks($index)
	{
		$blocks = [];
		switch ($index) {
			case 1:
				$blocks = [
					['type' => 'add', 'moduleName' => 'SSalesProcesses', 'data' => ['319', '86', 'LBL_ATTENTION', '5', '0', '0', '0', '0', '0', '1', '0']],
					['type' => 'add', 'moduleName' => 'SSalesProcesses', 'data' => ['320', '86', 'LBL_FINANCES', '3', '0', '0', '0', '0', '0', '1', '0']]
				];
				break;
			default:
				break;
		}
		return $blocks;
	}

	function setBlocks($blocks)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if (!empty($blocks)) {
			foreach ($blocks as $block) {
				if ($block['type'] == 'add') {
					$moduleInstance = Vtiger_Module::getInstance($block['moduleName']);
					//'blockid','tabid','blocklabel','sequence','show_title','visible','create_view','edit_view','detail_view','display_status','iscustom'
					$blockData = $block['data'];
					if (!$this->checkBlockExists($block['moduleName'], ['blocklabel' => $blockData[2]])) {
						$blockInstance = new Vtiger_Block();
						$blockInstance->label = $blockData[2];
						$blockInstance->showtitle = $blockData[4];
						$blockInstance->visible = $blockData[5];
						$blockInstance->increateview = $blockData[6];
						$blockInstance->ineditview = $blockData[7];
						$blockInstance->indetailview = $blockData[8];
						$blockInstance->display_status = $blockData[9];
						$blockInstance->iscustom = $blockData[10];
						$blockInstance->__create($moduleInstance);
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
					['86', '2094', 'attention', 'vtiger_crmentity', '1', '300', 'attention', 'Attention', '1', '2', '', '100', '1', '319', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "text", "LBL_ATTENTION", [], [], 'SSalesProcesses'],
					['86', '2095', 'estimated', 'u_yf_ssalesprocesses', '1', '71', 'estimated', 'FL_ESTIMATED', '1', '2', '', '100', '1', '320', '1', 'N~M', '1', '7', 'BAS', '1', '', '0', '', NULL, "decimal(25,8)", "LBL_FINANCES", [], [], 'SSalesProcesses'],
					['86', '2096', 'actual_sale', 'u_yf_ssalesprocesses', '1', '71', 'actual_sale', 'FL_ACTUAL_SALE', '1', '2', '', '100', '2', '320', '10', 'N~O', '2', '9', 'BAS', '1', '', '0', '', '0', "decimal(25,8)", "LBL_FINANCES", [], [], 'SSalesProcesses'],
					['86', '2097', 'estimated_date', 'u_yf_ssalesprocesses', '1', '5', 'estimated_date', 'FL_ESTIMATED_DATE', '1', '2', '', '100', '10', '269', '1', 'D~M', '2', '6', 'BAS', '1', '', '0', '', NULL, "date", "LBL_SSALESPROCESSES_INFORMATION", [], [], 'SSalesProcesses'],
					['86', '2098', 'actual_date', 'u_yf_ssalesprocesses', '1', '5', 'actual_date', 'FL_ACTUAL_DATE', '1', '2', '', '100', '11', '269', '10', 'D~O', '2', '8', 'BAS', '1', '', '0', '', NULL, "date", "LBL_SSALESPROCESSES_INFORMATION", [], [], 'SSalesProcesses'],
					['86', '2099', 'probability', 'u_yf_ssalesprocesses', '1', '9', 'probability', 'FL_PROBABILITY', '1', '2', '', '100', '9', '269', '1', 'N~O~2~2', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(5,2)", "LBL_SSALESPROCESSES_INFORMATION", [], [], 'SSalesProcesses'],
					['86', '2100', 'ssalesprocesses_source', 'u_yf_ssalesprocesses', '1', '16', 'ssalesprocesses_source', 'FL_SOURCE', '1', '2', '', '100', '6', '269', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "varchar(255)", "LBL_SSALESPROCESSES_INFORMATION", ['PLL_WEBSITE', 'PLL_PHONE', 'PLL_MAIL_CAMPAIGN', 'PLL_CONFERENCE', 'PLL_RECOMMENDATION', 'PLL_MEETING', 'PLL_ADVERTISING', 'PLL_COMMUNITY', 'PLL_OTHER'], [], 'SSalesProcesses'],
					['86', '2101', 'ssalesprocesses_type', 'u_yf_ssalesprocesses', '1', '16', 'ssalesprocesses_type', 'FL_TYPE', '1', '2', '', '100', '7', '269', '1', 'V~M', '1', '4', 'BAS', '1', '', '0', '', NULL, "varchar(255)", "LBL_SSALESPROCESSES_INFORMATION", ['PLL_NEW_SALES', 'PLL_RENEWAL', 'PLL_AFTERSALES', 'PLL_TENDER'], [], 'SSalesProcesses'],
					['86', '2102', 'ssalesprocesses_status', 'u_yf_ssalesprocesses', '1', '16', 'ssalesprocesses_status', 'FL_STATUS', '1', '2', '', '100', '8', '269', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "varchar(255)", "LBL_SSALESPROCESSES_INFORMATION", ['PLL_DRAFT', 'PLL_FOR_REALIZATION', 'PLL_QUOTE_ENQUIRY', 'PLL_REQUIREMENTS_CARD', 'PLL_CALCULATION', 'PLL_QUOTE', 'PLL_NEGOTIATIONS', 'PLL_AWAITING_DECISION', 'PLL_AWAITING_SIGNATURES', 'PLL_ORDER', 'PLL_REALIZATION', 'PLL_AWAITING_PAYMENT', 'PLL_INVOICING', 'PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED'], [], 'SSalesProcesses'],
					['88', '2103', 'sum_total', 'u_yf_scalculations', '1', '7', 'sum_total', 'FL_TOTAL_PRICE', '1', '2', '', '100', '3', '278', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SCalculations'],
					['88', '2104', 'sum_marginp', 'u_yf_scalculations', '1', '7', 'sum_marginp', 'FL_MARGINP', '1', '2', '', '100', '4', '278', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(10,2)", "LBL_STATISTICS", [], [], 'SCalculations'],
					['88', '2105', 'sum_margin', 'u_yf_scalculations', '1', '7', 'sum_margin', 'FL_MARGIN', '1', '2', '', '100', '5', '278', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SCalculations'],
					['89', '2106', 'sum_total', 'u_yf_squotes', '1', '7', 'sum_total', 'FL_TOTAL_PRICE', '1', '2', '', '100', '3', '282', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SQuotes'],
					['89', '2107', 'sum_marginp', 'u_yf_squotes', '1', '7', 'sum_marginp', 'FL_MARGINP', '1', '2', '', '100', '4', '282', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(10,2)", "LBL_STATISTICS", [], [], 'SQuotes'],
					['89', '2108', 'sum_margin', 'u_yf_squotes', '1', '7', 'sum_margin', 'FL_MARGIN', '1', '2', '', '100', '5', '282', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SQuotes'],
					['89', '2109', 'sum_gross', 'u_yf_squotes', '1', '7', 'sum_gross', 'FL_SUM_GROSS', '1', '2', '', '100', '6', '282', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SQuotes'],
					['89', '2110', 'sum_discount', 'u_yf_squotes', '1', '7', 'sum_discount', 'FL_SUM_DISCOUNT', '1', '2', '', '100', '7', '282', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SQuotes'],
					['90', '2111', 'sum_total', 'u_yf_ssingleorders', '1', '7', 'sum_total', 'FL_TOTAL_PRICE', '1', '2', '', '100', '3', '286', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SSingleOrders'],
					['90', '2112', 'sum_marginp', 'u_yf_ssingleorders', '1', '7', 'sum_marginp', 'FL_MARGINP', '1', '2', '', '100', '4', '286', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(10,2)", "LBL_STATISTICS", [], [], 'SSingleOrders'],
					['90', '2113', 'sum_margin', 'u_yf_ssingleorders', '1', '7', 'sum_margin', 'FL_MARGIN', '1', '2', '', '100', '5', '286', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SSingleOrders'],
					['90', '2114', 'sum_gross', 'u_yf_ssingleorders', '1', '7', 'sum_gross', 'FL_SUM_GROSS', '1', '2', '', '100', '6', '286', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SSingleOrders'],
					['90', '2115', 'sum_discount', 'u_yf_ssingleorders', '1', '7', 'sum_discount', 'FL_SUM_DISCOUNT', '1', '2', '', '100', '7', '286', '3', 'N~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, "decimal(27,8)", "LBL_STATISTICS", [], [], 'SSingleOrders'],
					['14', '2208', 'ean', 'vtiger_products', '1', '1', 'ean', 'FL_EAN_13', '1', '2', '', '100', '28', '31', '1', 'V~O', '2', '3', 'BAS', '1', '', '1', '9999999999999', NULL, 'varchar(30)', 'LBL_PRODUCT_INFORMATION', [], [], 'Products'],
					['58', '2267', 'osssoldservices_renew', 'vtiger_osssoldservices', '1', '15', 'osssoldservices_renew', 'FL_RENEWAL', '1', '0', '', '100', '11', '141', '10', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'varchar(255)', 'LBL_INFORMATION', ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', 'PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE', 'PLL_RENEWED', 'PLL_NOT_RENEWED', 'PLL_NOT_APPLICABLE'], [], 'OSSSoldServices'],
					['37', '2268', 'assets_renew', 'vtiger_assets', '1', '15', 'assets_renew', 'FL_RENEWAL', '1', '0', '', '100', '19', '95', '10', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'varchar(255)', 'LBL_ASSET_INFORMATION', ['PLL_PLANNED', 'PLL_WAITING_FOR_RENEWAL', 'PLL_WAITING_FOR_VERIFICATION', 'PLL_WAITING_FOR_ACCEPTANCE', 'PLL_RENEWED', 'PLL_NOT_RENEWED', 'PLL_NOT_APPLICABLE'], [], 'Assets'],
					['14', '2269', 'subunit', 'vtiger_products', '2', '16', 'subunit', 'FL_SUBUNIT', '1', '2', '', '100', '7', '33', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'varchar(255)', 'LBL_STOCK_INFORMATION', ['50g', '100g', '300g', '500g', '100ml', '250ml', '330ml', '500ml'], [], 'Products'],
//					['107', '2274', 'fcorectinginvoice_formpayment', 'u_yf_fcorectinginvoice', '1', '15', 'fcorectinginvoice_formpayment', 'FL_FORM_PAYMENT', '1', '2', '', '100', '10', '361', '1', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'varchar(255)', 'LBL_BASIC_DETAILS', ['PLL_TRANSFER', 'PLL_CASH'], [], 'FCorectingInvoice'],
					['13', '2332', 'contract_type', 'vtiger_troubletickets', '1', '16', 'contract_type', 'FL_SERVICE_CONTRACTS_TYPE', '1', '2', '', '100', '22', '25', '9', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'varchar(255)', 'LBL_TICKET_INFORMATION', [], [], 'HelpDesk'],
					['13', '2333', 'contracts_end_date', 'vtiger_troubletickets', '1', '5', 'contracts_end_date', 'FL_SERVICE_CONTRACTS_DATE', '1', '2', '', '100', '23', '25', '9', 'V~O', '1', NULL, 'BAS', '1', '', '0', '', NULL, 'date', 'LBL_TICKET_INFORMATION', [], [], 'HelpDesk'],
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

	function getWidget($index)
	{
		$widgets = [];
		switch ($index) {
			case 1:
				$widgets = [
					['type' => 'add', 'data' => ['103', getTabid('Accounts'), 'RelatedModule', '', '1', '6', NULL, '{"relatedmodule":"' . getTabid('SSalesProcesses') . '","limit":"10","columns":"3","action":"1","switchHeader":"0","filter":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['128', getTabid('FBookkeeping'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['129', getTabid('FBookkeeping'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['130', getTabid('FBookkeeping'), 'Comments', 'ModComments', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['131', getTabid('FBookkeeping'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'add', 'data' => ['124', getTabid('IStorages'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['125', getTabid('IStorages'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['126', getTabid('IStorages'), 'Comments', 'ModComments', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['127', getTabid('IStorages'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'add', 'data' => ['112', getTabid('IGRN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['113', getTabid('IGRN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['114', getTabid('IGRN'), 'Comments', 'ModComments', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['115', getTabid('IGRN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'add', 'data' => ['104', getTabid('IGDN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['105', getTabid('IGDN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['106', getTabid('IGDN'), 'Comments', 'ModComments', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['107', getTabid('IGDN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'add', 'data' => ['108', getTabid('IIDN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['109', getTabid('IIDN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['110', getTabid('IIDN'), 'Comments', 'ModComments', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['111', getTabid('IIDN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'add', 'data' => ['116', getTabid('IGIN'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['117', getTabid('IGIN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['118', getTabid('IGIN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['119', getTabid('IGIN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['120', getTabid('IPreOrder'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['121', getTabid('IPreOrder'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['122', getTabid('IPreOrder'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['123', getTabid('IPreOrder'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"limit":"","relatedmodule":"' . getTabid('Documents') . '","columns":"3","action":"1","filter":"-","checkbox_selected":"","checkbox":"-"}']],
					['type' => 'remove', 'data' => ['33', getTabid('Project'), 'RelatedModule', 'HelpDesk', '2', '8', NULL, '{"limit":"5","relatedmodule":"' . getTabid('HelpDesk') . '","columns":"3","action":"1","filter":"-"}']],
					['type' => 'add', 'data' => ['148', getTabid('SSalesProcesses'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SCalculations'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SQuoteEnquiries'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SQuotes'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SRecurringOrders'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SRequirementsCards'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['148', getTabid('SSingleOrders'), 'Activities', 'Calendar', '1', '6', NULL, '{"limit":"5"}']],
					['type' => 'add', 'data' => ['155', getTabid('SRecurringOrders'), 'Summary', NULL, '1', '0', NULL, '[]', '156', '91', 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['155', getTabid('SRecurringOrders'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"relatedmodule":"' . getTabid('Documents') . '","limit":"","columns":"3","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['155', getTabid('SRecurringOrders'), 'EmailList', 'Emails', '2', '4', NULL, '{"relatedmodule":"Emails","limit":"5"}']],
					['type' => 'add', 'data' => ['155', getTabid('SRecurringOrders'), 'Comments', 'ModComments', '2', '5', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['105', getTabid('SRecurringOrders'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['132', getTabid('ISTDN'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['133', getTabid('ISTDN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['134', getTabid('ISTDN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"relatedmodule":"' . getTabid('Documents') . '","limit":"","columns":"3","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['135', getTabid('ISTDN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['136', getTabid('ISTN'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['137', getTabid('ISTN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['138', getTabid('ISTN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['139', getTabid('ISTN'), 'RelatedModule', '', '2', '3', NULL, '{"relatedmodule":"' . getTabid('Documents') . '","limit":"","columns":"1","switchHeader":"-","filter":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['140', getTabid('ISTRN'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['141', getTabid('ISTRN'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['142', getTabid('ISTRN'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['144', getTabid('KnowledgeBase'), 'Comments', '', '2', '2', NULL, '{"relatedmodule":"ModComments","limit":"5"}']],
					['type' => 'add', 'data' => ['145', getTabid('KnowledgeBase'), 'Updates', 'LBL_UPDATES', '1', '1', NULL, '[]']],
					['type' => 'add', 'data' => ['146', getTabid('KnowledgeBase'), 'RelatedModule', '', '2', '3', NULL, '{"relatedmodule":"' . getTabid('Documents') . '","limit":"5","columns":"1","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}']],
					['type' => 'add', 'data' => ['147', getTabid('KnowledgeBase'), 'Summary', NULL, '1', '0', NULL, '[]']],
					['type' => 'add', 'data' => ['143', getTabid('ISTRN'), 'RelatedModule', 'Documents', '2', '3', NULL, '{"relatedmodule":"' . getTabid('Documents') . '","limit":"","columns":"3","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}']],
				];
				break;
			default:
				break;
		}
		return $widgets;
	}

	public function setWidget($widgets)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($widgets) {
			foreach ($widgets as $widget) {
				if (empty($widget)) {
					continue;
				}
				list($id, $tabid, $type, $label, $wcol, $sequence, $nomargin, $data) = $widget['data'];
				$sqlLabel = '';
				if ($type != 'Summary') {
					$sqlLabel = " AND label = '$label'";
				}
				$result = $db->pquery("SELECT 1 FROM `vtiger_widgets` WHERE tabid=? AND type = ? $sqlLabel;", [$tabid, $type]);
				if ($result->rowCount() == 0 && $widget['type'] == 'add') {
					$db->insert('vtiger_widgets', [
						'tabid' => $tabid,
						'type' => $type,
						'label' => $label,
						'wcol' => $wcol,
						'sequence' => $sequence,
						'nomargin' => $nomargin,
						'data' => $data
					]);
				} elseif ($result->rowCount() > 0 && $widget['type'] == 'remove') {
					$db->delete('vtiger_widgets', '`tabid` = ? AND `type` = ? AND `label` = ?;', [$tabid, $type, $label]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getRelations($index)
	{
		$ralations = [];
		switch ($index) {
			case 1:
				$ralations = [
					['type' => 'add', 'data' => ['462', 'IStorages', 'Products', 'get_many_to_many', '1', 'Products', '0', '', '0', '0', '0']],
					['type' => 'add', 'data' => ['463', 'Products', 'IStorages', 'get_many_to_many', '17', 'IStorages', '0', '', '0', '0', '0']],
					['type' => 'add', 'data' => [452, 'Accounts', 'SSalesProcesses', 'get_dependents_list', 26, 'SSalesProcesses', 0, 'ADD', 0, 0, 0]],
					['type' => 'remove', 'data' => [176, 'Project', 'HelpDesk', 'get_dependents_list', 5, 'HelpDesk', 0, 'ADD', 0, 0, 0]],
					['type' => 'add', 'data' => ['84', 'Campaigns', 'Leads', 'get_campaigns_records', '3', 'Leads', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['88', 'Campaigns', 'Accounts', 'get_campaigns_records', '4', 'Accounts', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['442', 'Campaigns', 'Vendors', 'get_campaigns_records', '6', 'Vendors', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['444', 'Campaigns', 'Contacts', 'get_campaigns_records', '5', 'Contacts', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['446', 'Campaigns', 'Partners', 'get_campaigns_records', '7', 'Partners', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['448', 'Campaigns', 'Competition', 'get_campaigns_records', '8', 'Competition', '0', 'ADD,SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['17', 'Leads', 'Campaigns', 'get_campaigns', '7', 'Campaigns', '0', 'select', '0', '0', '0']],
					['type' => 'add', 'data' => ['87', 'Accounts', 'Campaigns', 'get_campaigns', '6', 'Campaigns', '0', 'select', '0', '0', '0']],
					['type' => 'add', 'data' => ['443', 'Vendors', 'Campaigns', 'get_campaigns', '16', 'Campaigns', '0', 'SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['445', 'Contacts', 'Campaigns', 'get_campaigns', '28', 'Campaigns', '0', 'SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['447', 'Partners', 'Campaigns', 'get_campaigns', '6', 'Campaigns', '0', 'SELECT', '0', '0', '0']],
					['type' => 'add', 'data' => ['449', 'Competition', 'Campaigns', 'get_campaigns', '6', 'Campaigns', '0', 'SELECT', '0', '0', '0']],
					['type' => 'remove', 'data' => ['88', 'Campaigns', 'Accounts', 'get_accounts', '3', 'Accounts', '0', 'add,select', '0', '0', '0']],
					['type' => 'remove', 'data' => ['84', 'Campaigns', 'Leads', 'get_leads', '2', 'Leads', '0', 'add,select', '0', '0', '0']],
					['type' => 'add', 'data' => ['471', 'ISTN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['472', 'ISTDN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['473', 'IGRN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['474', 'IGDN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['475', 'IIDN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['476', 'IGIN', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
					['type' => 'add', 'data' => ['477', 'IPreOrder', 'Calendar', 'get_related_list', '2', 'Activities', '0', 'ADD', '0', '0', '0']],
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
				list($id, $moduleName, $relMmoduleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment) = $relation['data'];
				$tabid = getTabid($moduleName);
				$relTabid = getTabid($relMmoduleName);
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
//					if ($name == 'get_many_to_many') {
//						$refTableName = Vtiger_Relation_Model::getReferenceTableInfo($moduleName, $relMmoduleName);
//						if (!Vtiger_Utils::CheckTable($refTableName['table'])) {
//							Vtiger_Utils::CreateTable(
//								$refTableName['table'], '(crmid INT(19) ,relcrmid INT(19),KEY crmid (crmid),KEY relcrmid (relcrmid),'
//								. ' CONSTRAINT `' . $refTableName['table'] . '_ibfk_1` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,'
//								. ' CONSTRAINT `' . $refTableName['table'] . '_ibfk_2` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE)', true);
//						}
//					}
				} elseif ($result->rowCount() > 0 && $relation['type'] == 'remove') {
					$db->delete('vtiger_relatedlists', '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
				}
			}
		}
	}

	public function getLink($index)
	{
		$links = [];
		switch ($index) {
			case 1:
				$links = [
					['type' => 'add', 'data' => ['1', getTabid('Home'), 'DASHBOARDWIDGET', 'DW_SUMMATION_BY_MONTHS', 'index.php?module=FInvoice&view=ShowWidget&name=SummationByMonths', NULL, '0', NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['2', getTabid('Home'), 'DASHBOARDWIDGET', 'DW_SUMMATION_BY_USER', 'index.php?module=FInvoice&view=ShowWidget&name=SummationByUser', NULL, NULL, NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['267', getTabid('Home'), 'DASHBOARDWIDGET', 'LBL_PRODUCTS_SOLD_TO_RENEW', 'index.php?module=Home&view=ShowWidget&name=ProductsSoldToRenew', '', '0', NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['268', getTabid('Home'), 'DASHBOARDWIDGET', 'LBL_SOLD_SERVICES_TO_RENEW', 'index.php?module=Home&view=ShowWidget&name=ServicesSoldToRenew', '', '0', NULL, NULL, NULL]],
					['type' => 'add', 'data' => ['3', getTabid('Home'), 'DASHBOARDWIDGET', 'Notifications', 'index.php?module=Home&view=ShowWidget&name=Notifications', NULL, '3', NULL, NULL, NULL]],
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
				list($id, $tabid, $type, $label, $url, $iconpath, $sequence, $path, $class, $method) = $link['data'];
				$handlerInfo = ['path' => $path, 'class' => $class, 'method' => $method];
				if ($link['type'] == 'add') {
					$result = $db->pquery('SELECT 1 FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=?;', [$tabid, $type, $label, $url]);
					if (!$db->getRowCount($result))
						Vtiger_Link::addLink($tabid, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
				} elseif ($link['type'] == 'remove') {
					Vtiger_Link::deleteLink($tabid, $type, $label, $url);
				}
			}
		}
	}

	public function addModules()
	{
		global $log;
		$log->debug("Entering YetiForceUpdate::addModules() method ...");
		$db = PearDatabase::getInstance();
		$rootDir = vglobal('root_directory');
		$dirName = 'cache/updates/files/';
		$modules = ['FBookkeeping', 'FInvoice', 'KnowledgeBase', 'IStorages', 'IGRN', 'FInvoiceProforma', 'IGDN', 'IIDN', 'IGIN', 'IPreOrder', 'ISTDN', 'ISTN', 'ISTRN', 'FCorectingInvoice', 'IGRNC', 'IGDNC'];
		$i = 0;
		foreach ($modules as $module) {
			if (file_exists('cache/updates/' . $module . '.xml') && !Vtiger_Module::getInstance($module)) {
				$locations = ['modules/' . $module];
				foreach ($locations as $loc) {
					if (is_dir($dirName . $loc) && !file_exists($rootDir . $loc)) {
						mkdir($rootDir . $loc);
					}
					Vtiger_Functions::recurseCopy($dirName . $loc, $loc, true);
					Vtiger_Functions::recurseDelete($dirName . $loc);
				}

				$importInstance = new Vtiger_PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/' . $module . '.xml');
				$importInstance->import_Module();
				unlink('cache/updates/' . $module . '.xml');
				$this->postInstalModule($module);
				if (!in_array($module, ['FBookkeeping', 'KnowledgeBase', 'IStorages', 'ISTN'])) {
					$db->update('vtiger_tab', ['type' => 1], '`name` = ?', [$module]);
					$locations = ['modules/Vtiger/inventoryfields' => '', 'modules/Vtiger/models/' => 'InventoryField.php', 'modules/Vtiger/models' => '/Inventory.php'];
					if ($i == 0) {
						foreach ($locations as $loc => $key) {
							if (is_dir($dirName . $loc) && !file_exists($rootDir . $loc)) {
								mkdir($rootDir . $loc);
							}
							if (empty($key)) {
								$log->debug("Entering YetiForceUpdate::addModules() method copy file...recurseCopy " . print_r($loc . $key, true));
								Vtiger_Functions::recurseCopy($dirName . $loc, $loc, true);
							} else {
								$log->debug("Entering YetiForceUpdate::addModules() method copy file... check if exist " . print_r($loc . $key, true));
								if (file_exists($dirName . $loc . $key)) {
									$log->debug("Entering YetiForceUpdate::addModules() method copy file... " . print_r($loc . $key, true));
									copy($dirName . $loc . $key, $rootDir . $loc . $key);
								}
							}
							Vtiger_Functions::recurseDelete($dirName . $loc . $key);
						}
						++$i;
					}
					$this->setInventoryFields($this->getInventoryFields($module));
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::addModules() method ...");
	}

	public function postInstalModule($moduleName)
	{
		$db = PearDatabase::getInstance();
		$moduleInstance = CRMEntity::getInstance($moduleName);
		$moduleInstance->setModuleSeqNumber("configure", $moduleName, $this->getPrefix($moduleName), '1');
		$db->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$moduleName]);
		$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
		if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
			include_once 'modules/ModComments/ModComments.php';
			if (class_exists('ModComments'))
				ModComments::addWidgetTo([$moduleName]);
		}
		ModTracker::enableTrackingForModule(Vtiger_Functions::getModuleId($moduleName));

		// to remove
		switch ($moduleName) {
			case 'IGIN':
				$db->query("insert  into `u_yf_igin_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_igin_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_igin_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'IGRN':
				$db->query("insert  into `u_yf_igrn_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_igrn_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_igrn_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'IIDN':
				$db->query("insert  into `u_yf_iidn_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_iidn_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_iidn_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'IGDN':
				$db->query("insert  into `u_yf_igdn_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_igdn_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_igdn_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'IPreOrder':
				$db->query("insert  into `u_yf_ipreorder_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_ipreorder_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_ipreorder_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'ISTDN':
				$db->query("insert  into `u_yf_istdn_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_istdn_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_istdn_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'ISTRN':
				$db->query("insert  into `u_yf_istrn_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_istrn_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_istrn_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'FCorectingInvoice':
				break;
			case 'IGRNC':
				$db->query("insert  into `u_yf_igrnc_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_igrnc_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_igrnc_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			case 'IGDNC':
				$db->query("insert  into `u_yf_igdnc_invmap`(`module`,`field`,`tofield`) values ('Products','ean','ean');");
				$db->query("insert  into `u_yf_igdnc_invmap`(`module`,`field`,`tofield`) values ('Products','subunit','subunit');");
				$db->query("insert  into `u_yf_igdnc_invmap`(`module`,`field`,`tofield`) values ('Products','usageunit','unit');");
				break;
			default:
				break;
		}
	}

	public function getPrefix($moduleName)
	{
		$prefixes = [
			'FBookkeeping' => 'F-B',
			'FInvoice' => 'F-I',
			'KnowledgeBase' => 'KB',
			'IStorages' => 'I-S',
			'IGRN' => 'I-GRN',
			'FInvoiceProforma' => 'F-IP',
			'IGDN' => 'I-GDN',
			'IIDN' => 'I-IDN',
			'IGIN' => 'I-GIN',
			'ISTDN' => 'I-TD',
			'ISTRN' => 'I-TR',
			'ISTN' => 'I-SN',
			'IGRNC' => 'I-IGRNC',
			'IGDNC' => 'I-IGDNC',
			'FCorectingInvoice' => 'F-CI',
			'IPreOrder' => 'I-PO'
		];
		return $prefixes[$moduleName];
	}

	function postupdate()
	{
		return true;
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

	public function removeHandler($class = [])
	{
		$log = vglobal('log');
		$db = PearDatabase::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ((bool) $class) {
			$em = new VTEventsManager($db);
			foreach ($class as $handler) {
				$em->unregisterHandler($handler);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getMax($table, $field, $filter = '')
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $id;
	}

	function checkBlockExists($moduleName, $block)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery("SELECT * FROM vtiger_settings_blocks WHERE label = ? ;", [$block]);
		else
			$result = $db->pquery("SELECT * FROM vtiger_blocks WHERE tabid = ? AND blocklabel = ? ;", [getTabid($moduleName), $block['blocklabel']]);

		if (!$db->num_rows($result)) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return false;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return true;
	}

	public function checkFieldExists($moduleName, $column, $table)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", [$field[1], $field[4]]);
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
