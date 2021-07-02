<?php

/**
 * Settings QuickCreateEditor SaveSequenceNumber action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_QuickCreateEditor_SaveSequenceNumber_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('move');
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function move(App\Request $request)
	{
		$updatedFieldsList = $request->getArray('updatedFields', 'Integer');
		$result = Settings_QuickCreateEditor_Module_Model::updateFieldSequenceNumber($updatedFieldsList);

		$response = new Vtiger_Response();
		$response->setResult(!empty($result));
		$response->emit();
	}
}
