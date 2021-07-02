<?php
/**
 * IGDN CRMEntity Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class IGDN extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_igdn';
	public $table_index = 'igdnid';
	protected $lockFields = ['igdn_status' => ['PLL_ACCEPTED']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_igdncf', 'igdnid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_igdn', 'u_yf_igdncf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_igdn' => 'igdnid',
		'u_yf_igdncf' => 'igdnid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'subject' => 'subject',
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
		'subject' => ['igdn', 'subject'],
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
}
