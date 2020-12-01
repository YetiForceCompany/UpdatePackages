/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_MailRbl_Index_Js',
	{},
	{
		dataTable: false,
		dataTableMap: {
			request: {
				order: [[0, 'desc']],
				columns: [
					{ data: 'datetime' },
					{ orderable: false, data: 'sender' },
					{ orderable: false, data: 'recipient' },
					{ data: 'type' },
					{ data: 'status' },
					{ data: 'user' },
					{
						orderable: false,
						data: function (row) {
							let action = '';
							action += '<div class="o-tab__container--action">';
							action += `<button type="button" class="btn btn-secondary btn-xs js-details" data-id="${
								row['id']
							}" title="${app.vtranslate(
								'BTN_SHOW_DETAILS'
							)}" data-js="click"><span class="fas fa-search-plus"></span></button>`;
							action += `<button type="button" class="btn btn-primary btn-xs ml-2 js-send-request-id" data-id="${
								row['id']
							}" title="${app.vtranslate(
								'BTN_STATUS_ACTION_SEND_REQUEST'
							)}" data-js="click"><span class="fas fa-paper-plane"></span></button>`;
							if (row['statusId'] !== 1) {
								action += `<button type="button" class="btn btn-success btn-xs ml-2 js-update" data-id="${
									row['id']
								}" data-status="1" title="${app.vtranslate(
									'BTN_STATUS_ACTION_ACCEPT'
								)}" data-js="click"><span class="fas fa-check"></span></button>`;
							}
							if (row['statusId'] !== 2) {
								action += `<button type="button" class="btn btn-warning btn-xs ml-2 js-update" data-id="${
									row['id']
								}" data-status="2" title="${app.vtranslate(
									'BTN_STATUS_ACTION_REJECT'
								)}" data-js="click"><span class="fas fa-times"></span></button>`;
							}
							action += `<button type="button" class="btn btn-danger btn-xs ml-2 js-trash" data-id="${
								row['id']
							}" title="${app.vtranslate('BTN_DELETE')}" data-js="click"><span class="fas fa-trash"></span></button>`;
							action += '</dv>';
							return action;
						},
						defaultContent: ''
					}
				]
			},
			blackList: {
				columns: [
					{ data: 'ip' },
					{ data: 'status' },
					{
						orderable: false,
						data: function (row) {
							let action = '';
							if (row['request'] != 0) {
								action += `<button type="button" class="btn btn-secondary btn-sm js-details" data-id="${
									row['request']
								}" title="${app.vtranslate(
									'BTN_SHOW_DETAILS'
								)}" data-js="click"><span class="fas fa-search-plus"></span></button>`;
							}
							return action;
						},
						defaultContent: ''
					},
					{
						orderable: false,
						data: function (row) {
							let action = '';
							if (row['statusId'] !== 0) {
								action += `<button type="button" class="btn btn-success btn-sm js-update" data-id="${
									row['id']
								}" data-status="0" title="${app.vtranslate(
									'BTN_STATUS_ACTION_ACCEPT'
								)}" data-js="click"><span class="fas fa-check"></span></button>`;
							}
							if (row['statusId'] !== 1) {
								action += `<button type="button" class="btn btn-warning btn-sm ml-2 js-update" data-id="${
									row['id']
								}" data-status="1" title="${app.vtranslate(
									'BTN_UNLOCK_STATUS_ACTION_REJECT'
								)}" data-js="click"><span class="fas fa-times"></span></button>`;
							}
							action += `<button type="button" class="btn btn-danger btn-sm ml-2 js-trash" data-id="${
								row['id']
							}" title="${app.vtranslate('BTN_DELETE')}" data-js="click"><span class="fas fa-trash"></span></button>`;
							return action;
						},
						defaultContent: ''
					}
				]
			},
			whiteList: {
				columns: [
					{ data: 'ip' },
					{ data: 'status' },
					{
						orderable: false,
						data: function (row) {
							let action = '';
							if (row['request'] != 0) {
								action += `<button type="button" class="btn btn-secondary btn-sm js-details" data-id="${
									row['request']
								}" title="${app.vtranslate(
									'BTN_SHOW_DETAILS'
								)}" data-js="click"><span class="fas fa-search-plus"></span></button>`;
							}
							return action;
						},
						defaultContent: ''
					},
					{
						orderable: false,
						data: function (row) {
							let action = '';
							if (row['statusId'] !== 0) {
								action += `<button type="button" class="btn btn-success btn-sm js-update" data-id="${
									row['id']
								}" data-status="0" title="${app.vtranslate(
									'BTN_STATUS_ACTION_ACCEPT'
								)}" data-js="click"><span class="fas fa-check"></span></button>`;
							}
							if (row['statusId'] !== 1) {
								action += `<button type="button" class="btn btn-warning btn-sm ml-2 js-update" data-id="${
									row['id']
								}" data-status="1" title="${app.vtranslate(
									'BTN_UNLOCK_STATUS_ACTION_REJECT'
								)}" data-js="click"><span class="fas fa-times"></span></button>`;
							}
							action += `<button type="button" class="btn btn-danger btn-sm ml-2 js-trash" data-id="${
								row['id']
							}" title="${app.vtranslate('BTN_DELETE')}" data-js="click"><span class="fas fa-trash"></span></button>`;
							return action;
						},
						defaultContent: ''
					}
				]
			},
			publicRbl: {
				columns: [
					{ data: 'ip' },
					{ data: 'type' },
					{ data: 'status' },
					{
						orderable: false,
						defaultContent: ''
					}
				]
			}
		},
		/**
		 * Register DataTable
		 */
		registerDataTable: function (container) {
			const self = this;
			let form = container.find('.js-filter-form');
			App.Fields.Date.registerRange(form);
			let table = container.find('.js-data-table');
			let mode = container.attr('id');
			if (table.hasClass('dataTable')) {
				table.DataTable().clear().destroy();
			}
			self.dataTable = app.registerDataTables(
				table,
				Object.assign(
					{
						processing: true,
						serverSide: true,
						paging: true,
						searching: false,
						lengthChange: false,
						pageLength: 20,
						ajax: {
							url: 'index.php?parent=Settings&module=MailRbl&action=GetData&mode=' + mode,
							type: 'POST',
							data: function (data) {
								data = $.extend(data, form.serializeFormData());
							}
						},
						order: []
					},
					self.dataTableMap[mode]
				)
			);
			container.find('input,select').on('change', function () {
				self.dataTable.ajax.reload();
			});
			return table;
		},
		/**
		 * Register tab events
		 * @param {jQuery} contentContainer
		 */
		registerTabEvents: function (contentContainer = $('.js-tab.active')) {
			const self = this;
			let mode = contentContainer.attr('id');
			let table = this.registerDataTable(contentContainer);
			table.off('click', '.js-details').on('click', '.js-details', function () {
				let progressIndicatorElement = jQuery.progressIndicator();
				app.showModalWindow(
					null,
					'index.php?module=AppComponents&view=MailMessageAnalysisModal&record=' + this.dataset.id,
					function (container) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						container.find('iframe').each(function () {
							let iframe = $(this);
							iframe.on('load', (e) => {
								let content = iframe.contents();
								iframe.height(content.find('body').height() + 30);
								content.find('head').append('<style>body{margin: 0;}p{margin: 0.5em 0;}</style>');
							});
						});
					}
				);
			});
			table.off('click', '.js-trash').on('click', '.js-trash', function () {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					mode: mode,
					action: 'DeleteAjax',
					record: this.dataset.id
				}).done(function () {
					self.dataTable.ajax.reload();
				});
			});
			table.off('click', '.js-update').on('click', '.js-update', function () {
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					mode: mode,
					action: 'SaveAjax',
					record: this.dataset.id,
					status: this.dataset.status
				}).done(function (response) {
					self.dataTable.ajax.reload();
					app.showNotify({
						text: app.vtranslate(response.result.message),
						type: true === response.result.success ? 'success' : 'error'
					});
				});
			});
			table.off('click', '.js-send-request-id').on('click', '.js-send-request-id', function () {
				self.sendRequest(this.dataset.id);
			});
		},
		sendRequest: function (id) {
			const self = this;
			app.showModalWindow(
				null,
				'index.php?parent=Settings&module=MailRbl&view=ReportModal&id=' + id,
				function (container) {
					let form = container.find('form');
					container.find('.js-modal__save').on('click', function () {
						form.submit();
						self.dataTable.ajax.reload();
					});
				}
			);
		},
		/**
		 * Register events
		 */
		registerEvents: function () {
			this.registerTabEvents();
			$('#tabs a[data-toggle="tab"]').on('shown.bs.tab', (_) => {
				this.registerTabEvents();
			});
		}
	}
);
