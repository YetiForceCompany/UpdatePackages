/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Users_PasswordModal_JS',
	{},
	{
		registerValidatePassword: function (modal) {
			modal.on('click', '.js-validate-password', function (e) {
				AppConnector.request({
					module: 'Users',
					action: 'VerifyData',
					mode: 'validatePassword',
					record: modal.find('[name="record"]').val(),
					password: modal.find('[name="' + $(e.currentTarget).data('field') + '"]').val()
				}).done(function (data) {
					if (data.success && data.result) {
						Vtiger_Helper_Js.showMessage({
							text: data.result.message,
							type: data.result.type
						});
					}
				});
			});
		},
		registerEvents: function (modal) {
			this.registerValidatePassword(modal);
		}
	}
);
