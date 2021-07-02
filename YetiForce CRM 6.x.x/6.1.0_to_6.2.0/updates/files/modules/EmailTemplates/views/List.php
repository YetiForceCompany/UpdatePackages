<?php

/**
 * EmailTemplates list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class EmailTemplates_List_View extends Vtiger_List_View
{
	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		$parentScript = parent::getFooterScripts($request);
		$fileNames = [
			'libraries.clipboard.dist.clipboard',
		];
		$scriptInstances = $this->checkAndConvertJsScripts($fileNames);

		return array_merge($parentScript, $scriptInstances);
	}
}
