/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
$.Class(
	'Base_RecordConverter_JS',
	{},
	{
		container: false,
		/**
		 * Function get values for request query
		 * @returns {{module: string, view: string, convertType: integer, fieldMerge: string, onlyBody: boolean, destinyModule: string, sourceView: string}}
		 */
		getParams: function () {
			let params = {
				module: this.container.data('module'),
				view: this.container.data('view'),
				convertType: this.container.find('.js-convert-type option:selected').val(),
				onlyBody: true,
				sourceView: app.getViewName()
			};
			if (app.getViewName() === 'List') {
				let listInstance = Vtiger_List_Js.getInstance();
				params.selected_ids = listInstance.readSelectedIds(true);
				params.excluded_ids = listInstance.readExcludedIds(true);
				params.cvId = listInstance.getCurrentCvId();
				if (listInstance.getListSearchInstance()) {
					let searchValue = listInstance.getListSearchInstance().getAlphabetSearchValue();
					params.search_params = JSON.stringify(
						listInstance.getListSearchInstance().getListSearchParams()
					);
					if (typeof searchValue != 'undefined' && searchValue.length > 0) {
						params.search_key = listInstance.getListSearchInstance().getAlphabetSearchField();
						params.search_value = searchValue;
						params.operator = 's';
					}
				}
			} else {
				params.selected_ids = app.getRecordId();
			}
			return params;
		},
		/**
		 * Function load modal window
		 * @returns {object}
		 */
		loadModalWindow: function () {
			const body = this.container.find('.js-modal-body');
			const aDeferred = $.Deferred();
			const progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					enabled: true,
					elementToBlock: body
				}
			});
			AppConnector.request(this.getParams()).then(
				function (responseData) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					body.html($(responseData).html());
					App.Fields.Picklist.showSelect2ElementView(body.find('.select2'));
					aDeferred.resolve(responseData);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				}
			);
			return aDeferred.promise();
		},
		/**
		 * Function listener to change convert type
		 */
		registerChangeConvertType: function () {
			let self = this;
			self.container.on('change', '.js-convert-type', (e) => {
				self.loadModalWindow();
			});
		},
		/**
		 * Function listener to send a form
		 * * @returns {object}
		 */
		registerSubmitForm: function () {
			let self = this;
			self.container.on('click', "[name='saveButton']", (e) => {
				let convertType = self.container.find('.js-convert-type option:selected').val();
				if (convertType) {
					let formData = self.container.find('form').serializeFormData();
					let postData = {};
					if (app.getViewName() === 'List') {
						let listInstance = Vtiger_List_Js.getInstance();
						postData = listInstance.getDefaultParams();
						postData.selected_ids = listInstance.readSelectedIds(true);
						postData.excluded_ids = listInstance.readExcludedIds(true);
						postData.cvid = listInstance.getCurrentCvId();
					} else {
						postData = {
							selected_ids: app.getRecordId()
						};
					}
					postData.convertType = convertType;
					postData.viewInfo = app.getViewName();
					const aDeferred = $.Deferred();
					const progressIndicatorElement = $.progressIndicator({
						blockInfo: {
							enabled: true,
							elementToBlock: self.container.find('.modal-body')
						}
					});
					AppConnector.request($.extend(formData, postData)).done(
						function (responseData) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							let parseResult = JSON.parse(responseData);
							if (parseResult.result.redirect) {
								window.location.href = parseResult.result.redirect;
							} else {
								if (parseResult.result.createdRecords) {
									Vtiger_Helper_Js.showMessage({
										text: app.vtranslate(parseResult.result.createdRecords),
										type: 'success'
									});
								}
								if (parseResult.result.error) {
									Vtiger_Helper_Js.showMessage({
										text: app.vtranslate(parseResult.result.error),
										type: 'error'
									});
								}
								app.hideModalWindow();
							}
							aDeferred.resolve(responseData);
						},
						function (textStatus, errorThrown) {
							aDeferred.reject(textStatus, errorThrown);
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						}
					);
					return aDeferred.promise();
				}
			});
		},
		/**
		 * Register events function
		 * @param modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.registerChangeConvertType();
			this.registerSubmitForm();
		}
	}
);
