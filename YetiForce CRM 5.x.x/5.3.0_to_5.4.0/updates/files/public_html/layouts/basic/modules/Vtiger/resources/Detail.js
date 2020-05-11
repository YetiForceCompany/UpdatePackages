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
	'Vtiger_Detail_Js',
	{
		detailInstance: false,
		getInstance: function () {
			if (Vtiger_Detail_Js.detailInstance == false) {
				let moduleClassName = app.getModuleName() + '_' + app.getViewName() + '_Js',
					instance;
				if (typeof window[moduleClassName] !== 'undefined') {
					instance = new window[moduleClassName]();
				} else {
					instance = new Vtiger_Detail_Js();
				}
				Vtiger_Detail_Js.detailInstance = instance;
			}
			return Vtiger_Detail_Js.detailInstance;
		},
		/*
		 * function to trigger Detail view actions
		 * @params: Action url , callback function.
		 */
		triggerDetailViewAction: function (detailActionUrl, callBackFunction) {
			var detailInstance = Vtiger_Detail_Js.getInstance();
			var selectedIds = [];
			selectedIds.push(detailInstance.getRecordId());
			var postData = {
				selected_ids: JSON.stringify(selectedIds),
			};
			var actionParams = {
				type: 'POST',
				url: detailActionUrl,
				dataType: 'html',
				data: postData,
			};

			AppConnector.request(actionParams)
				.done(function (data) {
					if (data) {
						app.showModalWindow(data, { 'text-align': 'left' });
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					}
				})
				.fail(function (error, err) {});
		},
		/*
		 * function to trigger send Sms
		 * @params: send sms url , module name.
		 */
		triggerSendSms: function (detailActionUrl, module) {
			Vtiger_Detail_Js.triggerDetailViewAction(detailActionUrl);
		},
		triggerTransferOwnership: function (massActionUrl) {
			var thisInstance = this;
			thisInstance.getRelatedModulesContainer = false;
			var actionParams = {
				type: 'POST',
				url: massActionUrl,
				dataType: 'html',
				data: {},
			};
			AppConnector.request(actionParams).done(function (data) {
				if (data) {
					var callback = function (data) {
						var params = app.validationEngineOptions;
						params.onValidationComplete = function (form, valid) {
							if (valid) {
								if (form.attr('name') == 'changeOwner') {
									thisInstance.transferOwnershipSave(form);
								}
							}
							return false;
						};
						jQuery('#changeOwner').validationEngine(app.validationEngineOptions);
					};
					app.showModalWindow(data, function (data) {
						var selectElement = thisInstance.getRelatedModuleContainer();
						App.Fields.Picklist.changeSelectElementView(selectElement, 'select2');
						if (typeof callback == 'function') {
							callback(data);
						}
					});
				}
			});
		},
		transferOwnershipSave: function (form) {
			var transferOwner = jQuery('#transferOwnerId').val();
			var relatedModules = jQuery('#related_modules').val();
			var recordId = jQuery('#recordId').val();
			var params = {
				module: app.getModuleName(),
				action: 'TransferOwnership',
				record: recordId,
				transferOwnerId: transferOwner,
				related_modules: relatedModules,
			};
			AppConnector.request(params).done(function (data) {
				if (data.success) {
					app.hideModalWindow();
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
						type: 'info',
					};
					var oldvalue = jQuery('.assigned_user_id').val();
					var element = jQuery('.assigned_user_id ');

					element.find('option[value="' + oldvalue + '"]').removeAttr('selected');
					element.find('option[value="' + transferOwner + '"]').attr('selected', 'selected');
					element.trigger('liszt:updated');
					var Fieldname = element.find('option[value="' + transferOwner + '"]').data('picklistvalue');
					element
						.closest('.row-fluid')
						.find('.value')
						.html(
							'<a href="index.php?module=Users&amp;parent=Settings&amp;view=Detail&amp;record=' +
								transferOwner +
								'">' +
								Fieldname +
								'</a>'
						);

					Vtiger_Helper_Js.showPnotify(params);
				}
			});
		},
		/*
		 * Function to get the related module container
		 */
		getRelatedModuleContainer: function () {
			if (this.getRelatedModulesContainer == false) {
				this.getRelatedModulesContainer = jQuery('#related_modules');
			}
			return this.getRelatedModulesContainer;
		},
		reloadRelatedList: function () {
			var detailInstance = Vtiger_Detail_Js.getInstance();
			var params = {};
			if (jQuery('[name="currentPageNum"]').length > 0) {
				params.page = jQuery('[name="currentPageNum"]').val();
			}
			detailInstance.loadRelatedList(params);
		},
		runRecordChanger: function (id) {
			AppConnector.request({
				module: app.getModuleName(),
				record: app.getRecordId(),
				action: 'Save',
				mode: 'recordChanger',
				id: id,
			})
				.done(function () {
					window.location.reload();
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Vtiger_Helper_Js.showPnotify({
						type: 'error',
						text: textStatus,
					});
				});
		},
		showWorkflowTriggerView: function (instance) {
			$(instance).popover('hide');
			const detailInstance = Vtiger_Detail_Js.getInstance(),
				callback = function (data) {
					let treeInstance = data.find('#treeWorkflowContents');
					treeInstance.jstree({
						core: {
							data: JSON.parse(data.find('.js-tree-workflow-data').val()),
							themes: {
								name: 'proton',
								responsive: true,
								icons: false,
							},
						},
						checkbox: {
							three_state: false,
						},
						plugins: ['search', 'category'],
					});
					data.find('[type="submit"]').on('click', function () {
						let tasks = {};
						let selected = treeInstance.jstree('getCategory', true);
						$.each(selected, function (index, treeElement) {
							if (treeElement.attr === 'record') {
								tasks[treeElement.record_id] = [];
							}
						});
						$.each(selected, function (index, treeElement) {
							if (tasks[treeElement.parent] !== undefined && treeElement.attr === 'task') {
								tasks[treeElement.parent].push(treeElement.record_id);
							}
						});
						if (Object.keys(tasks).length === 0) {
							Vtiger_Helper_Js.showPnotify({
								title: app.vtranslate('JS_INFORMATION'),
								text: app.vtranslate('JS_NOT_SELECTED_WORKFLOW_TRIGGER'),
								type: 'error',
							});
						} else {
							Vtiger_Helper_Js.showPnotify({
								title: app.vtranslate('JS_MESSAGE'),
								text: app.vtranslate('JS_STARTED_PERFORM_WORKFLOW'),
								type: 'info',
							});
							AppConnector.request({
								module: app.getModuleName(),
								action: 'Workflow',
								mode: 'execute',
								user: data.find('[name="user"]').val(),
								record: detailInstance.getRecordId(),
								tasks: JSON.stringify(tasks),
							})
								.done(function () {
									Vtiger_Helper_Js.showPnotify({
										title: app.vtranslate('JS_MESSAGE'),
										text: app.vtranslate('JS_COMPLETED_PERFORM_WORKFLOW'),
										type: 'success',
									});
									app.hideModalWindow();
									detailInstance.loadWidgets();
								})
								.fail(function () {
									Vtiger_Helper_Js.showPnotify({
										title: app.vtranslate('JS_ERROR'),
										text: app.vtranslate('JS_ERROR_DURING_TRIGGER_OF_WORKFLOW'),
										type: 'error',
									});
									app.hideModalWindow();
								});
						}
					});
				};
			AppConnector.request({
				module: app.getModuleName(),
				view: 'WorkflowTrigger',
				record: detailInstance.getRecordId(),
			}).done(function (data) {
				if (data) {
					app.showModalWindow(data, '', callback);
				}
			});
		},
	},
	{
		targetPicklistChange: false,
		targetPicklist: false,
		detailViewContentHolder: false,
		detailViewForm: false,
		detailViewDetailsTabLabel: 'LBL_RECORD_DETAILS',
		detailViewSummaryTabLabel: 'LBL_RECORD_SUMMARY',
		detailViewRecentCommentsTabLabel: 'ModComments',
		detailViewRecentActivitiesTabLabel: 'Activities',
		detailViewRecentUpdatesTabLabel: 'LBL_UPDATES',
		detailViewRecentDocumentsTabLabel: 'Documents',
		fieldUpdatedEvent: 'Vtiger.Field.Updated',
		//Filels list on updation of which we need to upate the detailview header
		updatedFields: ['company', 'designation', 'title'],
		//Event that will triggered before saving the ajax edit of fields
		fieldPreSave: 'Vtiger.Field.PreSave',
		tempData: [],
		referenceFieldNames: {
			Calendar: {
				Accounts: 'link',
				Leads: 'link',
				Vendors: 'link',
				OSSEmployees: 'link',
				Contacts: 'linkextend',
				Campaigns: 'process',
				HelpDesk: 'process',
				Projects: 'process',
				ServiceContracts: 'process',
			},
			OutsourcedProducts: {
				Leads: 'parent_id',
				Accounts: 'parent_id',
				Contacts: 'parent_id',
			},
			Assets: {
				Accounts: 'parent_id',
				Contacts: 'parent_id',
			},
			OSSOutsourcedServices: {
				Leads: 'parent_id',
				Accounts: 'parent_id',
				Contacts: 'parent_id',
			},
			OSSSoldServices: {
				Accounts: 'parent_id',
				Contacts: 'parent_id',
			},
		},
		//constructor
		init: function () {},
		loadWidgetsEvents: function () {
			const thisInstance = this;
			app.event.on('DetailView.Widget.AfterLoad', function (e, widgetContent, relatedModuleName, instance) {
				if (relatedModuleName === 'Calendar') {
					thisInstance.reloadWidgetActivitesStats(widgetContent.closest('.activityWidgetContainer'));
				}
				if (relatedModuleName === 'ModComments') {
					thisInstance.registerCommentEventsInDetail(widgetContent.closest('.updatesWidgetContainer'));
				}
				if (widgetContent.find('[name="relatedModule"]').length) {
					thisInstance.registerShowSummary(widgetContent);
				}
				if (relatedModuleName === 'OSSMailView') {
					Vtiger_Index_Js.registerMailButtons(widgetContent);
					widgetContent.find('.showMailModal').on('click', function (e) {
						let progressIndicatorElement = jQuery.progressIndicator();
						app.showModalWindow('', $(e.currentTarget).data('url') + '&noloadlibs=1', function (data) {
							Vtiger_Index_Js.registerMailButtons(data);
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
					});
				}
				thisInstance.registerEmailEvents(widgetContent);
				if (relatedModuleName === 'DetailView') {
					thisInstance.registerBlockStatusCheckOnLoad();
				}
			});
		},
		loadWidgets: function () {
			var thisInstance = this;
			var widgetList = jQuery('[class^="widgetContainer_"]');
			widgetList.each(function (index, widgetContainerELement) {
				var widgetContainer = jQuery(widgetContainerELement);
				thisInstance.loadWidget(widgetContainer);
			});
			thisInstance.registerRelatedModulesRecordCount();
		},
		loadWidget: function (widgetContainer, params) {
			const thisInstance = this,
				contentContainer = $('.js-detail-widget-content', widgetContainer);
			let relatedModuleName;
			this.registerFilterForAddingModuleRelatedRecordFromSummaryWidget(widgetContainer);
			if (widgetContainer.find('[name="relatedModule"]').length) {
				relatedModuleName = widgetContainer.find('[name="relatedModule"]').val();
			} else {
				relatedModuleName = widgetContainer.data('name');
			}
			if (params === undefined) {
				let urlParams = widgetContainer.data('url');
				if (urlParams == undefined) {
					return;
				}
				let queryParameters = urlParams.split('&'),
					keyValueMap = {},
					index;
				for (index = 0; index < queryParameters.length; index++) {
					let queryParamComponents = queryParameters[index].split('=');
					keyValueMap[queryParamComponents[0]] = queryParamComponents[1];
				}
				params = keyValueMap;
			}
			let aDeferred = $.Deferred();
			contentContainer.progressIndicator({});
			AppConnector.request({
				type: 'POST',
				async: false,
				dataType: 'html',
				data: params,
			})
				.done(function (data) {
					contentContainer.progressIndicator({ mode: 'hide' });
					contentContainer.html(data);
					App.Fields.Picklist.showSelect2ElementView(widgetContainer.find('.select2'));
					app.registerModal(contentContainer);
					if (relatedModuleName) {
						let relatedController = Vtiger_RelatedList_Js.getInstance(
							thisInstance.getRecordId(),
							app.getModuleName(),
							thisInstance.getSelectedTab(),
							relatedModuleName
						);
						relatedController.setRelatedContainer(contentContainer);
						relatedController.registerRelatedEvents();
						thisInstance.widgetRelatedRecordView(widgetContainer, true);
					}
					app.event.trigger('DetailView.Widget.AfterLoad', contentContainer, relatedModuleName, thisInstance);
					aDeferred.resolve(params);
				})
				.fail(function () {
					contentContainer.progressIndicator({ mode: 'hide' });
					aDeferred.reject();
				});
			return aDeferred.promise();
		},
		widgetRelatedRecordView: function (container, load) {
			var cacheKey = this.getRecordId() + '_' + container.data('id');
			var relatedRecordCacheID = app.moduleCacheGet(cacheKey);
			if (relatedRecordCacheID !== null) {
				var newActive = container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']");
				if (newActive.length) {
					container.find('.js-carousel-item.active').removeClass('active');
					container.find(".js-carousel-item[data-id = '" + relatedRecordCacheID + "']").addClass('active');
				}
			}
			var controlBox = container.find('.control-widget');
			var prev = controlBox.find('.prev');
			var next = controlBox.find('.next');
			var active = container.find('.js-carousel-item.active');
			if (container.find('.js-carousel-item').length <= 1 || !active.next().length) {
				next.addClass('disabled');
			} else {
				next.removeClass('disabled');
			}
			if (container.find('.js-carousel-item').length <= 1 || !active.prev().length) {
				prev.addClass('disabled');
			} else {
				prev.removeClass('disabled');
			}
			if (load) {
				next.on('click', function () {
					if ($(this).hasClass('disabled')) {
						return;
					}
					var active = container.find('.js-carousel-item.active');
					active.removeClass('active');
					var nextElement = active.next();
					nextElement.addClass('active');
					if (!nextElement.next().length) {
						next.addClass('disabled');
					}
					if (active.prev()) {
						prev.removeClass('disabled');
					}
					app.moduleCacheSet(cacheKey, nextElement.data('id'));
				});
				prev.on('click', function () {
					if ($(this).hasClass('disabled')) {
						return;
					}
					var active = container.find('.js-carousel-item.active');
					active.removeClass('active');
					var prevElement = active.prev();
					prevElement.addClass('active');
					if (!prevElement.prev().length) {
						prev.addClass('disabled');
					}
					if (active.next()) {
						next.removeClass('disabled');
					}
					app.moduleCacheSet(cacheKey, prevElement.data('id'));
				});
			}
		},

		loadContents: function (url, data) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();

			var detailContentsHolder = this.getContentHolder();
			var params = url;
			if (typeof data !== 'undefined') {
				params = {};
				params.url = url;
				params.data = data;
			}
			AppConnector.requestPjax(params).done(function (responseData) {
				detailContentsHolder.html(responseData);
				responseData = detailContentsHolder.html();
				//thisInstance.triggerDisplayTypeEvent();
				thisInstance.registerBlockStatusCheckOnLoad();
				//Make select box more usability
				App.Fields.Picklist.changeSelectElementView(detailContentsHolder);
				//Attach date picker event to date fields
				App.Fields.Date.register(detailContentsHolder);
				thisInstance.getForm().validationEngine();
				app.event.trigger('DetailView.LoadContents.AfterLoad', responseData);
				aDeferred.resolve(responseData);
			});
			return aDeferred.promise();
		},
		getUpdateFieldsArray: function () {
			return this.updatedFields;
		},
		/**
		 * Function to return related tab.
		 * @return : jQuery Object.
		 */
		getTabByLabel: function (tabLabel) {
			var tabs = this.getTabs();
			var targetTab = false;
			tabs.each(function (index, element) {
				var tab = jQuery(element);
				var labelKey = tab.data('labelKey');
				if (labelKey == tabLabel) {
					targetTab = tab;
					return false;
				}
			});
			return targetTab;
		},
		getTabByModule: function (moduleName, relationId = '') {
			var tabs = this.getTabs();
			var targetTab = false;
			tabs.each(function (index, element) {
				var tab = jQuery(element);
				if (
					tab.data('reference') == moduleName &&
					(!relationId || (relationId && relationId == tab.data('relation-id')))
				) {
					targetTab = tab;
					return false;
				}
			});
			return targetTab;
		},
		selectModuleTab: function () {
			var relatedTabContainer = this.getTabContainer();
			var moduleTab = relatedTabContainer.find('li.module-tab');
			this.deSelectAllrelatedTabs();
			this.markTabAsSelected(moduleTab);
		},
		deSelectAllrelatedTabs: function () {
			this.getTabs().removeClass('active');
		},
		markTabAsSelected: function (tabElement) {
			tabElement.addClass('active');
			$(
				'.related .dropdown [data-reference="' +
					tabElement.data('reference') +
					'"][data-relation-id="' +
					tabElement.data('relation-id') +
					'"]'
			).addClass('active');
		},
		reloadTabContent: function () {
			this.getSelectedTab().trigger('click');
		},
		getSelectedTab: function () {
			var tabContainer = this.getTabContainer();
			return tabContainer.find('.js-detail-tab.active:not(.d-none)');
		},
		getTabContainer: function () {
			return jQuery('div.related');
		},
		getTabs: function () {
			var topTabs = this.getTabContainer().find('li.baseLink:not(.d-none)');
			var dropdownMenuTabs = this.getTabContainer().find('li:not(.baseLink)');
			dropdownMenuTabs.each(function (n, e) {
				var currentTarget = jQuery(this);
				var iteration = currentTarget.data('iteration');
				var className = currentTarget.hasClass('mainNav') ? 'mainNav' : 'relatedNav';
				if (
					iteration != undefined &&
					topTabs.filter('.' + className + '[data-iteration="' + iteration + '"]').length < 1
				) {
					topTabs.push(currentTarget.get(0));
				}
			});
			return topTabs;
		},
		getContentHolder: function () {
			if (this.detailViewContentHolder == false) {
				this.detailViewContentHolder = jQuery('div.details div.contents');
			}
			return this.detailViewContentHolder;
		},
		/**
		 * Function which will give the detail view form
		 * @return : jQuery element
		 */
		getForm: function () {
			if (this.detailViewForm == false) {
				this.detailViewForm = jQuery('#detailView');
			}
			return this.detailViewForm;
		},
		getRecordId: function () {
			return app.getRecordId();
		},
		getRelatedModuleName: function () {
			if (jQuery('.relatedModuleName', this.getContentHolder()).length == 1) {
				return jQuery('.relatedModuleName', this.getContentHolder()).val();
			}
		},
		saveFieldValues: function (fieldDetailList) {
			var aDeferred = jQuery.Deferred();

			var recordId = this.getRecordId();

			var data = {};
			if (typeof fieldDetailList !== 'undefined') {
				data = fieldDetailList;
			}
			data['record'] = recordId;
			data['module'] = app.getModuleName();
			data['action'] = 'SaveAjax';

			const params = {};
			params.data = data;
			params.async = false;
			params.dataType = 'json';
			this.preSaveValidation(JSON.parse(JSON.stringify(params)), this.getForm()).then((response) => {
				if (response === true) {
					AppConnector.request(params)
						.done(function (responseData) {
							aDeferred.resolve(responseData);
						})
						.fail((jqXHR, textStatus, errorThrown) => {
							aDeferred.reject(jqXHR, textStatus, errorThrown);
							app.errorLog(jqXHR, textStatus, errorThrown);
						});
				} else {
					aDeferred.resolve({ success: false });
				}
			});

			return aDeferred.promise();
		},
		preSaveValidation: function (params, form) {
			const aDeferred = $.Deferred();
			if (form.find('#preSaveValidation').val()) {
				document.progressLoader = $.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'html',
					blockInfo: {
						enabled: true,
					},
				});
				params.data.mode = 'preSaveValidation';
				AppConnector.request(params)
					.done((data) => {
						document.progressLoader.progressIndicator({ mode: 'hide' });
						let response = data.result;
						for (let i = 0; i < response.length; i++) {
							if (response[i].result !== true) {
								Vtiger_Helper_Js.showPnotify(
									response[i].message ? response[i].message : app.vtranslate('JS_ERROR')
								);
							}
						}
						if (data.result.length <= 0) {
							aDeferred.resolve(true);
						} else {
							aDeferred.resolve(false);
						}
					})
					.fail((textStatus, errorThrown) => {
						document.progressLoader.progressIndicator({ mode: 'hide' });
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
						app.errorLog(textStatus, errorThrown);
						aDeferred.resolve(false);
					});
			} else {
				aDeferred.resolve(true);
			}

			return aDeferred.promise();
		},
		getRelatedListCurrentPageNum: function () {
			return jQuery('input[name="currentPageNum"]', this.getContentHolder()).val();
		},
		/**
		 * function to hide comment block.
		 */
		hideCommentBlock: function () {
			$('.js-add-comment-block', $('.js-comments-body', this.getContentHolder())).hide();
		},

		/**
		 * function to show comment block.
		 */
		showCommentBlock: function () {
			$('.js-add-comment-block', $('.js-comments-body', this.getContentHolder())).show();
		},

		/**
		 * function to get the Comment thread for the given parent.
		 * params: Url to get the Comment thread
		 */
		getCommentThread: function (url) {
			var aDeferred = jQuery.Deferred();
			AppConnector.request(url)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {});
			return aDeferred.promise();
		},
		/**
		 * Function to save comment
		 */
		saveCommentAjax: function (
			element,
			commentMode,
			commentContentValue,
			editCommentReason,
			commentId,
			parentCommentId,
			aDeferred
		) {
			var thisInstance = this;
			var progressIndicatorElement = jQuery.progressIndicator({});
			var commentInfoBlock = element.closest('.js-comment-single');
			var relatedTo = commentInfoBlock.find('.related_to').val();
			if (!relatedTo) {
				relatedTo = thisInstance.getRecordId();
			}
			let postData = {
				commentcontent: commentContentValue,
				related_to: relatedTo,
				module: 'ModComments',
			};

			if (commentMode == 'edit') {
				postData['record'] = commentId;
				postData['reasontoedit'] = editCommentReason;
				postData['parent_comments'] = parentCommentId;
				postData['mode'] = 'edit';
				postData['action'] = 'Save';
			} else if (commentMode == 'add') {
				postData['parent_comments'] = commentId;
				postData['action'] = 'SaveAjax';
			}
			AppConnector.request(postData)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (commentMode == 'add') {
						thisInstance.addRelationBetweenRecords(
							'ModComments',
							data.result.id,
							thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel)
						);
					}
					app.event.trigger('DetailView.SaveComment.AfterAjax', commentInfoBlock, postData, data);
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					element.removeAttr('disabled');
					aDeferred.reject(textStatus, errorThrown);
				});
		},
		saveComment: function (e) {
			let aDeferred = jQuery.Deferred(),
				currentTarget = jQuery(e.currentTarget),
				commentMode = currentTarget.data('mode'),
				closestCommentBlock = currentTarget.closest('.js-add-comment-block'),
				commentContent = closestCommentBlock.find('.js-comment-content'),
				commentContentValue = commentContent.html(),
				errorMsg,
				editCommentReason;
			if ('' === commentContentValue) {
				errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY');
				commentContent.validationEngine('showPrompt', errorMsg, 'error', 'bottomLeft', true);
				aDeferred.reject(errorMsg);
				return aDeferred.promise();
			}
			if ('edit' === commentMode) {
				editCommentReason = closestCommentBlock.find('[name="reasonToEdit"]').val();
			}
			let element = jQuery(e.currentTarget),
				commentInfoHeader = closestCommentBlock.closest('.js-comment-details').find('.js-comment-info-header'),
				commentId = commentInfoHeader.data('commentid'),
				parentCommentId = commentInfoHeader.data('parentcommentid');
			this.saveCommentAjax(
				element,
				commentMode,
				commentContentValue,
				editCommentReason,
				commentId,
				parentCommentId,
				aDeferred
			);
			return aDeferred.promise();
		},
		/**
		 * function to return the UI of the comment.
		 * return html
		 */
		getCommentUI: function (commentId) {
			var aDeferred = jQuery.Deferred();
			var postData = {
				view: 'DetailAjax',
				module: 'ModComments',
				record: commentId,
			};
			AppConnector.request(postData)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {});
			return aDeferred.promise();
		},
		/**
		 * function to return cloned add comment block
		 * return jQuery Obj.
		 */
		getCommentBlock: function () {
			let clonedCommentBlock = jQuery('.basicAddCommentBlock', this.getContentHolder())
				.clone(true, true)
				.removeClass('basicAddCommentBlock d-none')
				.addClass('js-add-comment-block');
			clonedCommentBlock
				.find('.commentcontenthidden')
				.removeClass('commentcontenthidden')
				.addClass('js-comment-content');
			return clonedCommentBlock;
		},
		/**
		 * function to return cloned edit comment block
		 * return jQuery Obj.
		 */
		getEditCommentBlock: function () {
			let clonedCommentBlock = jQuery('.basicEditCommentBlock', this.getContentHolder())
				.clone(true, true)
				.removeClass('basicEditCommentBlock d-none')
				.addClass('js-add-comment-block');
			clonedCommentBlock
				.find('.commentcontenthidden')
				.removeClass('commentcontenthidden')
				.addClass('js-comment-content');
			new App.Fields.Text.Completions(clonedCommentBlock.find('.js-completions'), { emojiPanel: false });
			return clonedCommentBlock;
		},
		/*
		 * Function to register the submit event for Send Sms
		 */
		registerSendSmsSubmitEvent: function () {
			var thisInstance = this;
			jQuery('body').on('submit', '#massSave', function (e) {
				var form = jQuery(e.currentTarget);
				var smsTextLength = form.find('#message').html().length;
				if (smsTextLength > 160) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED'),
						type: 'error',
					};
					Vtiger_Helper_Js.showPnotify(params);
					return false;
				}
				var submitButton = form.find(':submit');
				submitButton.attr('disabled', 'disabled');
				thisInstance.SendSmsSave(form);
				e.preventDefault();
			});
		},
		/*
		 * Function to Save and sending the Sms and hide the modal window of send sms
		 */
		SendSmsSave: function (form) {
			var progressInstance = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
				},
			});
			var SendSmsUrl = form.serializeFormData();
			AppConnector.request(SendSmsUrl)
				.done(function (data) {
					app.hideModalWindow();
					progressInstance.progressIndicator({
						mode: 'hide',
					});
				})
				.fail(function (error, err) {});
		},
		/**
		 * Function which will register events to update the record name in the detail view when any of
		 * the name field is changed
		 */
		registerNameAjaxEditEvent: function () {
			var thisInstance = this;
			var detailContentsHolder = thisInstance.getContentHolder();
			detailContentsHolder.on(thisInstance.fieldUpdatedEvent, '.nameField', function (e, params) {
				var form = thisInstance.getForm();
				var nameFields = form.data('nameFields');
				var recordLabel = '';
				for (var index in nameFields) {
					if (index != 0) {
						recordLabel += ' ';
					}

					var nameFieldName = nameFields[index];
					recordLabel += form.find('[name="' + nameFieldName + '"]').val();
				}
				var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
				recordLabelElement.text(recordLabel);
			});
		},
		updateHeaderNameFields: function () {
			var thisInstance = this;
			var detailContentsHolder = thisInstance.getContentHolder();
			var form = thisInstance.getForm();
			var nameFields = form.data('nameFields');
			var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.recordLabel');
			var title = '';
			for (var index in nameFields) {
				var nameFieldName = nameFields[index];
				var nameField = form.find('[name="' + nameFieldName + '"]');
				if (nameField.length > 0) {
					var recordLabel = nameField.val();
					title += recordLabel + ' ';
					recordLabelElement.find('[class="' + nameFieldName + '"]').text(recordLabel);
				}
			}
			var salutatioField = recordLabelElement.find('.salutation');
			if (salutatioField.length > 0) {
				var salutatioValue = salutatioField.text();
				title = salutatioValue + title;
			}
			recordLabelElement.attr('title', title);
		},
		registerAjaxEditEvent: function () {
			var thisInstance = this;
			var detailContentsHolder = thisInstance.getContentHolder();
			detailContentsHolder.on(thisInstance.fieldUpdatedEvent, 'input,select,textarea', function (e) {
				thisInstance.updateHeaderValues(jQuery(e.currentTarget));
			});
		},
		updateHeaderValues: function (currentElement) {
			var thisInstance = this;
			if (currentElement.hasClass('nameField')) {
				thisInstance.updateHeaderNameFields();
				return true;
			}

			var name = currentElement.attr('name');
			var updatedFields = this.getUpdateFieldsArray();
			var detailContentsHolder = thisInstance.getContentHolder();
			if (jQuery.inArray(name, updatedFields) != '-1') {
				var recordLabel = currentElement.val();
				var recordLabelElement = detailContentsHolder.closest('.contentsDiv').find('.' + name + '_label');
				recordLabelElement.text(recordLabel);
			}
		},
		/*
		 * Function to register the click event of email field
		 */
		registerEmailFieldClickEvent: function () {
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.emailField', function (e) {
				e.stopPropagation();
			});
		},
		/*
		 * Function to register the click event of phone field
		 */
		registerPhoneFieldClickEvent: function () {
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.phoneField', function (e) {
				e.stopPropagation();
			});
		},
		/*
		 * Function to register the click event of url field
		 */
		registerUrlFieldClickEvent: function () {
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.urlField', function (e) {
				e.stopPropagation();
			});
		},
		/**
		 * Function to register event for related list row click
		 */
		registerRelatedRowClickEvent: function () {
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.listViewEntries', function (e) {
				var targetElement = jQuery(e.target, jQuery(e.currentTarget));
				if (targetElement.is('td:first-child') && targetElement.children('input[type="checkbox"]').length > 0)
					return;
				if (jQuery(e.target).is('input[type="checkbox"]')) return;
				var elem = jQuery(e.currentTarget);
				var recordUrl = elem.data('recordurl');
				if (typeof recordUrl !== 'undefined') {
					window.location.href = recordUrl;
				}
			});
		},
		loadRelatedList: function (params) {
			var aDeferred = jQuery.Deferred();
			if (params == undefined) {
				params = {};
			}
			var relatedListInstance = Vtiger_RelatedList_Js.getInstance(
				this.getRecordId(),
				app.getModuleName(),
				this.getSelectedTab(),
				this.getRelatedModuleName()
			);
			relatedListInstance.loadRelatedList(params).done(
				function (data) {
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
			);
			return aDeferred.promise();
		},
		/**
		 * Function to register Event for Sorting
		 */
		registerEventForRelatedList: function () {
			var thisInstance = this;
			var detailContentsHolder = this.getContentHolder();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			if (relatedModuleName) {
				var relatedController = Vtiger_RelatedList_Js.getInstance(
					thisInstance.getRecordId(),
					app.getModuleName(),
					thisInstance.getSelectedTab(),
					relatedModuleName
				);
				relatedController.setRelatedContainer(detailContentsHolder);
				relatedController.registerRelatedEvents();
			}
			detailContentsHolder.find('.detailViewBlockLink').each(function (n, block) {
				block = $(block);
				var blockContent = block.find('.blockContent');
				if (blockContent.is(':visible')) {
					AppConnector.request({
						type: 'GET',
						dataType: 'html',
						data: {},
						url: block.data('url'),
					}).done(function (response) {
						blockContent.html(response);
						var relatedController = Vtiger_RelatedList_Js.getInstance(
							thisInstance.getRecordId(),
							app.getModuleName(),
							thisInstance.getSelectedTab(),
							block.data('reference')
						);
						relatedController.setRelatedContainer(blockContent);
						relatedController.registerRelatedEvents();
					});
				}
			});
			detailContentsHolder.find('.detailViewBlockLink .blockHeader').on('click', function (e) {
				const target = $(e.target);
				if (
					target.is('input') ||
					target.is('button') ||
					target.parents().is('button') ||
					target.hasClass('js-stop-propagation') ||
					target.parents().hasClass('js-stop-propagation')
				) {
					return false;
				}
				const block = $(this).closest('.js-toggle-panel');
				const blockContent = block.find('.blockContent');
				const isEmpty = blockContent.is(':empty');
				let url = block.data('url');
				if (!blockContent.is(':visible') && url) {
					blockContent.progressIndicator();
					AppConnector.request(url).done(function (response) {
						blockContent.html(response);
						const relatedController = Vtiger_RelatedList_Js.getInstance(
							thisInstance.getRecordId(),
							app.getModuleName(),
							thisInstance.getSelectedTab(),
							block.data('reference')
						);
						relatedController.setRelatedContainer(blockContent);
						if (isEmpty) {
							relatedController.registerRelatedEvents();
						} else {
							relatedController.registerPostLoadEvents();
						}
					});
				}
			});
		},
		registerBlockAnimationEvent: function () {
			var thisInstance = this;
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.find('.blockHeader').on('click', function (e) {
				const target = $(e.target);
				if (
					target.is('input') ||
					target.is('button') ||
					target.parents().is('button') ||
					target.hasClass('js-stop-propagation') ||
					target.parents().hasClass('js-stop-propagation')
				) {
					return false;
				}
				var currentTarget = $(this).find('.js-block-toggle').not('.d-none');
				var blockId = currentTarget.data('id');
				var closestBlock = currentTarget.closest('.js-toggle-panel');
				var bodyContents = closestBlock.find('.blockContent');
				var data = currentTarget.data();
				var module = app.getModuleName();
				if (data.mode === 'show') {
					bodyContents.addClass('d-none');
					app.cacheSet(module + '.' + blockId, 0);
					currentTarget.addClass('d-none');
					closestBlock.find('[data-mode="hide"]').removeClass('d-none');
				} else {
					bodyContents.removeClass('d-none');
					app.cacheSet(module + '.' + blockId, 1);
					currentTarget.addClass('d-none');
					closestBlock.find('[data-mode="show"]').removeClass('d-none');
				}
				app.event.trigger('DetailView.js-block-toggle.PostLoad', bodyContents, data, thisInstance);
			});
		},
		registerBlockStatusCheckOnLoad: function () {
			let blocks = this.getContentHolder().find('.js-toggle-panel');
			let module = app.getModuleName();
			blocks.each(function (index, block) {
				let currentBlock = jQuery(block);
				let dynamicAttr = currentBlock.attr('data-dynamic');
				if (typeof dynamicAttr !== typeof undefined && dynamicAttr !== false) {
					let headerAnimationElement = currentBlock.find('.js-block-toggle').not('.d-none');
					let bodyContents = currentBlock.closest('.js-toggle-panel').find('.blockContent');
					let blockId = headerAnimationElement.data('id');
					let cacheKey = module + '.' + blockId;
					let value = app.cacheGet(cacheKey, null);
					if (value != null) {
						if (value == 1) {
							headerAnimationElement.addClass('d-none');
							currentBlock.find("[data-mode='show']").removeClass('d-none');
							bodyContents.removeClass('d-none');
						} else {
							headerAnimationElement.addClass('d-none');
							currentBlock.find("[data-mode='hide']").removeClass('d-none');
							bodyContents.addClass('d-none');
						}
					}
				}
			});
		},
		/**
		 * Function to handle the ajax edit for detailview and summary view fields
		 * which will expects the currentTdElement
		 */
		ajaxEditHandling: function (currentTdElement) {
			const thisInstance = this;
			let readRecord = $('.setReadRecord'),
				detailViewValue = $('.value', currentTdElement),
				editElement = $('.edit', currentTdElement),
				actionElement = $('.js-detail-quick-edit', currentTdElement),
				fieldElement = $('.fieldname', editElement);
			readRecord.prop('disabled', true);
			$(fieldElement).each(function (index, element) {
				let fieldName = $(element).val(),
					elementTarget = $(element),
					elementName =
						$.inArray(elementTarget.data('type'), [
							'taxes',
							'sharedOwner',
							'multipicklist',
							'multiListFields',
							'multiDomain',
							'mailScannerFields',
							'mailScannerActions',
						]) != -1
							? fieldName + '[]'
							: fieldName;
				let fieldElement = $('[name="' + elementName + '"]:not([type="hidden"])', editElement);
				if (fieldElement.attr('disabled') == 'disabled') {
					return;
				}
				if (editElement.length <= 0) {
					return;
				}
				if (editElement.is(':visible')) {
					return;
				}
				if (fieldElement.attr('data-inputmask')) {
					fieldElement.inputmask();
				}
				detailViewValue.addClass('d-none');
				actionElement.addClass('d-none');
				editElement
					.removeClass('d-none')
					.children()
					.filter('input[type!="hidden"]input[type!="image"],select')
					.filter(':first')
					.focus();
				let saveHandler = function (e) {
					thisInstance.registerNameAjaxEditEvent();
					let element = $(e.target);
					if ($(e.currentTarget).find('.dateTimePickerField').length) {
						if (element.closest('.drp-calendar').length || element.hasClass('drp-calendar')) {
							return;
						}
					}
					if (
						element.closest('.fieldValue').is(currentTdElement) ||
						element.hasClass('select2-selection__choice__remove') ||
						element.closest('.select2-container--open').length ||
						element.parents('.clockpicker-popover').length
					) {
						return;
					}
					currentTdElement.removeAttr('tabindex');
					currentTdElement.removeClass('is-edit-active');
					let previousValue = elementTarget.data('prevValue'),
						editElement = elementTarget.closest('.edit'),
						ajaxEditNewValue =
							editElement.find('[name="' + elementName + '"]').length > 0
								? editElement.find('[name="' + elementName + '"]').val()
								: editElement.find('[name="' + fieldName + '"]').val(),
						fieldInfo = Vtiger_Field_Js.getInstance(fieldElement.data('fieldinfo')),
						dateTimeField = [],
						dateTime = false;
					if (editElement.find('[data-fieldinfo]').length == 2) {
						editElement.find('[data-fieldinfo]').each(function () {
							let field = {
								name: $(this).attr('name'),
								type: $(this).data('fieldinfo').type,
							};
							if (field['type'] == 'datetime') {
								dateTime = true;
							}
							dateTimeField.push(field);
						});
					}
					if (fieldElement.is('input:checkbox')) {
						if (fieldElement.is(':checked')) {
							ajaxEditNewValue = '1';
						} else {
							ajaxEditNewValue = '0';
						}
						fieldElement = fieldElement.filter('[type="checkbox"]');
					}
					if (fieldElement.validationEngine('validate')) {
						if (fieldElement.attr('data-inputmask')) {
							fieldElement.inputmask();
						}
						return;
					}
					function toStr(v) {
						return v === undefined || v === null ? '' : v + '';
					}
					fieldElement.validationEngine('hide');
					if (toStr(previousValue) === toStr(ajaxEditNewValue)) {
						editElement.addClass('d-none');
						detailViewValue.removeClass('d-none');
						actionElement.removeClass('d-none');
						readRecord.prop('disabled', false);
						editElement.off('clickoutside');
					} else {
						let preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave),
							fieldNameValueMap = {};
						fieldElement.trigger(preFieldSaveEvent, {
							fieldValue: ajaxEditNewValue,
							recordId: thisInstance.getRecordId(),
						});
						if (preFieldSaveEvent.isDefaultPrevented()) {
							readRecord.prop('disabled', false);
							return;
						}
						currentTdElement.progressIndicator();
						editElement.addClass('d-none');
						fieldNameValueMap['value'] = ajaxEditNewValue;
						fieldNameValueMap['field'] = fieldName;
						fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
						thisInstance
							.saveFieldValues(fieldNameValueMap)
							.done(function (response) {
								editElement.off('clickoutside');
								readRecord.prop('disabled', false);
								currentTdElement.progressIndicator({ mode: 'hide' });
								detailViewValue.removeClass('d-none');
								actionElement.removeClass('d-none');
								if (!response.success) {
									return;
								}
								const postSaveRecordDetails = response.result;
								let displayValue = postSaveRecordDetails[fieldName].display_value,
									prevDisplayValue = postSaveRecordDetails[fieldName].prev_display_value;
								if (dateTimeField.length && dateTime) {
									displayValue =
										postSaveRecordDetails[dateTimeField[0].name].display_value +
										' ' +
										postSaveRecordDetails[dateTimeField[1].name].display_value;
								}
								detailViewValue.html(displayValue);
								Vtiger_Helper_Js.showPnotify({
									title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
									text:
										'<b>' +
										fieldInfo.data.label +
										'</b><br>' +
										'<b>' +
										app.vtranslate('JS_SAVED_FROM') +
										'</b>: ' +
										prevDisplayValue +
										'<br> ' +
										'<b>' +
										app.vtranslate('JS_SAVED_TO') +
										'</b>: ' +
										displayValue,
									type: 'info',
									textTrusted: true,
								});
								if (postSaveRecordDetails['isViewable'] === false) {
									let urlObject = app.convertUrlToObject(window.location.href);
									if (window !== window.parent) {
										window.parent.location.href =
											'index.php?module=' + urlObject['module'] + '&view=ListPreview';
									} else {
										window.location.href = 'index.php?module=' + urlObject['module'] + '&view=List';
									}
								} else if (postSaveRecordDetails['isEditable'] === false) {
									$.progressIndicator({
										position: 'html',
										blockInfo: {
											enabled: true,
										},
									});
									if (window !== window.parent) {
										window.location.href = window.location.href.replace(
											'view=Detail',
											'view=DetailPreview'
										);
									} else {
										window.location.reload();
									}
								}
								fieldElement.trigger(thisInstance.fieldUpdatedEvent, {
									old: previousValue,
									new: ajaxEditNewValue,
								});
								ajaxEditNewValue = ajaxEditNewValue === undefined ? '' : ajaxEditNewValue; //data cannot be undefined
								elementTarget.data('prevValue', ajaxEditNewValue);
								fieldElement.data('selectedValue', ajaxEditNewValue);
								if (thisInstance.targetPicklistChange) {
									if ($('.js-widget-general-info', thisInstance.getForm()).length > 0) {
										thisInstance.targetPicklist.find('.js-detail-quick-edit').trigger('click');
									} else {
										thisInstance.targetPicklist.trigger('click');
									}
									thisInstance.targetPicklistChange = false;
									thisInstance.targetPicklist = false;
								}
								let selectedTabElement = thisInstance.getSelectedTab();
								if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
									let detailContentsHolder = thisInstance.getContentHolder();
									thisInstance.reloadTabContent();
									thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
									thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
									thisInstance.registerEventForRelatedList();
								} else if (
									selectedTabElement.data('linkKey') == thisInstance.detailViewDetailsTabLabel
								) {
									thisInstance.registerEventForPicklistDependencySetup(thisInstance.getForm());
								}
								thisInstance.updateRecordsPDFTemplateBtn(thisInstance.getForm());
							})
							.fail(function (jqXHR, textStatus, errorThrown) {
								editElement.addClass('d-none');
								detailViewValue.removeClass('d-none');
								actionElement.removeClass('d-none');
								editElement.off('clickoutside');
								readRecord.prop('disabled', false);
								currentTdElement.progressIndicator({ mode: 'hide' });
								Vtiger_Helper_Js.showPnotify({
									type: 'error',
									title: app.vtranslate('JS_SAVE_NOTIFY_FAIL'),
									text: textStatus,
								});
							});
					}
				};
				editElement.on('clickoutside', saveHandler);
			});
		},
		triggerDisplayTypeEvent: function () {
			var widthType = app.cacheGet('widthType', 'narrowWidthType');
			if (widthType) {
				var elements = jQuery('#detailView').find('td');
				elements.addClass(widthType);
			}
		},
		/**
		 * Function updates the hidden elements which is used for creating relations
		 */
		addElementsToQuickCreateForCreatingRelation: function (container, customParams) {
			jQuery('<input type="hidden" name="relationOperation" value="true" >').appendTo(container);
			jQuery.each(customParams, function (index, value) {
				jQuery('<input type="hidden" name="' + index + '" value="' + value + '" >').appendTo(container);
			});
		},
		/**
		 * Function to register event for activity widget for adding
		 * event and task from the widget
		 */
		registerEventForActivityWidget: function () {
			var thisInstance = this;

			/*
			 * Register click event for add button in Related Activities widget
			 */
			jQuery('.createActivity').on('click', function (e) {
				var referenceModuleName = 'Calendar';
				var recordId = thisInstance.getRecordId();
				var module = app.getModuleName();
				var element = jQuery(e.currentTarget);

				let customParams = {};
				customParams['sourceModule'] = module;
				customParams['sourceRecord'] = recordId;
				if (
					module != '' &&
					referenceModuleName != '' &&
					typeof thisInstance.referenceFieldNames[referenceModuleName] !== 'undefined' &&
					typeof thisInstance.referenceFieldNames[referenceModuleName][module] !== 'undefined'
				) {
					var relField = thisInstance.referenceFieldNames[referenceModuleName][module];
					customParams[relField] = recordId;
				}
				var fullFormUrl = element.data('url');
				var preQuickCreateSave = function (data) {
					thisInstance.addElementsToQuickCreateForCreatingRelation(data, customParams);
					var taskGoToFullFormButton = data
						.find('[class^="CalendarQuikcCreateContents"]')
						.find('.js-full-editlink');
					var eventsGoToFullFormButton = data
						.find('[class^="EventsQuikcCreateContents"]')
						.find('.js-full-editlink');
					var taskFullFormUrl = taskGoToFullFormButton.data('url') + '&' + fullFormUrl;
					var eventsFullFormUrl = eventsGoToFullFormButton.data('url') + '&' + fullFormUrl;
					taskGoToFullFormButton.data('url', taskFullFormUrl);
					eventsGoToFullFormButton.data('url', eventsFullFormUrl);
				};
				var callbackFunction = function () {
					thisInstance.getFiltersDataAndLoad(e);
					thisInstance.loadWidget($('.widgetContentBlock[data-type="Updates"]'));
				};
				let QuickCreateParams = {};
				QuickCreateParams['callbackPostShown'] = preQuickCreateSave;
				QuickCreateParams['callbackFunction'] = callbackFunction;
				QuickCreateParams['data'] = Object.assign({}, customParams);
				QuickCreateParams['noCache'] = false;
				Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, QuickCreateParams);
			});
		},
		getEndDate: function (startDate) {
			var dateTab = startDate.split('-');
			var date = new Date(dateTab[0], dateTab[1], dateTab[2]);
			var newDate = new Date();

			newDate.setDate(date.getDate() + 2);
			return app.getStringDate(newDate);
		},
		getSingleEventType: function (modDay, id, type) {
			var dateStartEl = jQuery('[name="date_start"]');
			var dateStartVal = jQuery(dateStartEl).val();
			var dateStartFormat = jQuery(dateStartEl).data('date-format');
			var validDateFromat = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay, type);
			var map = jQuery.extend({}, ['#b6a996,black']);

			var params = {
				module: 'Calendar',
				action: 'Feed',
				start: validDateFromat,
				end: this.getEndDate(validDateFromat),
				type: type,
				mapping: map,
			};

			AppConnector.request(params).done(function (events) {
				var testDate = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay);
				if (!jQuery.isEmptyObject(events)) {
					if (events[0]['activitytype'] === 'Task') {
						for (var ev in events) {
							if (events[ev]['start'].indexOf(testDate) > -1) {
								jQuery('#' + id + ' .table').append(
									'<tr><td><a target="_blank" href="' +
										events[ev]['url'] +
										'">' +
										events[ev]['title'] +
										'</a></td></tr>'
								);
							}
						}
					} else {
						for (var i = 0; i < events[0].length; i++) {
							if (events[0][i]['start'].indexOf(testDate) > -1) {
								jQuery('#' + id + ' .table').append(
									'<tr><td><a target="_blank" href="' +
										events[0][i]['url'] +
										'">' +
										events[0][i]['title'] +
										'</a></td></tr>'
								);
							}
						}
					}
				}
			});
		},
		/**
		 * Function to add module related record from summary widget
		 */
		registerFilterForAddingModuleRelatedRecordFromSummaryWidget: function (container) {
			var thisInstance = this;
			container
				.find('.createRecordFromFilter')
				.off()
				.on('click', function (e) {
					var currentElement = jQuery(e.currentTarget);
					var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
					var referenceModuleName = summaryWidgetContainer.data('moduleName');
					var quickcreateUrl = currentElement.data('url');
					var parentId = thisInstance.getRecordId();
					var quickCreateParams = {};
					var relatedField = currentElement.data('prf');
					var autoCompleteFields = currentElement.data('acf');
					var moduleName = currentElement
						.closest('.js-detail-widget-header')
						.find('[name="relatedModule"]')
						.val();
					var relatedParams = {};
					var postQuickCreateSave = function (data) {
						thisInstance.postSummaryWidgetAddRecord(data, currentElement);
						if (referenceModuleName == 'ProjectTask') {
							thisInstance.loadModuleSummary();
						}
					};
					if (typeof relatedField !== 'undefined') {
						relatedParams[relatedField] = parentId;
					}
					if (typeof autoCompleteFields !== 'undefined') {
						$.each(autoCompleteFields, function (index, value) {
							relatedParams[index] = value;
						});
					}
					if (Object.keys(relatedParams).length > 0) {
						quickCreateParams['data'] = relatedParams;
					}
					quickCreateParams['noCache'] = true;
					quickCreateParams['callbackFunction'] = postQuickCreateSave;
					var progress = jQuery.progressIndicator({
						blockInfo: {
							enabled: true,
						},
					});
					let headerInstance;
					if (window !== window.parent) {
						headerInstance = window.parent.Vtiger_Header_Js.getInstance();
					} else {
						headerInstance = Vtiger_Header_Js.getInstance();
					}
					headerInstance
						.getQuickCreateForm(quickcreateUrl, moduleName, quickCreateParams)
						.done(function (data) {
							headerInstance.handleQuickCreateData(data, quickCreateParams);
							progress.progressIndicator({ mode: 'hide' });
						});
				});
			container
				.find('button.selectRelation')
				.off('click')
				.on('click', function (e) {
					let summaryWidgetContainer = jQuery(e.currentTarget).closest('.js-detail-widget');
					let referenceModuleName = summaryWidgetContainer.data('moduleName');
					let restrictionsField = $(this).data('rf');
					let params = {
						module: referenceModuleName,
						src_module: app.getModuleName(),
						src_record: thisInstance.getRecordId(),
						multi_select: true,
						relationId: summaryWidgetContainer.data('relationId'),
					};
					if (restrictionsField && Object.keys(restrictionsField).length > 0) {
						params['search_key'] = restrictionsField.key;
						params['search_value'] = restrictionsField.name;
					}
					app.showRecordsList(params, (modal, instance) => {
						instance.setSelectEvent((responseData) => {
							thisInstance
								.addRelationBetweenRecords(referenceModuleName, Object.keys(responseData), null, {
									relationId: params.relationId,
								})
								.done(function (data) {
									thisInstance.loadWidget(summaryWidgetContainer.find('.widgetContentBlock'));
								});
						});
					});
				});
		},
		registerAddingInventoryRecords: function () {
			jQuery('.createInventoryRecordFromFilter').on('click', function (e) {
				var currentElement = jQuery(e.currentTarget);
				var createUrl = currentElement.data('url');
				var autoCompleteFields = currentElement.data('acf');
				var addidtionalParams = '';
				if (typeof autoCompleteFields !== 'undefined') {
					$.each(autoCompleteFields, function (index, value) {
						addidtionalParams = '&' + index + '=' + value;
						createUrl = createUrl.concat(addidtionalParams);
					});
				}
				window.location.href = createUrl;
			});
		},
		registerEmailEvent: function () {
			this.getContentHolder()
				.find('.resetRelationsEmail')
				.on('click', function (e) {
					Vtiger_Helper_Js.showConfirmationBox({
						message: app.vtranslate('JS_EMAIL_RESET_RELATIONS_CONFIRMATION'),
					}).done(function (data) {
						AppConnector.request({
							module: 'OSSMailView',
							action: 'Relation',
							moduleName: app.getModuleName(),
							record: app.getRecordId(),
						}).done(function (d) {
							Vtiger_Helper_Js.showMessage({ text: d.result });
						});
					});
				});
		},
		getFiltersDataAndLoad: function (e, params) {
			var data = this.getFiltersData(e, params);
			this.loadWidget(data['container'], data['params']);
		},
		getFiltersData: function (e, params) {
			if (e.currentTarget) {
				var currentElement = jQuery(e.currentTarget);
			} else {
				currentElement = e;
			}
			var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			var widget = summaryWidgetContainer.find('.widgetContentBlock');
			var url = '&' + widget.data('url');
			var urlParams = {};
			url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				urlParams[key] = value;
			});
			var urlNewParams = [];
			summaryWidgetContainer.find('.js-switch, .js-filter_field').each(function (n, item) {
				var value = '';
				var element = jQuery(item);
				var name = element.data('urlparams');
				if (element.attr('type') == 'radio') {
					if (element.prop('checked')) {
						value =
							typeof element.data('on-val') !== 'undefined'
								? element.data('on-val')
								: element.data('off-val');
					}
				} else {
					var selectedFilter = element.find('option:selected').val();
					var fieldlable = element.data('fieldlable');
					var filter = element.data('filter');
					if (element.data('return') === 'value') {
						value = selectedFilter;
					} else {
						if (selectedFilter != fieldlable) {
							value = [[filter, 'e', selectedFilter]];
						} else {
							return;
						}
					}
				}
				if (name && value) {
					if (element.data('return') === 'value') {
						urlNewParams[name] = value;
					} else {
						if (name in urlNewParams) {
							urlNewParams[name].push(value);
						} else {
							urlNewParams[name] = [value];
						}
					}
				}
			});
			if (params != undefined) {
				$.extend(urlNewParams, params);
			}
			return { container: $(widget), params: $.extend(urlParams, urlNewParams) };
		},
		registerChangeFilterForWidget: function () {
			var thisInstance = this;
			jQuery('.js-switch').on('change', function (e, state) {
				$(e.currentTarget).closest('.js-switch__btn').addClass('active').siblings().removeClass('active');
				thisInstance.getFiltersDataAndLoad(e);
			});
			jQuery('.js-filter_field').on('select2:select', function (e, state) {
				thisInstance.getFiltersDataAndLoad(e);
			});
		},
		/**
		 * Function to register all the events related to summary view widgets
		 */
		registerSummaryViewContainerEvents: function (summaryViewContainer) {
			var thisInstance = this;
			this.registerEventForActivityWidget();
			this.registerChangeFilterForWidget();
			this.registerAddingInventoryRecords();
			this.registerEmailEvent();
			/**
			 * Function to handle the ajax edit for summary view fields
			 */
			summaryViewContainer.off('click').on('click', '.row .js-detail-quick-edit', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				currentTarget.addClass('d-none');
				var currentTdElement = currentTarget.closest('.fieldValue');
				thisInstance.ajaxEditHandling(currentTdElement);
			});
			/**
			 * Function to handle actions after ajax save in summary view
			 */
			summaryViewContainer.on(thisInstance.fieldUpdatedEvent, '.js-widget-general-info', function () {
				let updatesWidget = summaryViewContainer.find("[data-type='Updates']"),
					params;
				if (updatesWidget.length) {
					params = thisInstance.getFiltersData(updatesWidget);
					updatesWidget.find('.btnChangesReviewedOn').parent().remove();
					thisInstance.loadWidget(updatesWidget, params['params']);
				}
			});

			summaryViewContainer.on('click', '.editDefaultStatus', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				currentTarget.popover('hide');
				var url = currentTarget.data('url');
				if (url) {
					if (currentTarget.hasClass('showEdit')) {
						var headerInstance = Vtiger_Header_Js.getInstance();
						if (window !== window.parent) {
							headerInstance = window.parent.Vtiger_Header_Js.getInstance();
						}
						headerInstance.getQuickCreateForm(url, 'Calendar', { noCache: true }).done((data) => {
							headerInstance.handleQuickCreateData(data, {
								callbackFunction: () => {
									let widget = currentTarget.closest('.widgetContentBlock');
									if (widget.length) {
										thisInstance.loadWidget(widget);
										let updatesWidget = thisInstance
											.getContentHolder()
											.find("[data-type='Updates']");
										if (updatesWidget.length > 0) {
											thisInstance.loadWidget(updatesWidget);
										}
									} else {
										thisInstance.loadRelatedList();
									}
									thisInstance.registerRelatedModulesRecordCount();
								},
							});
						});
					} else {
						app.showModalWindow(null, url);
					}
				}
			});

			/*
			 * Register the event to edit Description for related activities
			 */
			summaryViewContainer.on('click', '.editDescription', function (e) {
				new App.Fields.Text.Editor(thisInstance.getContentHolder(), { toolbar: 'Min' });
				let currentTarget = jQuery(e.currentTarget),
					currentDiv = currentTarget.closest('.activityDescription'),
					editElement = currentDiv.find('.edit'),
					detailViewElement = currentDiv.find('.value'),
					descriptionText = currentDiv.find('.js-description-text'),
					descriptionEmpty = currentDiv.find('.js-no-description'),
					saveButton = currentDiv.find('.js-save-description'),
					closeButton = currentDiv.find('.js-close-description'),
					activityButtonContainer = currentDiv.find('.js-activity-buttons__container'),
					fieldnameElement = jQuery('.fieldname', editElement),
					fieldName = fieldnameElement.val(),
					fieldElement = jQuery('[name="' + fieldName + '"]', editElement),
					callbackFunction = () => {
						let previousValue = fieldnameElement.data('prevValue'),
							ajaxEditNewValue = fieldElement.val(),
							ajaxEditNewLable = fieldElement.val(),
							activityDiv = currentDiv.closest('.activityEntries'),
							activityId = activityDiv.find('.activityId').val(),
							moduleName = activityDiv.find('.activityModule').val(),
							activityType = activityDiv.find('.activityType').val();
						if (previousValue == ajaxEditNewValue) {
							closeDescription();
						} else {
							currentDiv.progressIndicator();
							editElement.add(activityButtonContainer).addClass('d-none');
							return new Promise(function (resolve, reject) {
								resolve(fieldElement.validationEngine('validate'));
							}).then((errorExists) => {
								//If validation fails
								if (errorExists) {
									Vtiger_Helper_Js.addClickOutSideEvent(currentDiv, callbackFunction);
									return;
								} else {
									ajaxEditNewValue = fieldElement.val(); //update editor value after conversion
									AppConnector.request({
										action: 'SaveAjax',
										record: activityId,
										field: fieldName,
										value: ajaxEditNewValue,
										module: moduleName,
										activitytype: activityType,
									}).done(() => {
										currentDiv.progressIndicator({ mode: 'hide' });
										detailViewElement.removeClass('d-none');
										currentTarget.show();
										descriptionText.html(ajaxEditNewLable);
										fieldnameElement.data('prevValue', ajaxEditNewValue);
										if (ajaxEditNewValue === '') {
											descriptionEmpty.removeClass('d-none');
										} else {
											descriptionEmpty.addClass('d-none');
										}
									});
								}
							});
						}
					},
					closeDescription = function () {
						fieldElement.val(fieldnameElement.data('prevValue'));
						editElement.add(activityButtonContainer).addClass('d-none');
						detailViewElement.removeClass('d-none');
						currentTarget.show();
					};
				currentTarget.hide();
				detailViewElement.addClass('d-none');
				activityButtonContainer.removeClass('d-none');
				editElement.removeClass('d-none').show();
				saveButton.off('click').one('click', callbackFunction);
				closeButton.off('click').one('click', closeDescription);
			});

			/*
			 * Register click event for add button in Related widgets
			 * to add record from widget
			 */

			jQuery('.changeDetailViewMode').on('click', function (e) {
				thisInstance
					.getTabs()
					.filter('[data-link-key="' + thisInstance.detailViewDetailsTabLabel + '"]:not(.d-none)')
					.trigger('click');
			});

			/*
			 * Register click event for add button in Related widgets
			 * to add record from widget
			 */
			jQuery('.createRecord').on('click', function (e) {
				var currentElement = jQuery(e.currentTarget);
				var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
				var widgetHeaderContainer = summaryWidgetContainer.find('.js-detail-widget-header');
				var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
				var recordId = thisInstance.getRecordId();
				var module = app.getModuleName();
				var customParams = {};
				customParams['sourceModule'] = module;
				customParams['sourceRecord'] = recordId;
				if (
					module != '' &&
					referenceModuleName != '' &&
					typeof thisInstance.referenceFieldNames[referenceModuleName] !== 'undefined' &&
					typeof thisInstance.referenceFieldNames[referenceModuleName][module] !== 'undefined'
				) {
					var fieldName = thisInstance.referenceFieldNames[referenceModuleName][module];
					customParams[fieldName] = recordId;
				}

				var postQuickCreateSave = function (data) {
					thisInstance.postSummaryWidgetAddRecord(data, currentElement);
				};

				var goToFullFormcallback = function (data) {
					thisInstance.addElementsToQuickCreateForCreatingRelation(data, customParams);
				};

				var QuickCreateParams = {};
				QuickCreateParams['callbackFunction'] = postQuickCreateSave;
				QuickCreateParams['goToFullFormcallback'] = goToFullFormcallback;
				QuickCreateParams['data'] = customParams;
				QuickCreateParams['noCache'] = false;
				Vtiger_Header_Js.getInstance().quickCreateModule(referenceModuleName, QuickCreateParams);
			});
			this.registerFastEditingFiels();
		},
		addRelationBetweenRecords: function (relatedModule, relatedModuleRecordId, selectedTabElement, params = {}) {
			var aDeferred = jQuery.Deferred();
			var thisInstance = this;
			if (selectedTabElement == undefined) {
				selectedTabElement = thisInstance.getSelectedTab();
			}
			var relatedController = Vtiger_RelatedList_Js.getInstance(
				thisInstance.getRecordId(),
				app.getModuleName(),
				selectedTabElement,
				relatedModule
			);
			relatedController
				.addRelations(relatedModuleRecordId, params)
				.done(function (data) {
					var summaryViewContainer = thisInstance.getContentHolder();
					var updatesWidget = summaryViewContainer.find("[data-type='Updates']");
					if (updatesWidget.length > 0) {
						var params = thisInstance.getFiltersData(updatesWidget);
						updatesWidget.find('.btnChangesReviewedOn').parent().remove();
						thisInstance.loadWidget(updatesWidget, params['params']);
					}
					aDeferred.resolve(data);
				})
				.fail(function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to handle Post actions after adding record from
		 * summary view widget
		 */
		postSummaryWidgetAddRecord: function (data, currentElement) {
			var thisInstance = this;
			var summaryWidgetContainer = currentElement.closest('.js-detail-widget');
			var widgetHeaderContainer = summaryWidgetContainer.find('.js-detail-widget-header');
			var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
			var idList = [];
			idList.push(data.result._recordId);
			let params = {};
			if (summaryWidgetContainer.data('relationId')) {
				params.relationId = summaryWidgetContainer.data('relationId');
			}
			this.addRelationBetweenRecords(referenceModuleName, idList, null, params).done(function (data) {
				thisInstance.loadWidget(summaryWidgetContainer.find('[class^="widgetContainer_"]'));
			});
		},
		registerChangeEventForModulesList: function () {
			jQuery('#tagSearchModulesList').on('change', function (e) {
				var modulesSelectElement = jQuery(e.currentTarget);
				if (modulesSelectElement.val() == 'all') {
					jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
				} else {
					jQuery('[name="tagSearchModuleResults"]').removeClass('d-none');
					var selectedOptionValue = modulesSelectElement.val();
					jQuery('[name="tagSearchModuleResults"]')
						.filter(':not(#' + selectedOptionValue + ')')
						.addClass('d-none');
				}
			});
		},
		registerEventForRelatedTabClick: function () {
			var thisInstance = this;
			var detailContentsHolder = thisInstance.getContentHolder();
			var detailContainer = detailContentsHolder.closest('div.detailViewInfo');

			jQuery('.related', detailContainer).on('click', 'li:not(.spaceRelatedList)', function (e, urlAttributes) {
				var tabElement = jQuery(e.currentTarget);
				if (!tabElement.hasClass('dropdown')) {
					var element = jQuery('<div></div>');
					element.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true,
							elementToBlock: detailContainer,
						},
					});
					var url = tabElement.data('url');
					if (typeof urlAttributes !== 'undefined') {
						var callBack = urlAttributes.callback;
						delete urlAttributes.callback;
					}
					thisInstance
						.loadContents(url, urlAttributes)
						.done(function (data) {
							thisInstance.deSelectAllrelatedTabs();
							thisInstance.markTabAsSelected(tabElement);
							Vtiger_Helper_Js.showHorizontalTopScrollBar();
							element.progressIndicator({ mode: 'hide' });
							thisInstance.registerHelpInfo();
							app.registerModal(detailContentsHolder);
							if (typeof callBack == 'function') {
								callBack(data);
							}
							//Summary tab is clicked
							if (tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
								thisInstance.loadWidgets();
							}
							thisInstance.registerBasicEvents();
							// Let listeners know about page state change.
							app.notifyPostAjaxReady();
							app.event.trigger('DetailView.Tab.AfterLoad', data, thisInstance);
						})
						.fail(function () {
							element.progressIndicator({ mode: 'hide' });
						});
				}
			});
		},
		/**
		 * Function to register event for setting up picklistdependency
		 * for a module if exist on change of picklist value
		 */
		registerEventForPicklistDependencySetup: function (container) {
			var thisInstance = this;
			var picklistDependcyElemnt = jQuery('[name="picklistDependency"]', container);
			if (picklistDependcyElemnt.length <= 0) {
				return;
			}
			var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());
			var sourcePicklists = Object.keys(picklistDependencyMapping);
			if (sourcePicklists.length <= 0) {
				return;
			}

			var sourcePickListNames = [];
			for (var i = 0; i < sourcePicklists.length; i++) {
				sourcePickListNames.push('[name="' + sourcePicklists[i] + '"]');
			}
			sourcePickListNames = sourcePickListNames.join(',');
			var sourcePickListElements = container.find(sourcePickListNames);
			sourcePickListElements.on('change', function (e) {
				var currentElement = jQuery(e.currentTarget);
				var sourcePicklistname = currentElement.attr('name');

				var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
				var selectedValue = currentElement.val();
				var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
				var picklistmap = configuredDependencyObject['__DEFAULT__'];

				if (typeof targetObjectForSelectedSourceValue === 'undefined') {
					targetObjectForSelectedSourceValue = picklistmap;
				}
				jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
					var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
					if (typeof targetPickListMap === 'undefined') {
						targetPickListMap = targetPickListValues;
					}
					var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
					if (targetPickList.length <= 0) {
						return;
					}

					//On change of SourceField value, If TargetField value is not there in mapping, make user to select the new target value also.
					var selectedValue = targetPickList.data('selectedValue');
					if (jQuery.inArray(selectedValue, targetPickListMap) == -1) {
						thisInstance.targetPicklistChange = true;
						thisInstance.targetPicklist = targetPickList.closest('td');
					} else {
						thisInstance.targetPicklistChange = false;
						thisInstance.targetPicklist = false;
					}

					var listOfAvailableOptions = targetPickList.data('availableOptions');
					if (typeof listOfAvailableOptions === 'undefined') {
						listOfAvailableOptions = jQuery('option', targetPickList);
						targetPickList.data('available-options', listOfAvailableOptions);
					}

					var targetOptions = new jQuery();
					var optionSelector = [];
					optionSelector.push('');
					for (var i = 0; i < targetPickListMap.length; i++) {
						optionSelector.push(targetPickListMap[i]);
					}

					jQuery.each(listOfAvailableOptions, function (i, e) {
						var picklistValue = jQuery(e).val();
						if (jQuery.inArray(picklistValue, optionSelector) != -1) {
							targetOptions = targetOptions.add(jQuery(e));
						}
					});
					var targetPickListSelectedValue = '';
					targetPickListSelectedValue = targetOptions.filter('[selected]').val();
					if (targetPickListMap.length == 1) {
						targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present.
					}
					targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger('change');
				});
			});
			//To Trigger the change on load
			sourcePickListElements.trigger('change');
		},
		/**
		 * Function to get child comments
		 */
		getChildComments: function (commentId) {
			var aDeferred = jQuery.Deferred();
			var url =
				'module=' +
				app.getModuleName() +
				'&view=Detail&record=' +
				this.getRecordId() +
				'&mode=showChildComments&commentid=' +
				commentId;
			var dataObj = this.getCommentThread(url);
			dataObj.done(function (data) {
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},
		/**
		 * Function to get parent comment
		 * @param {number} commentId
		 * @returns {string}
		 */
		getParentComments(commentId) {
			let aDeferred = $.Deferred(),
				url =
					'module=' +
					app.getModuleName() +
					'&view=Detail&record=' +
					this.getRecordId() +
					'&mode=showParentComments&commentid=' +
					commentId;
			this.getCommentThread(url).done(function (data) {
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},
		/**
		 * Function to show total records count in listview on hover
		 * of pageNumber text
		 */
		registerEventForTotalRecordsCount: function () {
			var thisInstance = this;
			var detailContentsHolder = this.getContentHolder();
			detailContentsHolder.on('click', '.totalNumberOfRecords', function (e) {
				var element = jQuery(e.currentTarget);
				var totalNumberOfRecords = jQuery('#totalCount').val();
				element.addClass('d-none');
				element.parent().progressIndicator({});
				if (totalNumberOfRecords == '') {
					var selectedTabElement = thisInstance.getSelectedTab();
					var relatedModuleName = thisInstance.getRelatedModuleName();
					var relatedController = Vtiger_RelatedList_Js.getInstance(
						thisInstance.getRecordId(),
						app.getModuleName(),
						selectedTabElement,
						relatedModuleName
					);
					relatedController.getRelatedPageCount().done(function () {
						thisInstance.showPagingInfo();
					});
				} else {
					thisInstance.showPagingInfo();
				}
				element.parent().progressIndicator({ mode: 'hide' });
			});
		},
		showPagingInfo: function () {
			var totalNumberOfRecords = jQuery('#totalCount').val();
			var pageNumberElement = jQuery('.pageNumbersText');
			var pageRange = pageNumberElement.text();
			var newPagingInfo = pageRange + ' (' + totalNumberOfRecords + ')';
			var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
			if (listViewEntriesCount != 0) {
				jQuery('.pageNumbersText').html(newPagingInfo);
			} else {
				jQuery('.pageNumbersText').html('');
			}
		},
		getCustomFieldNameValueMap: function (fieldNameValueMap) {
			return fieldNameValueMap;
		},
		registerSetReadRecord: function (detailContentsHolder) {
			var thisInstance = this;
			detailContentsHolder.on('click', '.setReadRecord', function (e) {
				var currentElement = jQuery(e.currentTarget);
				currentElement.closest('.btn-group').addClass('d-none');
				jQuery('#Accounts_detailView_fieldValue_was_read').find('.value').text(app.vtranslate('LBL_YES'));
				var params = {
					module: app.getModuleName(),
					action: 'SaveAjax',
					record: thisInstance.getRecordId(),
					field: 'was_read',
					value: 'on',
				};
				AppConnector.request(params).done(function (data) {
					var params = {
						text: app.vtranslate('JS_SET_READ_RECORD'),
						title: app.vtranslate('System'),
						type: 'info',
					};
					Vtiger_Helper_Js.showPnotify(params);
					var relatedTabKey = jQuery('.related li.active');
					if (
						relatedTabKey.data('linkKey') == thisInstance.detailViewSummaryTabLabel ||
						relatedTabKey.data('linkKey') == thisInstance.detailViewDetailsTabLabel
					) {
						thisInstance.reloadTabContent();
					}
				});
			});
		},
		registerFastEditingFiels: function () {
			var thisInstance = this;
			var fastEditingFiels = jQuery('.summaryWidgetFastEditing select');
			fastEditingFiels.on('change', function (e) {
				var fieldElement = jQuery(e.currentTarget);
				var fieldContainer = fieldElement.closest('.editField');
				var progressIndicatorElement = jQuery.progressIndicator({
					message: app.vtranslate('JS_SAVE_LOADER_INFO'),
					position: 'summaryWidgetFastEditing',
					blockInfo: {
						enabled: true,
					},
				});
				var fieldName = fieldContainer.data('fieldname');
				fieldName = fieldName.replace('q_', '');
				var fieldValue = fieldElement.val();
				var errorExists = fieldElement.validationEngine('validate');
				if (errorExists) {
					fieldContainer.progressIndicator({ mode: 'hide' });
					return;
				}
				var preFieldSaveEvent = jQuery.Event(thisInstance.fieldPreSave);
				fieldElement.trigger(preFieldSaveEvent, {
					fieldValue: fieldValue,
					recordId: thisInstance.getRecordId(),
				});
				var fieldNameValueMap = {};
				fieldNameValueMap['value'] = fieldValue;
				fieldNameValueMap['field'] = fieldName;
				fieldNameValueMap = thisInstance.getCustomFieldNameValueMap(fieldNameValueMap);
				thisInstance.saveFieldValues(fieldNameValueMap);
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				var params = {
					title: app.vtranslate('JS_SAVE_NOTIFY_OK'),
					type: 'success',
				};
				Vtiger_Helper_Js.showPnotify(params);
				thisInstance.reloadTabContent();
			});
		},
		registerHelpInfo: function () {
			var form = this.getForm();
			app.showPopoverElementView(form.find('.js-help-info'));
		},
		/**
		 * Register related modules record cound
		 * @param {jQuery} tabContainer
		 */
		registerRelatedModulesRecordCount(tabContainer) {
			const moreList = $('.related .nav .dropdown-menu');
			let relationContainer = tabContainer;
			if (!relationContainer || typeof relationContainer.length === 'undefined') {
				relationContainer = $(
					'.related .nav > .relatedNav, .related .nav > .mainNav, .detailViewBlockLink, .related .nav .dropdown-menu > .relatedNav'
				);
			}
			relationContainer.each((n, item) => {
				item = $(item);
				let relationId = item.data('relationId'),
					relatedModule = item.data('reference');
				if (item.data('count') === 1) {
					AppConnector.request({
						module: app.getModuleName(),
						action: 'RelationAjax',
						record: app.getRecordId(),
						relatedModule: relatedModule,
						mode: 'getRelatedListPageCount',
						relationId: relationId,
						tab_label: item.data('label-key'),
					}).done((response) => {
						if (response.success) {
							if (response.result.numberOfRecords === 0) {
								response.result.numberOfRecords = '';
							}
							item.find('.count').text(response.result.numberOfRecords);
							moreList
								.find('[data-reference="${relatedModule}"][data-relation-id="${relationId}"] .count')
								.text(response.result.numberOfRecords);
						}
					});
				}
			});
		},
		/**
		 * Function to display a new comments
		 */
		addComment: function (currentTarget, data) {
			const self = this;
			let mode = currentTarget.data('mode'),
				closestAddCommentBlock = currentTarget.closest('.js-add-comment-block'),
				commentTextAreaElement = closestAddCommentBlock.find('.js-comment-content'),
				commentInfoBlock = currentTarget.closest('.js-comment-single');
			commentTextAreaElement.html('');
			if (mode == 'add') {
				let commentId = data['result']['id'],
					commentHtml = self.getCommentUI(commentId);
				commentHtml.done(function (data) {
					let commentBlock = closestAddCommentBlock.closest('.js-comment-details'),
						detailContentsHolder = self.getContentHolder(),
						noCommentsMsgContainer = $('.js-noCommentsMsgContainer', detailContentsHolder);
					noCommentsMsgContainer.remove();
					if (commentBlock.length > 0) {
						closestAddCommentBlock.remove();
						let childComments = commentBlock.find('ul');
						if (childComments.length <= 0) {
							let currentChildCommentsCount = commentInfoBlock
									.find('.js-view-thread-block')
									.data('data-child-comments-count'),
								newChildCommentCount = currentChildCommentsCount + 1;
							commentInfoBlock.find('.js-child-comments-count').text(newChildCommentCount);
							let parentCommentId = commentInfoBlock.find('.js-comment-info-header').data('commentid');
							self.getChildComments(parentCommentId).done(function (responsedata) {
								$(responsedata).appendTo(commentBlock);
								commentInfoBlock.find('.js-view-thread-block').hide();
								commentInfoBlock.find('.hideThreadBlock').show();
							});
						} else {
							$('<li class="js-comment-details commentDetails">' + data + '</li>').appendTo(
								commentBlock.find('.js-comments-body')
							);
						}
					} else {
						$('<li class="js-comment-details commentDetails">' + data + '</li>').prependTo(
							closestAddCommentBlock.closest('.contents').find('.commentsList')
						);
					}
					commentInfoBlock.find('.js-comment-container').show();
					app.event.trigger('DetailView.SaveComment.AfterLoad', commentInfoBlock, data);
				});
			} else if (mode == 'edit') {
				let modifiedTime = commentInfoBlock.find('.js-comment-modified-time'),
					commentInfoContent = commentInfoBlock.find('.js-comment-info'),
					commentEditStatus = commentInfoBlock.find('.js-edited-status'),
					commentReason = commentInfoBlock.find('.js-edit-reason-span');
				commentInfoContent.html(data.result.commentcontent);
				commentReason.html(data.result.reasontoedit);
				modifiedTime.html(data.result.modifiedtime);
				modifiedTime.attr('title', data.result.modifiedtimetitle);
				if (commentEditStatus.hasClass('d-none')) {
					commentEditStatus.removeClass('d-none');
				}
				if (data.result.reasontoedit != '') {
					commentInfoBlock.find('.js-edit-reason').removeClass('d-none');
				}
				commentInfoContent.show();
				commentInfoBlock.find('.js-comment-container').show();
				closestAddCommentBlock.remove();
				app.event.trigger('DetailView.SaveComment.AfterUpdate', commentInfoBlock, data);
			}
		},
		/**
		 * Register all comment events
		 * @param {jQuery} detailContentsHolder
		 */
		registerCommentEvents(detailContentsHolder) {
			const self = this;
			detailContentsHolder.on('click', '.js-close-comment-block', function (e) {
				let commentInfoBlock = $(e.currentTarget.closest('.js-comment-single'));
				commentInfoBlock.find('.js-comment-container').show();
				commentInfoBlock.find('.js-comment-info').show();
				self.hideCommentBlock();
			});
			detailContentsHolder.on('click', '.js-reply-comment', function (e) {
				self.hideCommentBlock();
				let commentInfoBlock = $(e.currentTarget).closest('.js-comment-single');
				commentInfoBlock.find('.js-comment-container').hide();
				self.getCommentBlock().appendTo(commentInfoBlock).show();
			});
			detailContentsHolder.on('click', '.js-edit-comment', function (e) {
				self.hideCommentBlock();
				let commentInfoBlock = $(e.currentTarget).closest('.js-comment-single'),
					commentInfoContent = commentInfoBlock.find('.js-comment-info'),
					editCommentBlock = self.getEditCommentBlock();
				editCommentBlock.find('.js-comment-content').html(commentInfoContent.html());
				editCommentBlock.find('.js-reason-to-edit').html(commentInfoBlock.find('.js-edit-reason-span').text());
				commentInfoContent.hide();
				commentInfoBlock.find('.js-comment-container').hide();
				editCommentBlock.appendTo(commentInfoBlock).show();
			});
			detailContentsHolder.on('click', '.js-detail-view-save-comment', function (e) {
				let element = $(e.currentTarget);
				if (!element.is(':disabled')) {
					self.saveComment(e)
						.done(function () {
							self.registerRelatedModulesRecordCount();
							self.loadWidget(detailContentsHolder.find("[data-type='Comments']")).done(function () {
								element.removeAttr('disabled');
							});
						})
						.fail(function (error, err) {
							element.removeAttr('disabled');
							app.errorLog(error, err);
						});
				}
			});
			detailContentsHolder.on('click', '.js-save-comment', function (e) {
				let element = $(e.currentTarget);
				if (!element.is(':disabled')) {
					self.saveComment(e)
						.done(function (data) {
							self.registerRelatedModulesRecordCount(
								self.getTabByLabel(self.detailViewRecentCommentsTabLabel)
							);
							self.addComment(element, data);
							element.removeAttr('disabled');
						})
						.fail(function (error, err) {
							element.removeAttr('disabled');
							app.errorLog(error, err);
						});
					self.showCommentBlock();
				}
			});
			detailContentsHolder.on('click', '.js-more-recent-comments ', function () {
				self.getTabByLabel(self.detailViewRecentCommentsTabLabel).trigger('click');
			});
			detailContentsHolder.find('.js-detail-hierarchy-comments-btn').on('click', function (e) {
				if (
					$(this).hasClass('active') &&
					detailContentsHolder.find('.js-detail-hierarchy-comments-btn.active').length < 2
				) {
					return;
				}
				let recentCommentsTab = self.getTabByLabel(self.detailViewRecentCommentsTabLabel),
					url = recentCommentsTab.data('url'),
					regex = /&hierarchy=+([\w,]+)/;
				url = url.replace(regex, '');
				let hierarchy = [];
				if ($(this).hasClass('active')) {
					$(this).removeClass('active');
				} else {
					$(this).addClass('active');
				}
				detailContentsHolder.find('.js-detail-hierarchy-comments-btn.active').each(function () {
					hierarchy.push($(this).find('.js-detail-hierarchy-comments').val());
				});
				if (hierarchy.length !== 0) {
					url += '&hierarchy=' + hierarchy.join(',');
				}
				recentCommentsTab.data('url', url);
				recentCommentsTab.trigger('click');
			});
			detailContentsHolder.on('keypress', '.js-comment-search', function (e) {
				if (13 === e.which) {
					self.submitSearchForm(detailContentsHolder);
				}
			});
			detailContentsHolder.on('click', '.js-search-icon', function (e) {
				self.submitSearchForm(detailContentsHolder);
			});
		},
		/**
		 * Submit search comment form
		 * @param {jQuery} detailContentsHolder
		 */
		submitSearchForm(detailContentsHolder) {
			let searchTextDom = detailContentsHolder.find('.js-comment-search'),
				widgetContainer = searchTextDom.closest('[data-name="ModComments"]'),
				progressIndicatorElement = $.progressIndicator();
			if (searchTextDom.data('container') === 'widget' && !searchTextDom.val()) {
				let request = widgetContainer.data('url');
				AppConnector.request(request).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					detailContentsHolder.find('.js-comments-container').html(data);
				});
			} else {
				let hierarchy = [],
					limit = '',
					isWidget = false;
				if (searchTextDom.data('container') === 'widget') {
					(limit = widgetContainer.data('limit')), (isWidget = true);
					widgetContainer.find('.js-hierarchy-comments:checked').each(function () {
						hierarchy.push($(this).val());
					});
				} else {
					detailContentsHolder.find('.js-detail-hierarchy-comments:checked').each(function () {
						hierarchy.push($(this).val());
					});
				}
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					mode: 'showSearchComments',
					hierarchy: hierarchy.join(','),
					limit: limit,
					record: app.getRecordId(),
					search_key: searchTextDom.val(),
					is_widget: isWidget,
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (!searchTextDom.val()) {
						detailContentsHolder.html(data);
					} else {
						detailContentsHolder.find('.js-comments-body').html(data);
					}
				});
			}
		},
		/**
		 * Register hierarchy comments buttons
		 * @param {jQuery} widgetContainer
		 */
		registerCommentEventsInDetail(widgetContainer) {
			new App.Fields.Text.Completions($('.js-completions').eq(0), { emojiPanel: false });
			widgetContainer.on('change', '.js-hierarchy-comments', function (e) {
				let hierarchy = [];
				widgetContainer.find('.js-hierarchy-comments').each(function () {
					if ($(this).is(':checked')) {
						hierarchy.push($(this).val());
					}
				});
				let progressIndicatorElement = $.progressIndicator();
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					mode: 'showRecentComments',
					hierarchy: hierarchy.join(','),
					record: app.getRecordId(),
					limit: widgetContainer.find('.widgetContentBlock').data('limit'),
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					let widgetDataContainer = widgetContainer.find('.js-detail-widget-content');
					widgetDataContainer.html(data);
					App.Fields.Picklist.showSelect2ElementView(widgetDataContainer.find('.select2'));
				});
			});
		},
		registerMailPreviewWidget: function (container) {
			const self = this;
			container.on('click', '.showMailBody', (e) => {
				let row = $(e.currentTarget).closest('.row'),
					mailBody = row.find('.mailBody'),
					mailTeaser = row.find('.mailTeaser');
				mailBody.toggleClass('d-none');
				mailTeaser.toggleClass('d-none');
			});
			container.find('[name="mail-type"]').on('change', function (e) {
				self.loadMailPreviewWidget(container);
			});
			container.find('[name="mailFilter"]').on('change', function (e) {
				self.loadMailPreviewWidget(container);
			});
			container.on('click', '.showMailsModal', (e) => {
				let url = $(e.currentTarget).data('url');
				url += '&type=' + container.find('[name="mail-type"]').val();
				if (container.find('[name="mailFilter"]').length > 0) {
					url += '&mailFilter=' + container.find('[name="mailFilter"]').val();
				}
				let progressIndicatorElement = jQuery.progressIndicator();
				app.showModalWindow('', url, (data) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					self.registerMailPreviewWidget(data);
					Vtiger_Index_Js.registerMailButtons(data);
					data.find('.expandAllMails').click();
				});
			});
			container.find('.expandAllMails').on('click', function (e) {
				container.find('.mailBody').removeClass('d-none');
				container.find('.mailTeaser').addClass('d-none');
				container.find('.showMailBody .js-toggle-icon').removeClass('fa-caret-down').addClass('fa-caret-up');
			});
			container.find('.collapseAllMails').on('click', function (e) {
				container.find('.mailBody').addClass('d-none');
				container.find('.mailTeaser').removeClass('d-none');
				container.find('.showMailBody .js-toggle-icon').removeClass('fa-caret-up').addClass('fa-caret-down');
			});
		},
		loadMailPreviewWidget: function (widgetContent) {
			var thisInstance = this;
			var widgetDataContainer = widgetContent.find('.js-detail-widget-content');
			var recordId = $('#recordId').val();
			var progress = widgetDataContainer.progressIndicator();
			var params = {};
			params['module'] = 'OSSMailView';
			params['view'] = 'Widget';
			params['smodule'] = $('#module').val();
			params['srecord'] = recordId;
			params['mode'] = 'showEmailsList';
			params['type'] = $('[name="mail-type"]').val();
			params['mailFilter'] = $('[name="mailFilter"]').val();
			AppConnector.request(params).done(function (data) {
				widgetDataContainer.html(data);
				app.event.trigger('DetailView.Widget.AfterLoad', widgetDataContainer, params['module'], thisInstance);
				progress.progressIndicator({ mode: 'hide' });
			});
		},
		registerEmailEvents: function (detailContentsHolder) {
			Vtiger_Index_Js.registerMailButtons(detailContentsHolder);
		},
		registerMapsEvents: function (container) {
			if (container.find('#coordinates').length) {
				var mapView = new OpenStreetMap_Map_Js();
				mapView.registerDetailView(container);
			}
		},
		registerSocialMediaEvents(container) {
			let socialMediaContainer = container.find('.tpl-Detail-SocialMedia');
			if (socialMediaContainer.length) {
			}
		},
		registerShowSummary: function (container) {
			container.on('click', '.showSummaryRelRecord', function (e) {
				var currentTarget = $(e.currentTarget);
				var id = currentTarget.data('id');
				var summaryView = container.find('.summaryRelRecordView' + id);
				container.find('.listViewEntriesTable').css('display', 'none');
				summaryView.show();
			});
			container.on('click', '.hideSummaryRelRecordView', function (e) {
				var summaryView = container.find('.summaryRelRecordView');
				container.find('.listViewEntriesTable').css('display', 'table');
				summaryView.hide();
			});
		},
		/**
		 * Show confirmation on event click
		 * @param {jQuery} element
		 * @param {string} picklistName
		 */
		showProgressConfirmation(element, picklistName) {
			const self = this;
			let picklistValue = $(element).data('picklistValue');
			Vtiger_Helper_Js.showConfirmationBox({
				title: $(element).data('picklistLabel'),
				message: app.vtranslate('JS_CHANGE_VALUE_CONFIRMATION'),
			}).done(() => {
				const progressIndicatorElement = $.progressIndicator();
				self.saveFieldValues({
					value: picklistValue,
					field: picklistName,
				})
					.done(() => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						window.location.reload();
					})
					.fail(function (error, err) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.errorLog(error, err);
					});
			});
		},
		/**
		 * Change status from progress
		 */
		registerProgress() {
			const self = this;
			$('.js-header-progress-bar').each((index, element) => {
				let picklistName = $(element).data('picklistName');
				$(element)
					.find('.js-access')
					.on('click', (e) => {
						self.showProgressConfirmation(e.currentTarget, picklistName);
					});
			});
		},
		loadChat() {
			let chatVue = $('#ChatRecordRoomVue', this.detailViewContentHolder);
			if (chatVue.length) {
				let chatContainer = this.detailViewContentHolder.find('.js-chat-container');
				const padding = 10;
				chatContainer.height(
					$(document).height() - chatContainer.offset().top - $('.js-footer').outerHeight() - padding
				);
				window.ChatRecordRoomVueComponent.mount({
					el: '#ChatRecordRoomVue',
				});
			}
		},
		registerChat() {
			if (window.ChatRecordRoomVueComponent !== undefined) {
				this.loadChat();
				app.event.on('DetailView.Tab.AfterLoad', (e, data, instance) => {
					instance.detailViewContentHolder.ready(() => {
						this.loadChat();
					});
				});
			}
		},
		registerBasicEvents: function () {
			var thisInstance = this;
			var detailContentsHolder = thisInstance.getContentHolder();
			var selectedTabElement = thisInstance.getSelectedTab();
			//register all the events for summary view container

			if (this.getSelectedTab().data('labelKey') === 'ModComments') {
				new App.Fields.Text.Completions(detailContentsHolder.find('.js-completions'), { emojiPanel: false });
			}
			thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
			thisInstance.registerCommentEvents(detailContentsHolder);
			thisInstance.registerEmailEvents(detailContentsHolder);
			thisInstance.registerMapsEvents(detailContentsHolder);
			thisInstance.registerSocialMediaEvents(detailContentsHolder);
			thisInstance.registerSubProducts(detailContentsHolder);
			thisInstance.registerCollapsiblePanels(detailContentsHolder);
			App.Fields.Date.register(detailContentsHolder);
			App.Fields.DateTime.register(detailContentsHolder);
			App.Fields.MultiImage.register(detailContentsHolder);
			//Attach time picker event to time fields
			app.registerEventForClockPicker();
			App.Fields.Picklist.showSelect2ElementView(detailContentsHolder.find('select.select2'));
			new App.Fields.Text.Editor(detailContentsHolder, { toolbar: 'Min' });
			detailContentsHolder.on('click', '#detailViewNextRecordButton', function (e) {
				var url = selectedTabElement.data('url');
				var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
				var requestedPage = parseInt(currentPageNum) + 1;
				var nextPageUrl = url + '&page=' + requestedPage;
				thisInstance.loadContents(nextPageUrl);
			});
			detailContentsHolder.on('click', '#detailViewPreviousRecordButton', function (e) {
				var url = selectedTabElement.data('url');
				var currentPageNum = thisInstance.getRelatedListCurrentPageNum();
				var requestedPage = parseInt(currentPageNum) - 1;
				var params = {};
				var nextPageUrl = url + '&page=' + requestedPage;
				thisInstance.loadContents(nextPageUrl);
			});
			detailContentsHolder.on('click', 'div.detailViewTable div.fieldValue:not(.is-edit-active)', function (e) {
				let target = $(e.target);
				if (target.closest('a').hasClass('btnNoFastEdit') || target.hasClass('btnNoFastEdit')) return;
				let currentTdElement = jQuery(e.currentTarget);
				currentTdElement.addClass('is-edit-active');
				thisInstance.ajaxEditHandling(currentTdElement);
			});
			detailContentsHolder.on('click', 'div.recordDetails span.squeezedWell', function (e) {
				var currentElement = jQuery(e.currentTarget);
				var relatedLabel = currentElement.data('reference');
				jQuery('.detailViewInfo .related .nav > li[data-reference="' + relatedLabel + '"]').trigger('click');
			});
			detailContentsHolder.on('click', '.relatedPopup', function (e) {
				var editViewObj = new Vtiger_Edit_Js();
				editViewObj.showRecordsList(e);
				return false;
			});
			detailContentsHolder.on('click', '.viewThread', function (e) {
				let currentTarget = jQuery(e.currentTarget),
					currentTargetParent = currentTarget.parent(),
					commentActionsBlock = currentTarget.closest('.js-comment-actions'),
					currentCommentBlock = currentTarget.closest('.js-comment-details'),
					ulElements = currentCommentBlock.find('ul');
				if (ulElements.length > 0) {
					ulElements.show();
					commentActionsBlock.find('.hideThreadBlock').show();
					currentTargetParent.hide();
					return;
				}
				var commentId = currentTarget
					.closest('.js-comment-div')
					.find('.js-comment-info-header')
					.data('commentid');
				thisInstance.getChildComments(commentId).done(function (data) {
					jQuery(data).appendTo(jQuery(e.currentTarget).closest('.js-comment-details'));
					commentActionsBlock.find('.hideThreadBlock').show();
					currentTargetParent.hide();
				});
			});
			detailContentsHolder.on('click', '.js-view-parent-thread', function (e) {
				let currentTarget = jQuery(e.currentTarget),
					currentTargetParent = currentTarget.parent(),
					commentId = currentTarget
						.closest('.js-comment-div')
						.find('.js-comment-info-header')
						.data('commentid');
				thisInstance.getParentComments(commentId).done(function (data) {
					$(e.currentTarget.closest('.js-comment-details')).html(data);
					currentTarget.closest('.js-comment-actions').find('.hideThreadBlock').show();
					currentTargetParent.hide();
				});
			});
			detailContentsHolder.on('click', '.hideThread', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var currentTargetParent = currentTarget.parent();
				var commentActionsBlock = currentTarget.closest('.js-comment-actions');
				var currentCommentBlock = currentTarget.closest('.js-comment-details');
				currentCommentBlock.find('ul').hide();
				currentTargetParent.hide();
				commentActionsBlock.find('.js-view-thread-block').show();
			});
			detailContentsHolder.on('click', '.detailViewThread', function (e) {
				var recentCommentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentCommentsTabLabel);
				var commentId = jQuery(e.currentTarget)
					.closest('.js-comment-single')
					.find('.js-comment-info-header')
					.data('commentid');
				var commentLoad = function (data) {
					window.location.href = window.location.href + '#' + commentId;
				};
				recentCommentsTab.trigger('click', { commentid: commentId, callback: commentLoad });
			});
			detailContentsHolder.on('click', '.moreRecentRecords', function (e) {
				e.preventDefault();
				var recentCommentsTab = thisInstance.getTabByModule(
					$(this).data('label-key'),
					$(this).data('relation-id')
				);
				if (recentCommentsTab.length) {
					recentCommentsTab.trigger('click');
				} else {
					let currentTarget = $(e.currentTarget),
						container = currentTarget.closest("[class^='widgetContainer_']");
					if (container.length) {
						let page = container.find('[name="page"]:last').val(),
							url = container.data('url');
						currentTarget.prop('disabled', true);
						url = url.replace('&page=1', '&page=' + ++page);
						AppConnector.request(url).done(function (data) {
							let dataObj = $(data),
								containerTable = container.find('.js-detail-widget-content table');
							currentTarget.prop('disabled', false).addClass('d-none');
							container.find('[name="page"]:last').val(dataObj.find('[name="page"]').val());
							if (containerTable.length) {
								containerTable.append(dataObj.find('tbody tr'));
								if (dataObj.find('.moreRecentRecords').length) {
									currentTarget.removeClass('d-none');
								}
							} else {
								container.find('.js-detail-widget-content').append(dataObj);
							}
						});
					}
				}
			});
			detailContentsHolder.on('change', '.relatedHistoryTypes', function (e) {
				let widgetContent = jQuery(this).closest('.widgetContentBlock').find('.widgetContent'),
					progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true,
							elementToBlock: widgetContent,
						},
					});
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					record: app.getRecordId(),
					mode: 'showRecentRelation',
					page: 1,
					limit: widgetContent.find('.js-relatedHistoryPageLimit').val(),
					type: $(e.currentTarget).val(),
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					widgetContent.find('#relatedHistoryCurrentPage').remove();
					widgetContent.find('#moreRelatedUpdates').remove();
					widgetContent.html(data);
					Vtiger_Index_Js.registerMailButtons(widgetContent);
				});
			});
			detailContentsHolder.on('click', '.moreProductsService', function () {
				jQuery('.related .mainNav[data-reference="ProductsAndServices"]:not(.d-none)').trigger('click');
			});
			detailContentsHolder.on('click', '.moreRelatedUpdates', function () {
				var widgetContainer = jQuery(this).closest('.widgetContentBlock');
				var widgetContent = widgetContainer.find('.widgetContent');
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true,
						elementToBlock: widgetContent,
					},
				});
				var currentPage = widgetContent.find('#relatedHistoryCurrentPage').val();
				var nextPage = parseInt(currentPage) + 1;
				var types = widgetContainer.find('.relatedHistoryTypes').val();
				var pageLimit = widgetContent.find('#relatedHistoryPageLimit').val();
				AppConnector.request({
					module: app.getModuleName(),
					view: 'Detail',
					record: app.getRecordId(),
					mode: 'showRecentRelation',
					page: nextPage,
					limit: pageLimit,
					type: types,
				}).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					widgetContent.find('#relatedHistoryCurrentPage').remove();
					widgetContent.find('#moreRelatedUpdates').remove();
					widgetContent.find('#relatedUpdates').append(data);
				});
			});
			detailContentsHolder.on('click', '.moreRecentUpdates', function (e) {
				const container = $(e.currentTarget).closest('.recentActivitiesContainer');
				let newChange = container.find('#newChange').val(),
					nextPage = parseInt(container.find('#updatesCurrentPage').val()) + 1,
					url;
				if (container.closest('.js-detail-widget').length) {
					let data = thisInstance.getFiltersData(
						e,
						{
							page: nextPage,
							tab_label: 'LBL_UPDATES',
							newChange: newChange,
						},
						container.find('#updates')
					);
					url = data['params'];
				} else {
					url = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel).data('url');
					url = url.replace('&page=1', '&page=' + nextPage) + '&skipHeader=true&newChange=' + newChange;
					if (url.indexOf('&whereCondition') === -1) {
						let switchBtn = jQuery('.active .js-switch--recentActivities');
						url +=
							'&whereCondition=' +
							(typeof switchBtn.data('on-val') === 'undefined'
								? switchBtn.data('off-val')
								: switchBtn.data('on-val'));
					}
				}
				AppConnector.request(url).done(function (data) {
					let dataContainer = jQuery(data);
					container.find('#newChange').val(dataContainer.find('#newChange').val());
					container.find('#updatesCurrentPage').val(dataContainer.find('#updatesCurrentPage').val());
					container.find('.js-more-link').html(dataContainer.find('.js-more-link').html());
					container.find('#updates ul').append(dataContainer.find('#updates ul').html());
					app.event.trigger('DetailView.UpdatesWidget.AddMore', data, thisInstance);
				});
			});
			detailContentsHolder.on('click', '.btnChangesReviewedOn', function (e) {
				var progressInstance = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true,
					},
				});
				var url = 'index.php?module=ModTracker&action=ChangesReviewedOn&record=' + app.getRecordId();
				AppConnector.request(url).done(function (data) {
					progressInstance.progressIndicator({ mode: 'hide' });
					jQuery(e.currentTarget).parent().remove();
					thisInstance
						.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel)
						.find('.count.badge')
						.text('');
					if (selectedTabElement.data('labelKey') == thisInstance.detailViewRecentUpdatesTabLabel) {
						thisInstance.reloadTabContent();
					} else if (selectedTabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
						var updatesWidget = detailContentsHolder.find("[data-type='Updates']");
						if (updatesWidget.length > 0) {
							var params = thisInstance.getFiltersData(updatesWidget);
							thisInstance.loadWidget(updatesWidget, params['params']);
						}
					}
				});
			});
			detailContentsHolder.on('click', '.moreRecentDocuments', function () {
				var recentDocumentsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentDocumentsTabLabel);
				recentDocumentsTab.trigger('click');
			});
			detailContentsHolder.on('click', '.moreRecentActivities', function (e) {
				var currentTarget = $(e.currentTarget);
				currentTarget.prop('disabled', true);
				var container = currentTarget.closest('.activityWidgetContainer');
				var page = container.find('.currentPage').val();
				let records = container.find('.countActivities').val();
				let data = thisInstance.getFiltersData(e, { page: ++page });
				AppConnector.request({
					type: 'POST',
					async: false,
					dataType: 'html',
					data: data['params'],
				}).done(function (data) {
					currentTarget.prop('disabled', false);
					currentTarget.addClass('d-none');
					container.find('.currentPage').remove();
					container.find('.countActivities').remove();
					container.find('.js-detail-widget-content').append(data);
					let newRecords = container.find('.countActivities').val();
					container.find('.countActivities').val(parseInt(newRecords) + parseInt(records));
					thisInstance.reloadWidgetActivitesStats(container);
				});
			});
			detailContentsHolder.on('click', '.widgetFullscreen', function (e) {
				var currentTarget = $(e.currentTarget);
				var widgetContentBlock = currentTarget.closest('.widgetContentBlock');
				var url = widgetContentBlock.data('url');
				url = url.replace('&view=Detail&', '&view=WidgetFullscreen&');
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true,
					},
				});
				app.showModalWindow(null, 'index.php?' + url, function (modal) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
			thisInstance.registerEventForRelatedList();
			thisInstance.registerBlockAnimationEvent();
			thisInstance.registerMailPreviewWidget(
				detailContentsHolder.find('.widgetContentBlock[data-type="EmailList"]')
			);
			thisInstance.registerMailPreviewWidget(
				detailContentsHolder.find('.widgetContentBlock[data-type="HistoryRelation"]')
			);
			detailContentsHolder
				.find('.js-switch--recentActivities')
				.off()
				.on('change', function (e) {
					const currentTarget = jQuery(e.currentTarget),
						tabElement = thisInstance.getTabByLabel(thisInstance.detailViewRecentUpdatesTabLabel),
						variableName = currentTarget.data('urlparams'),
						valueOn = $(this).data('on-val'),
						valueOff = $(this).data('off-val');
					let url = tabElement.data('url');
					url = url
						.replace('&' + variableName + '=' + valueOn, '')
						.replace('&' + variableName + '=' + valueOff, '');
					if (typeof currentTarget.data('on-val') !== 'undefined') {
						url += '&' + variableName + '=' + valueOn;
					} else if (typeof currentTarget.data('off-val') !== 'undefined') {
						url += '&' + variableName + '=' + valueOff;
					}
					tabElement.data('url', url);
					tabElement.trigger('click');
				});
			app.registerIframeEvents(detailContentsHolder);
		},
		reloadWidgetActivitesStats: function (container) {
			let countElement = container.find('.countActivities');
			let totalElement = container.find('.totaltActivities');
			let switchBtn = container.find('.active .js-switch');
			if (!switchBtn.length) {
				switchBtn = container.find('.js-switch.previousMark');
			} else {
				container.find('.js-switch').removeClass('previousMark');
				switchBtn.addClass('previousMark');
			}
			container.find('.js-switch').toggleClass('previousMark');
			if (!countElement.length || !totalElement.length || totalElement.val() === '') {
				return false;
			}
			let stats = ' (' + countElement.val() + '/' + totalElement.val() + ')';
			let switchBtnParent = switchBtn.parent();
			let text = switchBtn.data('basic-text') + stats;
			switchBtnParent.removeTextNode();
			switchBtnParent.append(text);
		},
		refreshCommentContainer: function (commentId) {
			var thisInstance = this;
			var commentContainer = $('.commentsBody');
			var params = {
				module: app.getModuleName(),
				view: 'Detail',
				record: thisInstance.getRecordId(),
				mode: 'showThreadComments',
				commentid: commentId,
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true,
					elementToBlock: commentContainer,
				},
			});
			AppConnector.request(params).done(function (data) {
				progressIndicatorElement.progressIndicator({ mode: 'hide' });
				commentContainer.html(data);
			});
		},
		updateRecordsPDFTemplateBtn: function (form) {
			const thisInstance = this;
			let btnToolbar = $('.js-btn-toolbar .js-pdf');
			if (btnToolbar.length) {
				AppConnector.request({
					data: {
						module: app.getModuleName(),
						action: 'PDF',
						mode: 'hasValidTemplate',
						record: app.getRecordId(),
						view: app.getViewName(),
					},
					dataType: 'json',
				})
					.done(function (data) {
						if (data['result'].valid === false) {
							btnToolbar.addClass('d-none');
						} else {
							btnToolbar.removeClass('d-none');
						}
					})
					.fail(function (data, err) {
						app.errorLog(data, err);
					});
			}
		},
		updateWindowHeight: function (currentHeight, frame) {
			frame.height(currentHeight);
		},
		loadSubProducts: function (parentRow) {
			const thisInstance = this;
			let recordId = parentRow.data('product-id'),
				subProrductParams = {
					module: 'Products',
					action: 'SubProducts',
					record: recordId,
				};
			AppConnector.request(subProrductParams).done(function (data) {
				let responseData = data.result;
				thisInstance.addSubProducts(parentRow, responseData);
			});
		},
		addSubProducts: function (parentRow, responseData) {
			let subProductsContainer = $('.js-subproducts-container ul', parentRow);
			for (let id in responseData) {
				let productText = $('<li>').text(responseData[id]);
				subProductsContainer.append(productText);
			}
		},
		registerSubProducts: function (container) {
			const thisInstance = this;
			container.find('.inventoryItems .js-inventory-row').each(function (index) {
				thisInstance.loadSubProducts($(this), false);
			});
		},
		registerCollapsiblePanels(detailViewContainer) {
			const panels = detailViewContainer.find('.js-detail-widget-collapse');
			const storageName = `yf-${app.getModuleName()}-detail-widgets`;
			if (Quasar.plugins.LocalStorage.has(storageName)) {
				this.setPanels({ panels, storageName });
			} else {
				panels.collapse('show');
				let panelsStorage = {};
				panels.each((i, item) => {
					panelsStorage[item.dataset.storageId] = 'shown';
				});
				Quasar.plugins.LocalStorage.set(storageName, panelsStorage);
			}
			panels.on('hidden.bs.collapse shown.bs.collapse', (e) => {
				this.updatePanelsStorage({ id: e.target.dataset.storageKey, type: e.type, storageName });
			});
			panels.on('hide.bs.collapse show.bs.collapse', function (e) {
				$(e.currentTarget).siblings('.js-detail-widget-header').toggleClass('collapsed');
			});
		},
		setPanels({ panels, storageName }) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem(storageName);
			panels.each((i, item) => {
				if (
					panelsStorage[item.dataset.storageKey] === 'shown' ||
					undefined === panelsStorage[item.dataset.storageKey]
				) {
					$(item).collapse('show');
					$(item).siblings('.js-detail-widget-header').toggleClass('collapsed');
				}
			});
		},
		updatePanelsStorage({ id, type, storageName }) {
			const panelsStorage = Quasar.plugins.LocalStorage.getItem(storageName);
			panelsStorage[id] = type;
			Quasar.plugins.LocalStorage.set(storageName, panelsStorage);
		},
		registerEvents: function () {
			//this.triggerDisplayTypeEvent();
			this.registerHelpInfo();
			this.registerSendSmsSubmitEvent();
			this.registerAjaxEditEvent();
			this.registerRelatedRowClickEvent();
			this.registerBlockStatusCheckOnLoad();
			this.registerEmailFieldClickEvent();
			this.registerPhoneFieldClickEvent();
			this.registerEventForRelatedTabClick();
			Vtiger_Helper_Js.showHorizontalTopScrollBar();
			this.registerUrlFieldClickEvent();
			var detailViewContainer = jQuery('div.detailViewContainer');
			if (detailViewContainer.length <= 0) {
				// Not detail view page
				return;
			}

			this.registerSetReadRecord(detailViewContainer);
			this.registerEventForPicklistDependencySetup(this.getForm());
			this.getForm().validationEngine(app.validationEngineOptionsForRecord);
			this.loadWidgetsEvents();
			this.loadWidgets();
			this.registerBasicEvents();
			this.registerEventForTotalRecordsCount();
			this.registerProgress();
			this.registerChat(detailViewContainer);
		},
	}
);
