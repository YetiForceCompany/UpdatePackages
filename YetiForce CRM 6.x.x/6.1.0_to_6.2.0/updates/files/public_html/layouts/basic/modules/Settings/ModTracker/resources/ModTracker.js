/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_ModTracker_List_Js',
	{},
	{
		/**
		 * Register events for active elements
		 */
		registerActiveEvent: function () {
			var modTrackerContainer = jQuery('#modTrackerContainer');
			modTrackerContainer.on('change', '.js-active-modtracker', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var tr = currentTarget.closest('.js-row');
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'Save';
				params['mode'] = 'changeActiveStatus';
				params['id'] = tr.data('id');
				params['status'] = currentTarget.prop('checked');
				AppConnector.request(params)
					.done(function (data) {
						var params = {};
						params['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(params);
					})
					.fail(function (error) {
						var params = {};
						params['text'] = error;
						Settings_Vtiger_Index_Js.showMessage(params);
					});
			});
		},

		/**
		 * Function to register events
		 */
		registerEvents: function () {
			this.registerActiveEvent();
		}
	}
);
