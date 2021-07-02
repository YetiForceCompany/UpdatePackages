/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

new ClipboardJS('#copy-button', {
	text: function (trigger) {
		app.showNotify({
			text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
			type: 'success'
		});
		var element = jQuery('#' + trigger.getAttribute('data-copy-target'));
		var password = element.text();
		if (password === '') {
			password = element.val();
		}
		return password;
	}
});
