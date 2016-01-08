<?php
/**
 * RemoveModule Class
 * @package YetiForce.UpdatePackages
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');

class RemoveModule
{

	var $name;
	var $tabid;
	var $tableList = [];
	var $isentitytype;
	var $basicTableName;
	var $removeFields = [];
	var $links = [];
	var $workflows = [];
	var $workflowsMethod = [];
	var $handlers = [];
	var $widgets = [];

	function __construct($moduleName)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' (' . $moduleName . ')| Start');
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleInstance) {
			return;
		}
		$this->name = $moduleInstance->name;
		$this->tabid = $moduleInstance->id;
		$this->basicTableName = $moduleInstance->basetable;
		$this->isentitytype = $moduleInstance->isentitytype;
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Initialize this instance
	 */
	function init($valueMap)
	{
		foreach ($valueMap AS $key => $value) {
			$this->$key = $value;
		}
	}

	function delete()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();

		if (empty($this->name) || empty($this->tabid)) {
			$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
			return;
		}

		if ($this->isentitytype) {
			$this->deleteFromCRMEntity();
			$this->deleteTools();
			$this->deleteCVForModule();
			$this->deleteBlocksForModule();
			$this->deleteHandlers();
			$db->pquery('DELETE FROM vtiger_ws_entity WHERE `name` = ?;', [$this->name]);
			$db->pquery('DELETE FROM vtiger_ws_entity_referencetype WHERE `type` = ?;', [$this->name]);
			$db->pquery('DELETE FROM vtiger_ws_referencetype WHERE `type` = ?;', [$this->name]);
		}

		$this->deleteIcons();
		$this->unsetAllRelatedList();
		$this->deleteCommentsForModule();
		$this->deleteLanguagesForModule();
		$this->deleteSharing();
		$this->deleteFromModentityNum();
		$this->deleteCronsForModule();
		$this->deleteProfilesForModule();
		$this->deleteWorkflowsForModule();
		$this->deleteMenuForModule();
		$this->deleteGroup2Modules();
		$this->deleteModuleTables();
		$this->deleteCRMEntityRel();
		$this->deleteLinksForModule();
		$this->deleteOtherFields();
		$this->homeDefault();
		$this->deleteWorkflowsMethod();
		$this->deleteCalendarDefault();
		$this->dataAccess();
		$this->deleteDir();
		$this->__delete();
		$this->syncfile();
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function deleteOtherFields()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		foreach ($this->removeFields as $tableName => $columnsName) {
			if (!Vtiger_Utils::CheckTable($tableName)) {
				continue;
			}
			foreach ($columnsName as $columnName) {
				$result = $db->pquery("SELECT fieldid FROM vtiger_field WHERE columnname = ? AND tablename = ?;", [$columnName, $tableName]);
				if ($id = $db->getSingleValue($result)) {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					try {
						$fieldInstance->delete();
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

	public function homeDefault()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT stuffid FROM vtiger_homedefault WHERE setype = ?', [$this->name]);
		while ($id = $db->getSingleValue($result)) {
			$db->delete('vtiger_homedefault', 'stuffid = ?', [$id]);
			$db->delete('vtiger_homestuff', 'stuffid = ?', [$id]);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function dataAccess()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT dataaccessid FROM vtiger_dataaccess WHERE module_name = ?;", [$this->name]);
		while ($id = $db->getSingleValue($result)) {
			$db->delete('vtiger_dataaccess_cnd', 'dataaccessid = ?', [$id]);
			$db->delete('vtiger_dataaccess', 'dataaccessid = ?', [$id]);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	function deleteHandlers()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT handler_class FROM vtiger_eventhandler_module WHERE module_name = ?', [$this->name]);
		while ($handlerClass = $db->getSingleValue($result)) {
			$db->delete('vtiger_eventhandlers', 'handler_class = ?', [$handlerClass]);
			$db->delete('vtiger_eventhandler_module', 'module_name = ?', [$this->name]);
		}
		foreach ($this->handlers as $handlerClass) {
			$db->delete('vtiger_eventhandlers', 'handler_class = ?', [$handlerClass]);
			$db->delete('vtiger_eventhandler_module', 'module_name = ?', [$handlerClass]);
		}
	}

	function deleteWorkflowsMethod()
	{
		$db = PearDatabase::getInstance();
		foreach ($this->workflowsMethod as $method) {
			$db->delete('com_vtiger_workflowtasks_entitymethod', 'method_name = ?', [$method]);
		}
	}

	function deleteCalendarDefault()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT id FROM vtiger_calendar_default_activitytypes where module = ?', [$this->name]);
		while ($id = $db->getSingleValue($result)) {
			$db->delete('vtiger_calendar_default_activitytypes', 'id = ?', [$id]);
			$db->delete('vtiger_calendar_user_activitytypes', 'defaultid = ?', [$id]);
		}
	}

	/**
	 * Function to remove rows in vtiger_crmentity, vtiger_crmentityrel
	 */
	public function deleteFromCRMEntity()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT crmid FROM vtiger_crmentity where setype = ?', [$this->name]);
		while ($id = $db->getSingleValue($result)) {
			$this->deleteRecord($id);
		}
		$db->delete('vtiger_crmentity', 'setype = ?', [$this->name]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param <int> $recordId
	 */
	public function deleteRecord($recordId)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' (' . $recordId . ')| Start');
		vimport('~~modules/com_vtiger_workflow/include.inc');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
		$wfs = new VTWorkflowManager(PearDatabase::getInstance());
		$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$ON_DELETE);
		if (count($workflows)) {
			$wsId = vtws_getWebserviceEntityId($this->name, $recordId);
			$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
			$entityData = $entityCache->forId($wsId);
			foreach ($workflows as $id => $workflow) {
				if ($workflow->evaluate($entityCache, $entityData->getId())) {
					$workflow->performTasks($entityData);
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Delete tool (actions) of the module
	 */
	function deleteTools()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->pquery("DELETE FROM vtiger_profile2utility WHERE tabid=?", [$this->tabid]);
		$log->debug("Deleting tools ... DONE");
	}

	/**
	 * Delete filter associated for module
	 */
	function deleteCVForModule()
	{
		$db = PearDatabase::getInstance();

		$cvidres = $db->pquery('SELECT cvid FROM vtiger_customview WHERE entitytype=?', [$this->name]);
		$cvids = [];
		while (($cvid = $db->getSingleValue($cvidres)) !== false) {
			$cvids[] = $cvid;
		}
		if (!empty($cvids)) {
			$db->delete('vtiger_cvadvfilter', 'cvid IN (' . implode(',', $cvids) . ')');
			$db->delete('vtiger_cvcolumnlist', 'cvid IN (' . implode(',', $cvids) . ')');
			$db->delete('vtiger_customview', 'cvid IN (' . implode(',', $cvids) . ')');
		}
	}

	/**
	 * Delete all blocks associated with module
	 * @param Boolean true to delete associated fields, false otherwise
	 */
	function deleteBlocksForModule($recursive = true)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		if ($recursive)
			$this->deleteFieldsForModule();
		$db->delete('vtiger_module_dashboard_blocks', 'tabid = ?', [$this->tabid]);
		$db->pquery('DELETE vtiger_blocks, vtiger_blocks_hide'
			. ' FROM vtiger_blocks'
			. ' INNER JOIN `vtiger_blocks_hide`'
			. ' ON vtiger_blocks.`blockid` = vtiger_blocks_hide.`blockid`'
			. '  WHERE vtiger_blocks.`tabid` =?', [$this->tabid]);
		$db->delete('vtiger_blocks', 'tabid=?', [$this->tabid]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Delete fields associated with the module
	 */
	function deleteFieldsForModule()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$this->deletePickLists();
		$this->deleteUiType10Fields();
		$db->delete('vtiger_field', 'tabid=?', [$this->tabid]);
		$db->delete('vtiger_fieldmodulerel', 'module = ? OR relmodule = ?', [$this->name, $this->name]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove picklist-type or multiple choice picklist-type table
	 */
	function deletePickLists()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$query = "SELECT `fieldname` FROM `vtiger_field` WHERE `tabid` = '" . $this->tabid . "' AND  uitype IN (15, 16, 33)";
		$result = $db->query($query);
		while ($fieldName = $db->getSingleValue($result)) {
			$query = "SELECT COUNT(1) FROM `vtiger_field` WHERE `fieldname` = ? AND `tabid` <> ? AND uitype IN (?, ?, ?)";
			$result2 = $db->pquery($query, [$fieldName, $this->tabid, 15, 16, 33]);
			if ($db->getSingleValue($result2) == 0) {
				$db->query('DROP TABLE IF EXISTS vtiger_' . $fieldName . '');
				$db->query('DROP TABLE IF EXISTS vtiger_' . $fieldName . '_seq');
				$query = $db->query("SELECT picklistid from vtiger_picklist WHERE name = '$fieldName'");
				$picklistId = $db->getSingleValue($query);
				if ($picklistId) {
					$db->query("DELETE FROM vtiger_role2picklist WHERE picklistid = '$picklistId'");
					$db->query("DELETE FROM vtiger_picklist WHERE name = '$fieldName'");
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove uitype10 fields
	 */
	function deleteUiType10Fields()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$query = 'SELECT fieldid FROM `vtiger_fieldmodulerel` WHERE relmodule = ?';
		$result = $db->pquery($query, [$this->name]);
		while ($fieldId = $db->getSingleValue($result)) {
			$query = 'SELECT COUNT(1) FROM `vtiger_fieldmodulerel` WHERE fieldid = ?';
			$resultQuery = $db->pquery($query, [$fieldId]);
			if ($db->getSingleValue($resultQuery) == 1) {
				$field = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
				try {
					$field->delete();
				} catch (Exception $e) {
					$log->debug("ERROR " . __CLASS__ . "::" . __METHOD__ . ": code " . $e->getCode() . " message " . $e->getMessage());
				}
				$result2 = $db->query("SHOW COLUMNS FROM `" . $field->table . "` LIKE '" . $field->column . "';");
				if ($result2->rowCount() == 1) {
					$db->query("ALTER TABLE `" . $field->table . "` DROP COLUMN `" . $field->column . "` ;");
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove icons related to a module
	 */
	public function deleteIcons()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$iconSize = ['', 48, 64, 128];
		foreach ($iconSize as $value) {
			foreach (self::getAllLayouts() as $name => $label) {
				$fileName = "layouts/$name/skins/images/" . $this->name . $value . ".png";
				if (file_exists($fileName)) {
					@unlink($fileName);
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public static function getAllLayouts()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT name,label FROM vtiger_layout');
		$folders = [
			'basic' => vtranslate('LBL_DEFAULT')
		];
		while ($row = $db->fetch_array($result)) {
			$folders[$row['name']] = vtranslate($row['label']);
		}
		return $folders;
	}

	/**
	 * Unset related list information that exists with other module
	 */
	public function unsetAllRelatedList()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_relatedlists', 'tabid=? OR related_tabid=?', [$this->tabid, $this->tabid]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Delete coments associated with module
	 * @param Vtiger_Module Instnace of module to use
	 */
	function deleteCommentsForModule()
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_modcomments', 'related_to IN(SELECT crmid FROM vtiger_crmentity WHERE setype=?)', [$this->name]);
	}

	/**
	 * Function to remove language files related to a module
	 */
	function deleteLanguagesForModule()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT prefix FROM vtiger_language');
		while ($lang = $db->getSingleValue($result)) {
			$langFilePath = "languages/$lang/" . $this->name . ".php";
			if (file_exists($langFilePath))
				@unlink($langFilePath);
		}
	}

	/**
	 * Delete sharing access setup for module
	 */
	function deleteSharing()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->pquery("DELETE FROM vtiger_org_share_action2tab WHERE tabid=?", [$this->id]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove row in vtiger_modentity_num table
	 */
	public function deleteFromModentityNum()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_modentity_num', 'semodule = ?', [$this->name]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Delete all cron tasks associated with module
	 */
	function deleteCronsForModule()
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_cron_task', 'module = ?', [$this->name]);
	}

	/**
	 * Delete profile setup of the module
	 */
	function deleteProfilesForModule()
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_def_org_field', 'tabid = ?', [$this->tabid]);
		$db->delete('vtiger_def_org_share', 'tabid = ?', [$this->tabid]);
		$db->delete('vtiger_profile2field', 'tabid = ?', [$this->tabid]);
		$db->delete('vtiger_profile2standardpermissions', 'tabid = ?', [$this->tabid]);
		$db->delete('vtiger_profile2tab', 'tabid = ?', [$this->tabid]);
	}

	/**
	 * Delete all worklflows associated with module
	 */
	function deleteWorkflowsForModule()
	{
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE com_vtiger_workflows,com_vtiger_workflowtasks FROM `com_vtiger_workflows` 
			LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id
			WHERE `module_name` =?', [$this->name]);
		$db->delete('com_vtiger_workflowtasks_entitymethod', 'module_name = ?', [$this->name]);
		foreach ($this->workflows as $module => $name) {
			$result = $db->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE module_name = ? AND `summary` = ?;', [$module, $name]);
			while ($id = $db->getSingleValue($result)) {
				$db->pquery("DELETE FROM com_vtiger_workflowtasks WHERE workflow_id IN	(SELECT workflow_id FROM com_vtiger_workflows WHERE workflow_id=?)", [$id]);
				$db->pquery("DELETE FROM com_vtiger_workflows WHERE workflow_id=?;", [$id]);
			}
		}
	}

	/**
	 * Delete all menus associated with module
	 */
	function deleteMenuForModule()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT id FROM  yetiforce_menu WHERE module=?', [$this->tabid]);
		$db->delete('yetiforce_menu', 'module = ?', [$this->tabid]);
		$numRows = $db->getRowCount($result);
		if ($numRows) {
			$menuRecordModel = new Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}

	/**
	 * Function to remove rows in vtiger_group2modules table
	 */
	public function deleteGroup2Modules()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_group2modules', 'tabid = ?', [$this->tabid]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove tables created by a module
	 */
	public function deleteModuleTables()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->query('SET foreign_key_checks = 0');
		$moduleInstance = Vtiger_Module_Model::getInstance($this->name);
		if ($moduleInstance && $moduleInstance->isInventory()) {
			$db->query('DROP TABLE IF EXISTS ' . $this->basicTableName . '_inventory');
			$db->query('DROP TABLE IF EXISTS ' . $this->basicTableName . '_invfield');
			$db->query('DROP TABLE IF EXISTS ' . $this->basicTableName . '_invmap');
		}
		$db->query('DROP TABLE IF EXISTS ' . $this->basicTableName . 'cf');
		$db->query('DROP TABLE IF EXISTS ' . $this->basicTableName);
		foreach ($this->tableList as $tableName) {
			$db->query('DROP TABLE IF EXISTS ' . $tableName);
		}
		$db->query('SET foreign_key_checks = 1');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove rows in vtiger_crmentityrel
	 */
	public function deleteCRMEntityRel()
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_crmentityrel', '`module` = ? OR `relmodule` = ?', [$this->name, $this->name]);
	}

	/**
	 * Delete all links related to module
	 */
	function deleteLinksForModule()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_links', 'tabid=?', [$this->tabid]);
		$db->query("DELETE FROM vtiger_widgets WHERE type = 'RelatedModule' AND (`data` like '%relatedmodule\":\"" . $this->tabid . "%' OR `data` like '%relatedmodule\":\"" . $this->name . "%')");
		foreach ($this->links as $key => $link) {
			if (is_numeric($key)) {
				$db->delete('vtiger_links', 'linkurl = ?', [$link]);
			} else {
				$db->query("DELETE FROM vtiger_links WHERE linkurl LIKE '" . $link . "%'");
			}
		}
		foreach ($this->widgets as $type) {
			$db->delete('vtiger_widgets', 'type = ?', [$type]);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Function to remove files related to a module
	 */
	public function deleteDir()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$modulePath = 'modules/' . $this->name;
		Vtiger_Functions::recurseDelete($modulePath);
		foreach (self::getAllLayouts() as $name => $label) {
			$layoutPath = 'layouts/' . $name . '/modules/' . $this->name;
			Vtiger_Functions::recurseDelete($layoutPath);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	function __delete()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		if ($this->isentitytype) {
			$this->unsetEntityIdentifier();
		}
		$db->pquery("DELETE FROM vtiger_tab WHERE tabid=?", [$this->tabid]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Unset entity identifier information
	 */
	function unsetEntityIdentifier()
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_entityname WHERE tabid=?', [$this->tabid]);
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	/**
	 * Synchronize the menu information to flat file
	 */
	function syncfile()
	{
		$log = vglobal('log');
		$log->debug("Updating tabdata file ... ");
		create_tab_data_file();
		$log->debug("DONE");
	}
}
