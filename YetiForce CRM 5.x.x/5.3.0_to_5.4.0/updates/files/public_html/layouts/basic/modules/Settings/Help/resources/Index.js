/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js('Settings_Help_Index_Js', {
	registerPagination: function () {
		var page = $('.pagination .pageNumber');
		var thisInstance = this;
		page.on('click', function () {
			thisInstance.loadContent('github', $(this).data('id'));
		});
	},
	resisterSaveKeys: function (modal) {
		var thisInstance = this;
		var container = modal.find('.authModalContent');
		container.validationEngine(app.validationEngineOptions);
		container.find('.saveKeys').on('click', function () {
			if (container.validationEngine('validate')) {
				var params = {
					module: 'Github',
					parent: app.getParentModuleName(),
					action: 'SaveKeysAjax',
					username: $('[name="username"]').val(),
					client_id: $('[name="client_id"]').val(),
					token: $('[name="token"]').val()
				};
				container.progressIndicator({});
				AppConnector.request(params)
					.done(function (data) {
						container.progressIndicator({ mode: 'hide' });
						if (data.result.success == false) {
							var errorDiv = container.find('.errorMsg');
							errorDiv.removeClass('d-none');
							errorDiv.html(app.vtranslate('JS_ERROR_KEY'));
						} else {
							app.hideModalWindow();
							thisInstance.reloadContent();
							var params = {
								title: app.vtranslate('JS_LBL_PERMISSION'),
								text: app.vtranslate('JS_AUTHORIZATION_COMPLETE'),
								type: 'success'
							};
							Vtiger_Helper_Js.showMessage(params);
						}
					})
					.fail(function (error, err) {
						container.progressIndicator({ mode: 'hide' });
						app.hideModalWindow();
					});
			}
		});
	},
	registerAuthorizedEvent: function () {
		var thisInstance = this;
		$('.showModal').on('click', function () {
			app.showModalWindow($('.authModal'), function (container) {
				thisInstance.resisterSaveKeys(container);
			});
		});
	},
	registerGithubEvents: function (container) {
		var thisInstance = this;
		thisInstance.registerAuthorizedEvent();
		thisInstance.registerPagination();
		container.find('.js-switch--state, .js-switch--author').on('change', () => {
			thisInstance.loadContent('github', 1);
		});
		$('.addIssuesBtn').on('click', function () {
			var params = {
				module: 'Github',
				parent: app.getParentModuleName(),
				view: 'AddIssue'
			};
			container.progressIndicator({});
			AppConnector.request(params).done(function (data) {
				container.progressIndicator({ mode: 'hide' });
				app.showModalWindow(data, function () {
					thisInstance.loadEditorElement();
					thisInstance.registerSaveIssues();
				});
			});
		});
	},
	registerEventsLoadContent: function (thisInstance, mode, container) {
		thisInstance.registerGithubEvents(container);
	}
});
