/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_Mail_Config_Js',
	{},
	{
		registerChangeConfig() {
			const container = $('.configContainer');
			container.on('change', '.configCheckbox', function () {
				const progressIndicator = $.progressIndicator();
				const params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'SaveAjax';
				params['mode'] = 'updateConfig';
				params['type'] = $(this).data('type');
				params['name'] = $(this).attr('name');
				params['val'] = this.checked;
				AppConnector.request(params)
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						let messageParams = {};
						messageParams['text'] = data.result.message;
						Settings_Vtiger_Index_Js.showMessage(messageParams);
					})
					.fail(function (error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerSignature() {
			const container = $('#signature');
			App.Fields.Text.Editor.register(container.find('.js-editor'), {
				height: '20em'
			});
			App.Tools.VariablesPanel.registerRefreshCompanyVariables(container);
			container.find('.js-save-signature').on('click', function () {
				const progressIndicator = $.progressIndicator();
				const editor = CKEDITOR.instances.signatureEditor;
				const params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'SaveAjax';
				params['mode'] = 'updateSignature';
				params['val'] = editor.getData();
				AppConnector.request(params)
					.done(function (data) {
						progressIndicator.progressIndicator({ mode: 'hide' });
						Settings_Vtiger_Index_Js.showMessage({
							text: data.result.message
						});
					})
					.fail(function (error) {
						progressIndicator.progressIndicator({ mode: 'hide' });
					});
			});
		},
		registerEvents() {
			const thisInstance = this;
			thisInstance.registerChangeConfig();
			thisInstance.registerSignature();
			App.Fields.Text.registerCopyClipboard($('.js-container-variable'));
		}
	}
);
