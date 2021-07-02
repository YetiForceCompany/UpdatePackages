/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_WebserviceApps_Index_Js',
	{},
	{
		/**
		 * Get container
		 *
		 * @returns {HTMLElement|jQuery}
		 */
		getContainer() {
			return this.container;
		},
		/**
		 * Register actions for record
		 */
		registerTableEvents: function () {
			var thisInstance = this;
			const container = (this.container = $('.configContainer'));
			container.find('.edit').on('click', function (e) {
				var currentTarget = $(e.currentTarget);
				var trRow = currentTarget.closest('tr');
				thisInstance.showFormToEditKey(trRow.data('id'));
			});
			container.find('.remove').on('click', function (e) {
				var removeButton = jQuery(e.currentTarget);
				var currentTrElement = removeButton.closest('tr');
				var message = app.vtranslate('JS_DELETE_CONFIRMATION');
				Vtiger_Helper_Js.showConfirmationBox({ message: message }).done(function (e) {
					var params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'Delete',
						id: currentTrElement.data('id')
					};
					var progress = jQuery.progressIndicator();
					AppConnector.request(params).done(function (data) {
						progress.progressIndicator({ mode: 'hide' });
						thisInstance.loadTable();
					});
				});
			});
		},
		/**
		 * Refresh tables with records
		 */
		loadTable: function () {
			var thisInstance = this;
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'Index'
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				$('.configContainer').html(data);
				thisInstance.registerTableEvents();
			});
		},
		/**
		 * Show forms to edit or create record
		 * @param {int} id
		 */
		showFormToEditKey: function (id) {
			var thisInstance = this;
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'CreateApp'
			};
			if (id != '') {
				params['record'] = id;
			}
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				app.showModalWindow(data, function (container) {
					const prevButton = container.find('.previewPassword');
					const password = container.find('[name="pass"]');
					prevButton.on('mousedown', function (e) {
						password.attr('type', 'text');
					});
					prevButton.on('mouseup', function (e) {
						password.attr('type', 'password');
					});
					prevButton.on('mouseout', function (e) {
						password.attr('type', 'password');
					});
					const clipboard = App.Fields.Text.registerCopyClipboard(container, '.copyPassword');
					container.one('hidden.bs.modal', function () {
						clipboard.destroy();
					});
					var form = container.find('form');
					form.validationEngine(app.validationEngineOptions);
					container.find('[name="saveButton"]').on('click', function () {
						if (form.validationEngine('validate')) {
							let formData = form.serializeFormData();
							formData['module'] = app.getModuleName();
							formData['parent'] = app.getParentModuleName();
							formData['action'] = 'SaveAjax';
							formData['status'] = container.find('[name="status"]').is(':checked');
							if (id != '') {
								formData['id'] = id;
							}
							AppConnector.request(formData).done(function (data) {
								if (data.result === true) {
									thisInstance.loadTable();
									app.hideModalWindow();
								} else if (typeof data.result.error !== 'undefined') {
									app.showNotify({
										text: data.result.error,
										type: 'error'
									});
								}
							});
						}
					});
				});
			});
		},
		/**
		 * Register button to create record
		 */
		registerAddButton: function () {
			var thisInstance = this;
			$('.createKey').on('click', function () {
				thisInstance.showFormToEditKey();
			});
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			this.registerAddButton();
			this.registerTableEvents();
			App.Fields.Text.registerCopyClipboard(this.getContainer());
		}
	}
);
