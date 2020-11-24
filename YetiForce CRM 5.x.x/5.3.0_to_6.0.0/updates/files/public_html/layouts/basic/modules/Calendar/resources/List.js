/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

Vtiger_List_Js(
	'Calendar_List_Js',
	{
		triggerImportAction: function (importUrl) {
			var progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request(importUrl).done(function (data) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				if (data) {
					app.showModalWindow(data, function (data) {
						jQuery('#ical_import').validationEngine(app.validationEngineOptions);
					});
				}
			});
		}
	},
	{}
);
