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

Vtiger_List_Js(
	'Rss_List_Js',
	{},
	{
		/**
		 * Function get the height of the document
		 * @return <integer> height
		 */
		getDocumentHeight: function () {
			return jQuery(document).height();
		},

		registerRssButtonClickEvent: function (container) {
			var thisInstance = this;
			container.on('click', '.rssAddButton', function (e) {
				thisInstance.showRssModal('getRssAddForm').done(function (data) {
					var callBackFunction = function (data) {
						var params = app.validationEngineOptions;
						var form = data.find('#rssAddForm');
						params.onValidationComplete = function (form, valid) {
							if (valid) {
								thisInstance.rssFeedSave(form);
							}
							return false;
						};
						form.validationEngine(params);
					};
					app.showModalWindow(data, callBackFunction);
				});
			});
			container.on('click', '.changeFeedSource', function (e) {
				thisInstance.showRssModal('getRssWidget').done(function (data) {
					var callBackFunction = function (data) {
						data.on('click', '.rssLink', function (e) {
							var element = jQuery(e.currentTarget);
							var id = element.data('id');
							thisInstance.getRssFeeds(id).done(function () {
								app.hideModalWindow();
							});
						});
					};
					app.showModalWindow(data, callBackFunction);
				});
			});
		},
		breadCrumbsFilter: function (text) {
			return;
		},

		/**
		 * Function show rssAddForm model
		 */
		showRssModal: function (mode) {
			var aDeferred = jQuery.Deferred();
			var progressInstance = jQuery.progressIndicator({});
			var actionParams = {
				module: app.getModuleName(),
				view: 'ViewTypes',
				mode: mode
			};
			AppConnector.request(actionParams).done(
				function (data) {
					progressInstance.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {}
			);
			return aDeferred.promise();
		},

		/**
		 * Function to save rss feed
		 * @parm form
		 */
		rssFeedSave: function (form) {
			var thisInstance = this;
			var data = form.serializeFormData();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {
				module: app.getModuleName(),
				action: 'Save',
				feedurl: data.feedurl
			};
			AppConnector.request(params).done(function (result) {
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
				if (result.result.success) {
					app.hideModalWindow();
					thisInstance.getRssFeeds(result.result.id);
				} else {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate(result.result.message),
						type: 'error'
					};
					app.showNotify(params);
				}
			});
		},

		/**
		 * Function to get the feeds for specific id
		 * @param <integer> id
		 */
		getRssFeeds: function (id) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();
			var container = thisInstance.getListViewContainer();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var params = {
				module: app.getModuleName(),
				view: 'List',
				id: id
			};
			AppConnector.requestPjax(params).done(function (data) {
				aDeferred.resolve(data);
				container.find('#listViewContents').html(data);
				thisInstance.setFeedContainerHeight(container);
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
			});

			return aDeferred.promise();
		},

		/**
		 * Function to get the height of the Feed Container
		 * @param container
		 */
		setFeedContainerHeight: function (container) {
			var height = this.getDocumentHeight() / 1.5;
			container.find('.feedListContainer').height(height);
		},

		/**
		 * Function to register the click of feeds
		 * @param container
		 */
		registerFeedClickEvent: function (container) {
			var thisInstance = this;
			container.on('click', '.feedLink', function (e) {
				var element = jQuery(e.currentTarget);
				var url = element.data('url');
				var frameElement = thisInstance.getFrameElement(url);
				container.find('.feedFrame').html(frameElement);
			});
		},

		/**
		 * Function to get the iframe element
		 * @param <string> url
		 * @retrun <element> frameElement
		 */
		getFrameElement: function (url) {
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var frameElement = jQuery('<iframe>', {
				id: 'feedFrame',
				scrolling: 'auto',
				width: '100%',
				height: this.getDocumentHeight() / 2
			});
			frameElement.addClass('table-bordered');
			this.getHtml(url).done(function (html) {
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
				var frame = frameElement[0].contentDocument;
				frame.open();
				frame.write(html);
				frame.close();
			});

			return frameElement;
		},

		/**
		 * Function to get the html contents from url
		 * @param <string> url
		 * @return <string> html contents
		 */
		getHtml: function (url) {
			var aDeferred = jQuery.Deferred();
			var params = {
				module: app.getModuleName(),
				action: 'GetHtml',
				url: url
			};
			AppConnector.request(params).done(function (data) {
				aDeferred.resolve(data.result.html);
			});

			return aDeferred.promise();
		},

		/**
		 * Function to register record delete event
		 */
		registerDeleteRecordClickEvent: function () {
			var container = this.getListViewContainer();
			var thisInstance = this;
			container.on('click', '#deleteButton', function (e) {
				thisInstance.deleteRecord(container);
			});
		},

		/**
		 * Function to delete the record
		 */
		deleteRecord: function (container) {
			var thisInstance = this;
			var recordId = container.find('#recordId').val();
			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({ message: message }).done(
				function (e) {
					var module = app.getModuleName();
					var postData = {
						module: module,
						action: 'DeleteAjax',
						record: recordId
					};
					var deleteMessage = app.vtranslate('JS_RECORD_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						message: deleteMessage,
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request(postData).done(
						function (data) {
							progressIndicatorElement.progressIndicator({
								mode: 'hide'
							});
							if (data.success) {
								thisInstance.getRssFeeds();
							} else {
								var params = {
									text: app.vtranslate(data.error.message),
									title: app.vtranslate('JS_LBL_PERMISSION'),
									type: 'error'
								};
								app.showNotify(params);
							}
						},
						function (error, err) {}
					);
				},
				function (error, err) {}
			);
		},

		/**
		 * Function to register make default button click event
		 */
		registerMakeDefaultClickEvent: function (container) {
			var thisInstance = this;
			container.on('click', '#makeDefaultButton', function () {
				thisInstance.makeDefault(container);
			});
		},

		/**
		 * Function to make a record as default rss feed
		 */
		makeDefault: function (container) {
			var recordId = container.find('#recordId').val();
			var module = app.getModuleName();
			var postData = {
				module: module,
				action: 'MakeDefaultAjax',
				record: recordId
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request(postData).done(function (data) {
				progressIndicatorElement.progressIndicator({
					mode: 'hide'
				});
				if (data.success) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate(data.result.message),
						type: 'info'
					};
					app.showNotify(params);
				} else {
					app.showNotify({
						text: app.vtranslate(data.error.message),
						title: app.vtranslate('JS_LBL_PERMISSION'),
						type: 'error'
					});
				}
			});
		},

		registerEvents: function () {
			this._super();
			var container = this.getListViewContainer();
			this.registerRssButtonClickEvent(container);
			this.registerFeedClickEvent(container);
			this.registerMakeDefaultClickEvent(container);
			this.setFeedContainerHeight(container);
			this.registerDeleteRecordClickEvent();
		}
	}
);
