<?php
/**
 * LocationRegister crmentity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class LocationRegister extends Vtiger_CRMEntity
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name = 'u_yf_locationregister';
	/**
	 * Table index.
	 *
	 * @var string
	 */
	public $table_index = 'locationregisterid';

	/**
	 * Mandatory table for supporting custom fields.
	 *
	 * @var array
	 */
	public $customFieldTable = ['u_yf_locationregistercf', 'locationregisterid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 *
	 * @var array
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_locationregister', 'u_yf_locationregistercf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 *
	 * @var array
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_locationregister' => 'locationregisterid',
		'u_yf_locationregistercf' => 'locationregisterid',
	];

	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_NAME' => 'name',
		'Assigned To' => 'assigned_user_id',
		'FL_STATUS' => 'locationregister_status',
		'FL_CITY' => 'city'
	];

	/**
	 * For Popup listview and UI type support.
	 *
	 * @var array
	 */
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'FL_NAME' => ['locationregister', 'name'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
		'FL_STATUS' => ['locationregister', 'locationregister_status'],
		'FL_CITY' => ['locationregister', 'city'],
	];
	/**
	 * Search fields name.
	 *
	 * @var array
	 */
	public $search_fields_name = [];
	/**
	 * For Popup window record selection.
	 *
	 * @var array
	 */
	public $popup_fields = ['name', 'locationregister_status', 'city'];
	/**
	 * For Alphabetical search.
	 *
	 * @var string
	 */
	public $def_basicsearch_col = 'name';
	/**
	 * Column value to use on detail view record text display.
	 *
	 * @var string
	 */
	public $def_detailview_recname = 'name';
	/**
	 * Used when enabling/disabling the mandatory fields for the module. Refers to vtiger_field.fieldname values.
	 *
	 * @var array
	 */
	public $mandatory_fields = ['name', 'assigned_user_id'];
	/**
	 * Default order by.
	 *
	 * @var string
	 */
	public $default_order_by = '';
	/**
	 * Default sort order.
	 *
	 * @var string
	 */
	public $default_sort_order = 'ASC';
}
