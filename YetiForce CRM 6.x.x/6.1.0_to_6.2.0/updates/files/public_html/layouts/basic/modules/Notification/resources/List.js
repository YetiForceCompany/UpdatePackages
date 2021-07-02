/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_List_Js(
	'Notification_List_Js',
	{
		setAsMarked: function (id) {
			Vtiger_Index_Js.markNotifications(id).done(function () {
				Vtiger_Index_Js.getNotificationsForReminder();
				Vtiger_List_Js.getInstance().getListViewRecords();
			});
		}
	},
	{}
);
