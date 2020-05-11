/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Vtiger_RelatedList_Js',
	{
		relatedListInstance: false,
		getInstance: function (parentId, parentModule, selectedRelatedTabElement, relatedModuleName) {
			if (
				Vtiger_RelatedList_Js.relatedListInstance === false ||
				Vtiger_RelatedList_Js.relatedListInstance.moduleName !== relatedModuleName
			) {
				let moduleClassName = app.getModuleName() + '_RelatedList_Js',
					fallbackClassName = Vtiger_RelatedList_Js,
					instance;
				if (typeof window[moduleClassName] !== 'undefined') {
					instance = new window[moduleClassName]();
				} else {
					instance = new fallbackClassName();
				}
				instance.parentRecordId = parentId;
				instance.parentModuleName = parentModule;
				instance.selectedRelatedTabElement = selectedRelatedTabElement;
				instance.moduleName = relatedModuleName;
				instance.relatedTabsContainer = selectedRelatedTabElement.closest('div.related');
				instance.content = $('div.contents', instance.relatedTabsContainer.closest('div.detailViewContainer'));
				instance.relatedView = instance.content.find('input.relatedView').val();
				Vtiger_RelatedList_Js.relatedListInstance = instance;
			}
			return Vtiger_RelatedList_Js.relatedListInstance;
		},
		triggerMassAction: function (massActionUrl, type) {
			const self = this.relatedListInstance;
			let validationResult = self.checkListRecordSelected();
			if (validationResult != true) {
				let progressIndicatorElement = $.progressIndicator(),
					selectedIds = self.readSelectedIds(true),
					excludedIds = self.readExcludedIds(true),
					cvId = self.getCurrentCvId(),
					postData = self.getCompleteParams();
				delete postData.mode;
				delete postData.view;
				postData.viewname = cvId;
				postData.selected_ids = selectedIds;
				postData.excluded_ids = excludedIds;
				let actionParams = {
					type: 'POST',
					url: massActionUrl,
					data: postData,
				};
				if (type === 'sendByForm') {
					app.openUrlMethodPost(massActionUrl, postData);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				} else {
					AppConnector.request(actionParams)
						.done(function (responseData) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							if (responseData && responseData.result !== null) {
								if (responseData.result.notify) {
									Vtiger_Helper_Js.showMessage(responseData.result.notify);
								}
								if (responseData.result.reloadList) {
									Vtiger_Detail_Js.reloadRelatedList();
								}
								if (responseData.result.procesStop) {
									progressIndicatorElement.progressIndicator({ mode: 'hide' });
									return false;
								}
							}
						})
						.fail(function (error, err) {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				}
			} else {
				self.noRecordSelectedAlert();
			}
		},
		/**
		 * Method to verify if selected files exist
		 * @param {int} selectedIds
		 * @return boolean
		 */
		verifyFileExist: function (selectedIds) {
			let aDeferred = jQuery.Deferred();
			AppConnector.request({
				module: 'Documents',
				action: 'CheckFileIntegrity',
				mode: 'multiple',
				record: selectedIds,
			}).done(function (responseData) {
				if (responseData && responseData.result !== null) {
					if (responseData.result.message) {
						Vtiger_Helper_Js.showPnotify({ text: responseData.result.message });
						aDeferred.resolve(false);
					} else {
						aDeferred.resolve(true);
					}
				}
			});
			return aDeferred.promise();
		},
		/**
		 * Method to trigger mass download action
		 * @param massActionUrl
		 * @param type
		 */
		triggerMassDownload: function (massActionUrl, type) {
			const self = this.relatedListInstance,
				thisInstance = this;
			this.verifyFileExist(self.readSelectedIds(true)).done(function (data) {
				if (true === data) {
					thisInstance.triggerMassAction(
						massActionUrl.substring(0, massActionUrl.indexOf('&mode=multiple')),
						type
					);
				}
			});
		},
	},
	{
		selectedRelatedTabElement: false,
		parentRecordId: false,
		parentModuleName: false,
		moduleName: false,
		relatedTabsContainer: false,
		content: false,
		listSearchInstance: false,
		detailViewContentHolder: false,
		relatedView: false,
		frameProgress: false,
		noEventsListSearch: false,
		listViewContainer: false,
		setSelectedTabElement: function (tabElement) {
			this.selectedRelatedTabElement = tabElement;
		},
		getSelectedTabElement: function () {
			return this.selectedRelatedTabElement;
		},
		getParentId: function () {
			return this.parentRecordId;
		},
		getRelatedContainer: function () {
			return this.content;
		},
		setRelatedContainer: function (container) {
			this.content = container;
			this.relatedView = container.find('input.relatedView').val();
		},
		getContentHolder: function () {
			if (this.detailViewContentHolder == false) {
				this.detailViewContentHolder = $('div.details div.contents');
			}
			return this.detailViewContentHolder;
		},
		getCurrentPageNum: function () {
			return $('input[name="currentPageNum"]', this.content).val();
		},
		setCurrentPageNumber: function (pageNumber) {
			$('input[name="currentPageNum"]', this.content).val(pageNumber);
		},
		getOrderBy: function () {
			return $('#orderBy', this.content).val();
		},
		getDefaultParams: function () {
			let container = this.getRelatedContainer();
			let params = {
				relationId: container.find('#relationId').val(),
				orderby: this.getOrderBy(),
				page: this.getCurrentPageNum(),
			};
			if (container.find('.pagination').length) {
				params['totalCount'] = container.find('.pagination').data('totalCount');
			}
			if (container.find('.entityState').length) {
				params['entityState'] = container.find('.entityState').val();
			}
			if (this.listSearchInstance) {
				var searchValue = this.listSearchInstance.getAlphabetSearchValue();
				params.search_params = JSON.stringify(this.listSearchInstance.getListSearchParams());
			}
			if (typeof searchValue !== 'undefined' && searchValue.length > 0) {
				params['search_key'] = this.listSearchInstance.getAlphabetSearchField();
				params['search_value'] = searchValue;
				params['operator'] = 's';
			}
			if (this.moduleName == 'Calendar') {
				var switchBtn = container.find('.js-switch--calendar');
				if (switchBtn.length) {
					params.time = switchBtn.first().prop('checked') ? 'current' : 'history';
				}
			}
			return params;
		},
		getCompleteParams: function () {
			let container = this.getRelatedContainer();
			let params = {
				view: 'Detail',
				module: this.parentModuleName,
				record: this.getParentId(),
				relatedModule: this.moduleName,
				relatedView: this.relatedView,
				mode: 'showRelatedList',
				tab_label: container.find('#tab_label').val(),
			};
			return $.extend(this.getDefaultParams(), params);
		},
		loadRelatedList: function (params) {
			var aDeferred = jQuery.Deferred();
			var thisInstance = this;
			if (typeof thisInstance.moduleName === 'undefined' || thisInstance.moduleName.length <= 0) {
				var currentInstance = Vtiger_Detail_Js.getInstance();
				currentInstance.loadWidgets();
				return aDeferred.promise();
			}
			var progressInstance = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
				},
			});
			var completeParams = this.getCompleteParams();
			var activeTabsReference = thisInstance.relatedTabsContainer.find('li.active').data('reference');
			AppConnector.request($.extend(completeParams, params))
				.done(function (responseData) {
					var currentInstance = Vtiger_Detail_Js.getInstance();
					currentInstance.loadWidgets();
					progressInstance.progressIndicator({ mode: 'hide' });
					if (activeTabsReference !== 'ProductsAndServices') {
						thisInstance.relatedTabsContainer.find('li').removeClass('active');
						thisInstance.selectedRelatedTabElement.addClass('active');
						thisInstance.content.html(responseData);
						$('.pageNumbers', thisInstance.content).tooltip();
						thisInstance.registerPostLoadEvents();
						thisInstance.registerListEvents();
					}
					aDeferred.resolve(responseData);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
					Vtiger_Helper_Js.showPnotify({
						text: app.vtranslate('JS_NOT_ALLOWED_VALUE'),
						type: 'error',
					});
					progressInstance.progressIndicator({ mode: 'hide' });
				});
			return aDeferred.promise();
		},
		triggerDisplayTypeEvent: function () {
			var widthType = app.cacheGet('widthType', 'narrowWidthType');
			if (widthType) {
				var elements = this.content.find('.listViewEntriesTable').find('td,th');
				elements.attr('class', widthType);
			}
		},
		showSelectRelation: function (extendParams) {
			let params = $.extend(this.getRecordsListParams(), extendParams);
			app.showRecordsList(params, (modal, instance) => {
				instance.setSelectEvent((responseData) => {
					this.addRelations(Object.keys(responseData)).done(() => {
						app.event.trigger('RelatedListView.AfterSelectRelation', responseData, this, instance, params);
						let detail = Vtiger_Detail_Js.getInstance();
						this.loadRelatedList().done(function () {
							detail.registerRelatedModulesRecordCount();
						});
						if (this.getSelectedTabElement().data('link-key') === 'LBL_RECORD_SUMMARY') {
							detail.loadWidgets();
							detail.registerRelatedModulesRecordCount();
						}
					});
				});
			});
		},
		getRecordsListParams: function () {
			return {
				module: this.moduleName,
				src_module: this.parentModuleName,
				src_record: this.parentRecordId,
				multi_select: true,
			};
		},
		addRelations: function (idList, params = {}) {
			var aDeferred = jQuery.Deferred();
			AppConnector.request(
				$.extend(
					{
						module: this.parentModuleName,
						action: 'RelationAjax',
						mode: 'addRelation',
						related_module: this.moduleName,
						src_record: this.parentRecordId,
						relationId: this.getCompleteParams()['relationId'],
						related_record_list: $.isArray(idList) ? JSON.stringify(idList) : idList,
					},
					params
				)
			)
				.done(function (responseData) {
					aDeferred.resolve(responseData);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		deleteRelation(target) {
			let params = {};
			if (target.data('url')) {
				params = target.data('url');
			} else {
				let id = target.data('id') ? target.data('id') : target.closest('tr').data('id');
				params = {
					module: this.parentModuleName,
					action: 'RelationAjax',
					mode: 'deleteRelation',
					related_module: this.moduleName,
					src_record: this.parentRecordId,
					relationId: this.getCompleteParams()['relationId'],
					related_record_list: JSON.stringify([id]),
				};
			}
			let progressInstance = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
			AppConnector.request(params)
				.done((response) => {
					progressInstance.progressIndicator({ mode: 'hide' });
					if (response.result) {
						let widget = target.closest('.widgetContentBlock');
						const detail = Vtiger_Detail_Js.getInstance();
						if (widget.length) {
							detail.loadWidget(widget);
							let updatesWidget = this.getContentHolder().find('[data-type="Updates"]');
							if (updatesWidget.length > 0) {
								detail.loadWidget(updatesWidget);
							}
						} else {
							this.loadRelatedList();
						}
						detail.registerRelatedModulesRecordCount();
					} else {
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_CANNOT_REMOVE_RELATION'));
					}
				})
				.fail(function (err, errThrow) {
					progressInstance.progressIndicator({ mode: 'hide' });
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_CANNOT_REMOVE_RELATION'));
				});
		},
		/**
		 * Function to handle next page navigation
		 */
		nextPageHandler: function () {
			var aDeferred = jQuery.Deferred();
			var thisInstance = this;
			var pageLimit = jQuery('#pageLimit', this.content).val();
			var noOfEntries = jQuery('#noOfEntries', this.content).val();
			if (noOfEntries == pageLimit) {
				var pageNumber = this.getCurrentPageNum();
				var nextPage = parseInt(pageNumber) + 1;
				this.loadRelatedList({
					page: nextPage,
				})
					.done(function (data) {
						thisInstance.setCurrentPageNumber(nextPage);
						aDeferred.resolve(data);
					})
					.fail(function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					});
			}
			return aDeferred.promise();
		},
		/**
		 * Function to handle next page navigation
		 */
		previousPageHandler: function () {
			const thisInstance = this,
				aDeferred = jQuery.Deferred();
			let pageNumber = this.getCurrentPageNum();
			if (pageNumber > 1) {
				let previousPage = parseInt(pageNumber) - 1;
				this.loadRelatedList({
					page: previousPage,
				})
					.done(function (data) {
						thisInstance.setCurrentPageNumber(previousPage);
						aDeferred.resolve(data);
					})
					.fail(function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					});
			}
			return aDeferred.promise();
		},
		/**
		 * Function to handle select page jump in related list
		 */
		selectPageHandler: function (pageNumber) {
			const thisInstance = this,
				aDeferred = jQuery.Deferred();
			this.loadRelatedList({
				page: pageNumber,
			})
				.done(function (data) {
					thisInstance.setCurrentPageNumber(pageNumber);
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to handle page jump in related list
		 */
		pageJumpHandler: function (e) {
			var aDeferred = jQuery.Deferred();
			var thisInstance = this;
			if (e.which == 13) {
				var element = jQuery(e.currentTarget);
				var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
				if (typeof response !== 'undefined') {
					element.validationEngine('showPrompt', response, '', 'topLeft', true);
					e.preventDefault();
				} else {
					element.validationEngine('hideAll');
					var jumpToPage = parseInt(element.val());
					var totalPages = parseInt(jQuery('#totalPageCount', thisInstance.content).text());
					if (jumpToPage > totalPages) {
						var error = app.vtranslate('JS_PAGE_NOT_EXIST');
						element.validationEngine('showPrompt', error, '', 'topLeft', true);
					}
					var invalidFields = element.parent().find('.formError');
					if (invalidFields.length < 1) {
						var currentPage = jQuery('input[name="currentPageNum"]', thisInstance.content).val();
						if (jumpToPage == currentPage) {
							var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + ' ' + jumpToPage;
							var params = {
								text: message,
								type: 'info',
							};
							Vtiger_Helper_Js.showMessage(params);
							e.preventDefault();
							return false;
						}
						this.loadRelatedList({
							page: jumpToPage,
						})
							.done(function (data) {
								thisInstance.setCurrentPageNumber(jumpToPage);
								aDeferred.resolve(data);
							})
							.fail(function (textStatus, errorThrown) {
								aDeferred.reject(textStatus, errorThrown);
							});
					} else {
						e.preventDefault();
					}
				}
			}
			return aDeferred.promise();
		},
		/**
		 * Function to add related record for the module
		 */
		addRelatedRecord: function (element, callback) {
			var aDeferred = jQuery.Deferred();
			var thisInstance = this;
			var referenceModuleName = this.moduleName;
			var parentId = this.getParentId();
			var parentModule = this.parentModuleName;
			var quickCreateParams = {};
			var relatedParams = {};
			var relatedField = element.data('name');
			var fullFormUrl = element.data('url');
			if (relatedField) {
				relatedParams[relatedField] = parentId;
			}
			var eliminatedKeys = new Array('view', 'module', 'mode', 'action');
			var preQuickCreateSave = function (data) {
				var index, queryParam, queryParamComponents;
				let queryParameters = [];

				//To handle switch to task tab when click on add task from related list of activities
				//As this is leading to events tab intially even clicked on add task
				if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
					var urlSplit = fullFormUrl.split('?');
					var queryString = urlSplit[1];
					queryParameters = queryString.split('&');
					for (index = 0; index < queryParameters.length; index++) {
						queryParam = queryParameters[index];
						queryParamComponents = queryParam.split('=');
						if (queryParamComponents[0] == 'mode' && queryParamComponents[1] == 'Calendar') {
							data.find('a[data-tab-name="Task"]').trigger('click');
						}
					}
				}
				jQuery('<input type="hidden" name="sourceModule" value="' + parentModule + '" />').appendTo(data);
				jQuery('<input type="hidden" name="sourceRecord" value="' + parentId + '" />').appendTo(data);
				jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);

				if (typeof relatedField !== 'undefined') {
					var field = data.find('[name="' + relatedField + '"]');
					//If their is no element with the relatedField name,we are adding hidden element with
					//name as relatedField name,for saving of record with relation to parent record
					if (field.length == 0) {
						jQuery('<input type="hidden" name="' + relatedField + '" value="' + parentId + '" />').appendTo(
							data
						);
					}
				}
				for (index = 0; index < queryParameters.length; index++) {
					queryParam = queryParameters[index];
					queryParamComponents = queryParam.split('=');
					if (
						jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1' &&
						data.find('[name="' + queryParamComponents[0] + '"]').length == 0
					) {
						jQuery(
							'<input type="hidden" name="' +
								queryParamComponents[0] +
								'" value="' +
								queryParamComponents[1] +
								'" />'
						).appendTo(data);
					}
				}
				if (typeof callback !== 'undefined') {
					callback();
				}
			};
			var postQuickCreateSave = function (data) {
				thisInstance.loadRelatedList().done(function (data) {
					aDeferred.resolve(data);
				});
			};

			//If url contains params then seperate them and make them as relatedParams
			if (typeof fullFormUrl !== 'undefined' && fullFormUrl.indexOf('?') !== -1) {
				var urlSplit = fullFormUrl.split('?');
				var queryString = urlSplit[1];
				var queryParameters = queryString.split('&');
				for (var index = 0; index < queryParameters.length; index++) {
					var queryParam = queryParameters[index];
					var queryParamComponents = queryParam.split('=');
					if (jQuery.inArray(queryParamComponents[0], eliminatedKeys) == '-1') {
						relatedParams[queryParamComponents[0]] = queryParamComponents[1];
					}
				}
			}

			quickCreateParams['data'] = relatedParams;
			quickCreateParams['callbackFunction'] = postQuickCreateSave;
			quickCreateParams['callbackPostShown'] = preQuickCreateSave;
			quickCreateParams['noCache'] = true;
			Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, quickCreateParams);
			return aDeferred.promise();
		},
		getRelatedPageCount: function () {
			var aDeferred = jQuery.Deferred();
			var element = this.content.find('#totalPageCount');
			var totalCountElem = this.content.find('#totalCount');
			var totalPageNumber = element.text();
			if (totalPageNumber == '') {
				element.progressIndicator({});
				AppConnector.request({
					module: this.parentModuleName,
					action: 'RelationAjax',
					mode: 'getRelatedListPageCount',
					record: this.getParentId(),
					relationId: this.getCompleteParams()['relationId'],
					relatedModule: this.moduleName,
				})
					.done(function (data) {
						var pageCount = data['result']['page'];
						var numberOfRecords = data['result']['numberOfRecords'];
						totalCountElem.val(numberOfRecords);
						element.text(pageCount);
						element.progressIndicator({ mode: 'hide' });
						aDeferred.resolve();
					})
					.fail(function (error, err) {
						aDeferred.reject(false);
					});
			} else {
				aDeferred.resolve();
			}
			return aDeferred.promise();
		},
		favoritesRelation: function (relcrmId, state) {
			var aDeferred = jQuery.Deferred();
			if (relcrmId) {
				AppConnector.request({
					module: this.parentModuleName,
					action: 'RelationAjax',
					mode: 'updateFavoriteForRecord',
					record: this.getParentId(),
					relcrmid: relcrmId,
					relatedModule: this.moduleName,
					relationId: this.getCompleteParams()['relationId'],
					actionMode: state ? 'delete' : 'add',
				})
					.done(function (data) {
						if (data.result) aDeferred.resolve(true);
					})
					.fail(function (error, err) {
						aDeferred.reject(false);
					});
			} else {
				aDeferred.reject(false);
			}
			return aDeferred.promise();
		},
		updatePreview: function (url) {
			var frame = this.content.find('.listPreviewframe');
			this.frameProgress = $.progressIndicator({
				position: 'html',
				message: app.vtranslate('JS_FRAME_IN_PROGRESS'),
				blockInfo: {
					enabled: true,
				},
			});
			var defaultView = '';
			if (app.getMainParams('defaultDetailViewName')) {
				defaultView =
					defaultView +
					'&mode=showDetailViewByMode&requestMode=' +
					app.getMainParams('defaultDetailViewName'); // full, summary
			}
			frame.attr('src', url.replace('view=Detail', 'view=DetailPreview') + defaultView);
		},
		registerUnreviewedCountEvent: function () {
			var ids = [];
			var relatedContent = this.content;
			var isUnreviewedActive = relatedContent.find('.unreviewed').length;
			relatedContent.find('tr.listViewEntries').each(function () {
				var id = jQuery(this).data('id');
				if (id) {
					ids.push(id);
				}
			});
			if (!ids || isUnreviewedActive < 1) {
				return;
			}
			AppConnector.request({
				action: 'ChangesReviewedOn',
				mode: 'getUnreviewed',
				module: 'ModTracker',
				sourceModule: this.moduleName,
				recordsId: ids,
			}).done(function (appData) {
				var data = appData.result;
				$.each(data, function (id, value) {
					if (value.a > 0) {
						relatedContent
							.find('tr[data-id="' + id + '"] .unreviewed .badge.all')
							.text(value.a)
							.parent()
							.removeClass('d-none');
					}
					if (value.m > 0) {
						relatedContent
							.find('tr[data-id="' + id + '"] .unreviewed .badge.mail')
							.text(value.m)
							.parent()
							.removeClass('d-none');
					}
				});
			});
		},
		registerChangeEntityStateEvent: function () {
			var thisInstance = this;
			var relatedContent = this.content;
			relatedContent.on('click', '.dropdownEntityState a', function (e) {
				var element = $(this);
				relatedContent.find('.entityState').val(element.data('value'));
				relatedContent.find('.pagination').data('totalCount', 0);
				relatedContent
					.find('.dropdownEntityState button')
					.find('span')
					.attr('class', element.find('span').attr('class'));
				thisInstance.loadRelatedList({ page: 1 });
			});
		},
		registerRowsEvent: function () {
			var thisInstance = this;
			if (this.relatedView === 'List' || this.relatedView === 'Detail') {
				this.content.find('.listViewEntries').on('click', function (e) {
					if ($(e.target).is('td')) {
						if (app.getViewName() == 'DetailPreview') {
							if ($(e.target).closest('tr').data('recordurl')) {
								top.document.location.href = $(e.target).closest('tr').data('recordurl');
							}
						} else {
							if ($(e.target).closest('tr').data('recordurl')) {
								document.location.href = $(e.target).closest('tr').data('recordurl');
							}
						}
					}
				});
				this.content.find('.showInventoryRow').on('click', function (e) {
					var target = $(this);
					var row = target.closest('tr');
					var inventoryRow = row.next();
					if (inventoryRow.hasClass('listViewInventoryEntries')) {
						inventoryRow.toggleClass('d-none');
					}
				});
			} else if (this.relatedView === 'ListPreview') {
				this.content.find('.listViewEntries').on('click', function (e) {
					let target = $(e.target);
					if (target.closest('div').hasClass('actions')) return;
					if (target.is('button') || target.parent().is('button')) return;
					if (target.closest('a').hasClass('noLinkBtn')) return;
					if ($(e.target, $(e.currentTarget)).is('td:first-child')) return;
					if (target.is('input[type="checkbox"]')) return;
					if ($.contains($(e.currentTarget).find('td:last-child').get(0), target[0])) return;
					if ($.contains($(e.currentTarget).find('td:first-child').get(0), target[0])) return;
					var recordUrl = $(this).data('recordurl');
					thisInstance.content.find('.listViewEntriesTable .listViewEntries').removeClass('active');
					$(this).addClass('active');
					thisInstance.updatePreview(recordUrl);
				});
			}
		},
		registerSummationEvent: function () {
			var thisInstance = this;
			this.content.on('click', '.listViewSummation button', function () {
				var button = $(this);
				var calculateValue = button.closest('td').find('.calculateValue');
				var params = thisInstance.getCompleteParams();
				params['action'] = 'RelationAjax';
				params['mode'] = 'calculate';
				params['fieldName'] = button.data('field');
				params['calculateType'] = button.data('operator');
				delete params['view'];
				var progress = $.progressIndicator({
					message: app.vtranslate('JS_CALCULATING_IN_PROGRESS'),
					position: 'html',
					blockInfo: {
						enabled: true,
					},
				});
				app.hidePopover(button);
				AppConnector.request(params).done(function (response) {
					if (response.success) {
						calculateValue.html(response.result);
					} else {
						calculateValue.html('');
					}
					progress.progressIndicator({ mode: 'hide' });
				});
				progress.progressIndicator({ mode: 'hide' });
			});
		},
		registerPreviewEvent: function () {
			var thisInstance = this;
			var contentHeight = this.content.find('.js-detail-preview,.js-list-preview');
			contentHeight.height(app.getScreenHeight() - (this.content.offset().top + $('.js-footer').height()));
			this.content.find('.listPreviewframe').on('load', function () {
				if (thisInstance.frameProgress) {
					thisInstance.frameProgress.progressIndicator({ mode: 'hide' });
				}
				contentHeight.height($(this).contents().find('.bodyContents').height() + 2);
			});
			this.content.find('.listViewEntriesTable .listViewEntries').first().trigger('click');
		},
		registerPaginationEvents: function () {
			var thisInstance = this;
			var relatedContent = this.content;
			this.content.on('click', '#relatedViewNextPageButton', function (e) {
				if ($(this).hasClass('disabled')) {
					return;
				}
				thisInstance.nextPageHandler();
			});
			this.content.on('click', '#relatedViewPreviousPageButton', function () {
				thisInstance.previousPageHandler();
			});
			this.content.on('click', '#relatedListPageJump', function (e) {
				thisInstance.getRelatedPageCount();
			});
			this.content
				.on('click', '#relatedListPageJumpDropDown > li', function (e) {
					e.stopImmediatePropagation();
				})
				.on('keypress', '#pageToJump', function (e) {
					thisInstance.pageJumpHandler(e);
				});
			this.content.on('click', '.pageNumber', function () {
				if ($(this).hasClass('disabled')) {
					return false;
				}
				thisInstance.selectPageHandler($(this).data('id'));
			});
			this.content.on('click', '#totalCountBtn', function () {
				app.hidePopover($(this));
				var params = {
					module: thisInstance.parentModuleName,
					view: 'Pagination',
					mode: 'getRelationPagination',
					record: thisInstance.getParentId(),
					relatedModule: thisInstance.moduleName,
					noOfEntries: $('#noOfEntries', relatedContent).val(),
					page: relatedContent.find('[name="currentPageNum"]').val(),
				};
				if (relatedContent.find('.entityState').length) {
					params['entityState'] = relatedContent.find('.entityState').val();
				}
				AppConnector.request(params).done(function (response) {
					relatedContent.find('.paginationDiv').html(response);
				});
			});
		},
		registerListEvents: function () {
			var relatedContent = this.content;
			var thisInstance = this;
			this.content.find('a.favorites').on('click', function (e) {
				var progressInstance = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true,
					},
				});
				var element = $(this);
				var row = element.closest('tr');
				thisInstance.favoritesRelation(row.data('id'), element.data('state')).done(function (response) {
					if (response) {
						var state = element.data('state') ? 0 : 1;
						element.data('state', state);
						if (state) {
							element.find('.far').addClass('d-none');
							element.find('.fas').removeClass('d-none');
						} else {
							element.find('.fas').addClass('d-none');
							element.find('.far').removeClass('d-none');
						}
						progressInstance.progressIndicator({ mode: 'hide' });
						var text = app.vtranslate('JS_REMOVED_FROM_FAVORITES');
						if (state) {
							text = app.vtranslate('JS_ADDED_TO_FAVORITES');
						}
						Vtiger_Helper_Js.showPnotify({ text: text, type: 'success' });
					}
				});
			});
			this.content.find('[name="addButton"]').on('click', function (e) {
				const element = $(this);
				if (element.hasClass('quickCreateSupported') !== true) {
					app.openUrl(element.data('url'));
					return;
				}
				thisInstance.addRelatedRecord(element);
			});
			this.content.find('.relatedHeader button.selectRelation').on('click', function (e) {
				let restrictionsField = $(this).data('rf');
				let params = {
					relationId: thisInstance.getCompleteParams()['relationId'],
				};
				if (restrictionsField && Object.keys(restrictionsField).length > 0) {
					params = {
						search_key: restrictionsField.key,
						search_value: restrictionsField.name,
						relationId: thisInstance.getCompleteParams()['relationId'],
					};
				}
				thisInstance.showSelectRelation(params);
			});
			this.content.find('button.relationDelete').on('click', function (e) {
				e.stopImmediatePropagation();
				let target = $(e.currentTarget);
				let params = {};
				if (target.data('confirm')) {
					params.message = target.data('confirm');
					params.title = target.html() + ' ' + target.data('content');
				} else if (target.data('content')) {
					params.message = target.data('content');
				} else {
					params.message = app.vtranslate('JS_DELETE_CONFIRMATION');
				}
				Vtiger_Helper_Js.showConfirmationBox(params).done(function () {
					thisInstance.deleteRelation(target);
				});
			});
			this.content.find('.js-switch--calendar').on('change', function (e) {
				thisInstance.loadRelatedList();
			});
			this.content.find('.relatedViewGroup a').on('click', function (e) {
				var element = $(this);
				thisInstance.relatedView = element.data('view');
				relatedContent.find('.pagination').data('totalCount', 0);
				thisInstance.loadRelatedList({ page: 1 });
			});
		},
		registerPostLoadEvents: function () {
			var thisInstance = this;
			this.registerRowsEvent();
			this.registerListScroll();
			if (this.relatedView === 'ListPreview') {
				this.registerPreviewEvent();
				if (!this.content.find('.gutter').length) {
					if (!this.content.find('.js-list-preview').length) return;
					this.setDomParams(this.content);
					this.toggleSplit(this.content);
					this.registerListPreviewEvents();
				}
			}
			this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.content, false, this);
			app.event.trigger('RelatedList.AfterLoad', thisInstance);
		},
		getSecondColMinWidth: function (container) {
			let maxWidth = 0,
				thisWidth;
			container.find('.js-list__row').each(function (i) {
				thisWidth = $(this).find('.js-list__field').first().width();
				if (i === 0) {
					maxWidth = thisWidth;
				} else {
					thisWidth > maxWidth ? (maxWidth = thisWidth) : maxWidth;
				}
			});
			return maxWidth;
		},
		setDomParams: function (container) {
			this.listColumnFirstWidth = container.find('.listViewEntriesDiv .listViewHeaders th').first().width();
			this.listColumnSecondWidth = this.getSecondColMinWidth(container);
			this.windowW = $(window).width();
			this.mainBody = container.closest('.mainBody');
			this.windowMinWidth = (15 / this.windowW) * 100;
			this.windowMaxWidth = 100 - this.minWidth;
			this.sideBlocks = container.find('.js-side-block');
			this.sideBlockLeft = this.sideBlocks.first();
			this.sideBlockRight = this.sideBlocks.last();
			this.list = container.find('.js-list-preview');
			this.preview = container.find('.js-detail-preview');
			this.rotatedText = container.find('.u-rotate-90');
			this.footerH = $('.js-footer').outerHeight();
			this.headerH = $('.js-header').outerHeight();
		},
		getDefaultSplitSizes: function () {
			let thWidth = ((this.listColumnFirstWidth + this.listColumnSecondWidth + 82) / this.windowW) * 100;
			return [thWidth, 100 - thWidth];
		},
		getSplitSizes() {
			const cachedParams = app.moduleCacheGet('userRelatedSplitSet');
			if (cachedParams !== undefined) {
				return cachedParams;
			} else {
				return this.getDefaultSplitSizes();
			}
		},
		registerListPreviewEvents() {
			app.showNewScrollbarTopBottom(this.content.find('.js-list-preview--scroll'));
			app.showNewScrollbarLeft(this.list);
			this.list.on('click', '.listViewEntries', () => {
				if (this.split.getSizes()[1] < 10) {
					const defaultGutterPosition = this.getDefaultSplitSizes();
					this.split.setSizes(defaultGutterPosition);
					this.preview.show();
					this.sideBlockRight.removeClass('d-block');
					app.moduleCacheSet('userRelatedSplitSet', defaultGutterPosition);
				}
			});
			if (!this.list.parents('.blockContent').length) {
				this.registerScrollEvent();
			}
		},
		registerScrollEvent() {
			this.gutter.addClass('js-fixed-scroll');
			let scrollContainer = App.Components.Scrollbar.page.element;
			let listOffsetTop = this.list.offset().top - this.headerH;
			let initialH = this.sideBlocks.height();
			let mainViewPortHeightCss = { height: this.mainBody.height() };
			let mainViewPortWidthCss = { width: this.mainBody.height() };
			let fixedElements = this.mainBody.find('.js-fixed-scroll');
			if (!this.mainBody.length) {
				this.mainBody = $(top.document).find('.mainBody');
				this.headerH = $(top.document).find('.js-header').outerHeight();
				scrollContainer = top.window.App.Components.Scrollbar.page.element;
				let iframe = $(top.document).find('.js-detail-preview');
				listOffsetTop = this.list.offset().top + iframe.offset().top - this.headerH + 1;
				mainViewPortHeightCss = { height: this.mainBody.height() };
				mainViewPortWidthCss = { width: this.mainBody.height() };
			}
			const onScroll = () => {
				if (scrollContainer.scrollTop() >= listOffsetTop) {
					fixedElements.css({ top: scrollContainer.scrollTop() - listOffsetTop });
					this.sideBlocks.css({ top: scrollContainer.scrollTop() - listOffsetTop + 56 });
					fixedElements.css(mainViewPortHeightCss);
					this.rotatedText.css(mainViewPortHeightCss);
					this.rotatedText.css(mainViewPortWidthCss);
				} else {
					fixedElements.css({ top: 'initial' });
					fixedElements.css({ height: initialH + scrollContainer.scrollTop() });
					this.rotatedText.css({
						width: initialH + scrollContainer.scrollTop(),
						height: initialH + scrollContainer.scrollTop(),
					});
				}
			};
			scrollContainer.on('scroll', onScroll);
		},
		/**
		 * Registers split's events.
		 * @param {jQuery} container - current container for reference.
		 * @param {Split} split - a split object.
		 */
		registerSplitEvents: function (container, split) {
			var rightSplitMaxWidth = (400 / this.windowW) * 100;
			var minWindowWidth = (23 / this.windowW) * 100;
			var maxWindowWidth = 100 - minWindowWidth;
			var listPreview = container.find('.js-detail-preview');
			this.gutter.on('dblclick', () => {
				let gutterRelatedMidPosition = app.moduleCacheGet('gutterRelatedMidPosition');
				if (isNaN(this.split.getSizes()[0])) {
					this.split.setSizes(gutterRelatedMidPosition);
				}
				if (split.getSizes()[0] < 7) {
					this.gutter.removeClass('js-gutter-corr-left');
					this.sideBlockLeft.removeClass('d-block');
					this.list.removeClass('u-hide-underneath');
					if (gutterRelatedMidPosition[0] > 11) {
						split.setSizes(gutterRelatedMidPosition);
					} else {
						split.setSizes(this.getDefaultSplitSizes());
					}
				} else if (split.getSizes()[1] < 20) {
					if (gutterRelatedMidPosition[1] > rightSplitMaxWidth + 1) {
						split.setSizes(gutterRelatedMidPosition);
					} else {
						split.setSizes(this.getDefaultSplitSizes());
					}
					this.gutter.removeClass('js-gutter-corr-right');
					this.sideBlockRight.removeClass('d-block');
					listPreview.show();
				} else if (split.getSizes()[0] > 7 && split.getSizes()[0] < 50) {
					split.setSizes([minWindowWidth, maxWindowWidth]);
					this.gutter.addClass('js-gutter-corr-left');
					this.sideBlockLeft.addClass('d-block');
					this.list.addClass('u-hide-underneath');
				} else if (split.getSizes()[1] > 10 && split.getSizes()[1] < 50) {
					split.setSizes([maxWindowWidth, minWindowWidth]);
					this.gutter.addClass('js-gutter-corr-right');
					this.sideBlockRight.addClass('d-block');
					listPreview.hide();
					//this.list.width(this.list.width() - 10);
				}
				app.moduleCacheSet('userRelatedSplitSet', split.getSizes());
			});
			this.sideBlockLeft.on('click', () => {
				let gutterRelatedMidPosition = app.moduleCacheGet('gutterRelatedMidPosition');
				if (gutterRelatedMidPosition[0] > 11) {
					split.setSizes(gutterRelatedMidPosition);
				} else {
					split.setSizes(this.getDefaultSplitSizes());
				}
				this.gutter.removeClass('js-gutter-corr-left');
				this.sideBlockLeft.removeClass('d-block');
				this.list.removeClass('u-hide-underneath');
				app.moduleCacheSet('userRelatedSplitSet', split.getSizes());
			});
			this.sideBlockRight.on('click', () => {
				let gutterRelatedMidPosition = app.moduleCacheGet('gutterRelatedMidPosition');
				if (gutterRelatedMidPosition[1] > rightSplitMaxWidth + 1) {
					split.setSizes(gutterRelatedMidPosition);
				} else {
					split.setSizes(this.getDefaultSplitSizes());
				}
				this.gutter.removeClass('js-gutter-corr-right');
				this.sideBlockRight.removeClass('d-block');
				listPreview.show();
				app.moduleCacheSet('userRelatedSplitSet', split.getSizes());
			});
		},
		registerSplit: function (container) {
			var rightSplitMaxWidth = (400 / this.windowW) * 100;
			const splitSizes = this.getSplitSizes();
			app.moduleCacheSet('gutterRelatedMidPosition', splitSizes);
			var split = Split([this.list[0], this.preview[0]], {
				sizes: splitSizes,
				minSize: 10,
				gutterSize: 24,
				snapOffset: 100,
				onDrag: () => {
					if (split.getSizes()[1] < rightSplitMaxWidth) {
						split.collapse(1);
					}
					if (split.getSizes()[0] < 7) {
						this.sideBlockLeft.addClass('d-block');
						this.list.addClass('u-hide-underneath');
					} else {
						this.gutter.removeClass('js-gutter-corr-left');
						this.sideBlockLeft.removeClass('d-block');
						this.list.removeClass('u-hide-underneath');
					}
					if (split.getSizes()[1] < 10) {
						this.sideBlockRight.addClass('d-block');
						this.preview.hide();
						this.list.width(this.list.width() - 10);
					} else {
						this.gutter.removeClass('js-gutter-corr-right');
						this.sideBlockRight.removeClass('d-block');
						this.preview.show();
					}
					if (split.getSizes()[0] > 10 && split.getSizes()[1] > rightSplitMaxWidth) {
						app.moduleCacheSet('gutterRelatedMidPosition', split.getSizes());
					}
					app.moduleCacheSet('userRelatedSplitSet', split.getSizes());
				},
			});
			this.gutter = container.find('.gutter');
			if (splitSizes[0] < 10) {
				this.gutter.addClass('js-gutter-corr-left');
				this.sideBlockLeft.addClass('d-block');
				this.list.addClass('u-hide-underneath');
			} else if (splitSizes[1] < rightSplitMaxWidth) {
				this.gutter.addClass('js-gutter-corr-right');
				this.sideBlockRight.addClass('d-block');
				this.preview.hide();
			}
			var mainWindowHeightCss = { height: $(window).height() - this.list.offset().top - this.footerH };
			if (!container.closest('.mainBody').length) {
				let mainBody = $(top.document).find('.mainBody').height();
				let iframe = $(top.document).find('.js-detail-preview');
				mainWindowHeightCss = { height: mainBody - this.list.offset().top - iframe.offset().top + 50 };
			}
			if (!this.list.parents('.blockContent').length) {
				this.gutter.css(mainWindowHeightCss);
				this.list.css(mainWindowHeightCss);
				this.sideBlocks.css(mainWindowHeightCss);
				this.rotatedText.css({
					width: this.sideBlockLeft.height(),
					height: this.sideBlockLeft.height(),
				});
			}
			this.registerSplitEvents(container, split);
			return split;
		},
		toggleSplit: function (container) {
			var commactHeight = container.closest('.commonActionsContainer').height();
			var splitsArray = [];
			this.split = this.registerSplit(container);
			splitsArray.push(this.split);
			$(window).on('resize', () => {
				if ($(window).width() < 993) {
					if (container.find('.gutter').length) {
						splitsArray[splitsArray.length - 1].destroy();
						this.sideBlockRight.removeClass('d-block');
						this.sideBlockLeft.removeClass('d-block');
					}
				} else {
					if (container.find('.gutter').length !== 1) {
						this.split = this.registerSplit(container);
						var gutter = container.find('.gutter');
						if (this.mainBody.scrollTop() >= this.list.offset().top + commactHeight) {
							gutter.addClass('gutterOnScroll');
							gutter.css('left', this.preview.offset().left - 8);
							gutter.on('mousedown', function () {
								$(this).on('mousemove', function (e) {
									$(this).css('left', this.preview.offset().left - 8);
								});
							});
						}
						splitsArray.push(this.split);
					}
					var currentSplit = splitsArray[splitsArray.length - 1];
					var minWidth = (15 / $(window).width()) * 100;
					var maxWidth = 100 - minWidth;
					if (typeof currentSplit === 'undefined') return;
					if (currentSplit.getSizes()[0] < minWidth + 5) {
						currentSplit.setSizes([minWidth, maxWidth]);
					} else if (currentSplit.getSizes()[1] < minWidth + 5) {
						currentSplit.setSizes([maxWidth, minWidth]);
					}
				}
			});
		},
		registerListScroll: function () {
			let container = $('.listViewEntriesDiv');
			if (this.relatedView !== 'ListPreview' && Quasar.plugins.Platform.is.desktop) {
				container.each((index, element) => {
					if (container.closest('.js-detail-widget-content').length) {
						element = container.closest('.js-detail-widget-content');
						element.each((i, el) => {
							App.Components.Scrollbar.xy($(el));
						});
					} else {
						App.Components.Scrollbar.xy($(element));
					}
				});
			}
		},
		getRecordsCount: function () {
			let aDeferred = $.Deferred(),
				recordCountVal = $('#recordsCount').val();
			if (recordCountVal != '') {
				aDeferred.resolve(recordCountVal);
			} else {
				let params = this.getCompleteParams();
				delete params.view;
				params.action = 'DetailAjax';
				params.mode = 'getRecordsCount';
				AppConnector.request(params).done(function (data) {
					$('#recordsCount').val(data['result']['count']);
					aDeferred.resolve(data['result']['count']);
				});
			}
			return aDeferred.promise();
		},
		noRecordSelectedAlert: function (text = 'JS_PLEASE_SELECT_ONE_RECORD') {
			return Vtiger_Helper_Js.showPnotify({ text: app.vtranslate(text) });
		},
		getCurrentCvId: function () {
			return $('#customFilter').find('option:selected').data('id');
		},
		checkListRecordSelected: function (minNumberOfRecords = 1) {
			let selectedIds = this.readSelectedIds();
			if (typeof selectedIds === 'object' && selectedIds.length < minNumberOfRecords) {
				return true;
			}
			return false;
		},
		readSelectedIds: function (decode) {
			let selectedIdsDataAttr = this.getCurrentCvId() + 'selectedIds',
				selectedIdsElementDataAttributes = $('#selectedIds').data(),
				selectedIds = [];
			if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes)) {
				this.writeSelectedIds(selectedIds);
			} else {
				selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
			}
			if (decode == true && typeof selectedIds == 'object') {
				return JSON.stringify(selectedIds);
			}
			return selectedIds;
		},
		writeSelectedIds: function (selectedIds) {
			if (!Array.isArray(selectedIds)) {
				selectedIds = [selectedIds];
			}
			$('#selectedIds').data(this.getCurrentCvId() + 'selectedIds', selectedIds);
		},
		readExcludedIds: function (decode) {
			let excludedIdsDataAttr = this.getCurrentCvId() + 'Excludedids',
				excludedIdsElementDataAttributes = $('#excludedIds').data(),
				excludedIds = [];
			if (!(excludedIdsDataAttr in excludedIdsElementDataAttributes)) {
				this.writeExcludedIds(excludedIds);
			} else {
				excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
			}
			if (decode == true && typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
			return excludedIds;
		},
		writeExcludedIds: function (excludedIds) {
			$('#excludedIds').data(this.getCurrentCvId() + 'Excludedids', excludedIds);
		},
		checkSelectAll: function () {
			let state = true;
			$('.relatedListViewEntriesCheckBox').each(function (index, element) {
				if ($(element).is(':checked')) {
					state = true;
				} else {
					state = false;
				}
			});
			$('#relatedListViewEntriesMainCheckBox').prop('checked', state);
			return state;
		},
		registerCheckBoxClickEvent: function () {
			const self = this;
			this.getRelatedContainer().on('click', '.relatedListViewEntriesCheckBox', function (e) {
				let selectedIds = self.readSelectedIds(),
					excludedIds = self.readExcludedIds(),
					elem = $(e.currentTarget);
				if (elem.is(':checked')) {
					elem.closest('tr').addClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.splice($.inArray(elem.val(), excludedIds), 1);
					} else if ($.inArray(elem.val(), selectedIds) == -1) {
						selectedIds.push(elem.val());
					}
				} else {
					elem.closest('tr').removeClass('highlightBackgroundColor');
					if (selectedIds == 'all') {
						excludedIds.push(elem.val());
						selectedIds = 'all';
					} else {
						selectedIds.splice($.inArray(elem.val(), selectedIds), 1);
					}
				}
				self.checkSelectAll();
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
			});
		},
		registerMainCheckBoxClickEvent: function () {
			const self = this;
			this.getRelatedContainer().on('click', '#relatedListViewEntriesMainCheckBox', function () {
				let selectedIds = self.readSelectedIds(),
					excludedIds = self.readExcludedIds();
				if ($('#relatedListViewEntriesMainCheckBox').is(':checked')) {
					let recordCountObj = self.getRecordsCount();
					recordCountObj.done(function (data) {
						$('#totalRecordsCount').text(data);
						if ($('#deSelectAllMsgDiv').css('display') == 'none') {
							$('#selectAllMsgDiv').show();
						}
					});
					$('.relatedListViewEntriesCheckBox').each(function (index, element) {
						$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
						if (selectedIds == 'all' && $.inArray($(element).val(), excludedIds) != -1) {
							excludedIds.splice($.inArray($(element).val(), excludedIds), 1);
						} else if ($.inArray($(element).val(), selectedIds) == -1) {
							selectedIds.push($(element).val());
						}
					});
				} else {
					$('#selectAllMsgDiv').hide();
					$('.relatedListViewEntriesCheckBox').each(function (index, element) {
						$(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						if (selectedIds == 'all') {
							excludedIds.push($(element).val());
							selectedIds = 'all';
						} else {
							selectedIds.splice($.inArray($(element).val(), selectedIds), 1);
						}
					});
				}
				self.writeSelectedIds(selectedIds);
				self.writeExcludedIds(excludedIds);
			});
		},
		registerSelectAllClickEvent: function () {
			const self = this;
			self.getRelatedContainer().on('click', '#selectAllMsg', function () {
				$('#selectAllMsgDiv').hide();
				$('#deSelectAllMsgDiv').show();
				$('#relatedListViewEntriesMainCheckBox').prop('checked', true);
				$('.relatedListViewEntriesCheckBox').each(function (index, element) {
					$(this).prop('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				self.writeSelectedIds('all');
			});
		},
		registerDeselectAllClickEvent: function () {
			const self = this;
			self.getRelatedContainer().on('click', '#deSelectAllMsg', function () {
				$('#deSelectAllMsgDiv').hide();
				$('#relatedListViewEntriesMainCheckBox').prop('checked', false);
				$('.relatedListViewEntriesCheckBox').each(function (index, element) {
					$(this).prop('checked', false).closest('tr').removeClass('highlightBackgroundColor');
				});
				self.writeSelectedIds([]);
				self.writeExcludedIds([]);
			});
		},
		/**
		 * Register quick edit save event description.
		 */
		registerQuickEditSaveEvent() {
			app.event.on('QuickEdit.AfterSaveFinal', (e, data, instance, element) => {
				if (this.moduleName === instance.data('moduleName')) {
					if (element.closest('.js-detail-widget').length) {
						Vtiger_Detail_Js.getInstance().postSummaryWidgetAddRecord(data, element);
					} else {
						this.loadRelatedList(data.result);
					}
				}
			});
		},
		registerRelatedEvents: function () {
			this.registerUnreviewedCountEvent();
			this.registerChangeEntityStateEvent();
			this.registerPaginationEvents();
			this.registerListEvents();
			this.registerPostLoadEvents();
			this.registerSummationEvent();
			this.registerCheckBoxClickEvent();
			this.registerMainCheckBoxClickEvent();
			this.registerSelectAllClickEvent();
			this.registerDeselectAllClickEvent();
			this.registerQuickEditSaveEvent();
			YetiForce_ListSearch_Js.registerSearch(this.content, (data) => {
				this.loadRelatedList(data);
			});
		},
	}
);
