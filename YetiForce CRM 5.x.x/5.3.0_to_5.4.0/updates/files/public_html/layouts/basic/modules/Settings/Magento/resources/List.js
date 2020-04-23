/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_Magento_List_Js',
	{
		/**
		 * Restart synchronization
		 *
		 * @param   {int}  record
		 */
		reload(record) {
			AppConnector.request({
				module: 'Magento',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'reload',
				record: record
			}).done(data => {
				Vtiger_Helper_Js.showPnotify({
					type: 'success',
					text: data.result.message
				});
			});
		}
	},
	{}
);
