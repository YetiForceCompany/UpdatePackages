<?php
/**
 * Settings LangManagement export action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Export language action.
 */
class Settings_LangManagement_Export_Action extends Settings_Vtiger_IndexAjax_View
{
	/**
	 * Process request.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$lang = $request->getByType('lang', 1);

		$package = new vtlib\LanguageExport();
		$package->exportLanguage($lang, '', $lang . '.zip', true);
	}
}
