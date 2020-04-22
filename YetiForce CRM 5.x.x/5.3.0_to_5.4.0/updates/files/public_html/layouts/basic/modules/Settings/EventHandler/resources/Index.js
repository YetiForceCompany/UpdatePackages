/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_EventHandler_Index_Js',
	{},
	{
		registerSave: function() {
			let tab = $('#my-tab-content');
			tab.find('input').on('change', function() {
				let name = this.name;
				let checked = this.checked;
				let tabName = $(this)
					.parents('.js-tab')
					.data('name');

				AppConnector.request({
					module: 'EventHandler',
					parent: 'Settings',
					action: 'Save',
					mode: 'set',
					tab: tabName,
					name: name,
					val: checked
				})
					.done(function(data) {
						Vtiger_Helper_Js.showPnotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function() {
						Vtiger_Helper_Js.showPnotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerEvents: function() {
			this.registerSave();
		}
	}
);
