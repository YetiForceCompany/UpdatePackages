<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SRequirementsCards extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_srequirementscards';
	public $table_index = 'srequirementscardsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_srequirementscardscf', 'srequirementscardsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_srequirementscards', 'u_yf_srequirementscardscf', 'vtiger_entity_stats'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_srequirementscards' => 'srequirementscardsid',
		'u_yf_srequirementscardscf' => 'srequirementscardsid',
		'vtiger_entity_stats' => 'crmid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['srequirementscards', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type
	 * @param mixed $moduleName
	 * @param mixed $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Fields\RecordNumber::getInstance($moduleName)->set('prefix', 'S-RC')->set('cur_id', 1)->save();
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['SRequirementsCards']);
				}
			}
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
		}
	}
}
