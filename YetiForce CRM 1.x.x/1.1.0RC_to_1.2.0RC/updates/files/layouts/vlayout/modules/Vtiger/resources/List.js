    /*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_List_Js",{

	listInstance : false,

	getRelatedModulesContainer : false,

	massEditPreSave : 'Vtiger.MassEdit.PreSave',

   getInstance: function(){
		if(Vtiger_List_Js.listInstance == false){
			var module = app.getModuleName();
			var parentModule = app.getParentModuleName();
			if(parentModule == 'Settings'){
				var moduleClassName = parentModule+"_"+module+"_List_Js";
				if(typeof window[moduleClassName] == 'undefined'){
					moduleClassName = module+"_List_Js";
				}
				var fallbackClassName = parentModule+"_Vtiger_List_Js";
				if(typeof window[fallbackClassName] == 'undefined') {
					fallbackClassName = "Vtiger_List_Js";
				}
			} else {
				moduleClassName = module+"_List_Js";
				fallbackClassName = "Vtiger_List_Js";
			}
			if(typeof window[moduleClassName] != 'undefined'){
				var instance = new window[moduleClassName]();
			}else{
				var instance = new window[fallbackClassName]();
			}
			Vtiger_List_Js.listInstance = instance;
			return instance;
		}
		return Vtiger_List_Js.listInstance;
	},
		/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail : function(massActionUrl, module, params){
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = listInstance.getDefaultParams();

            delete postData.module;
            delete postData.view;
            delete postData.parent;
            postData.selected_ids = selectedIds;
            postData.excluded_ids = excludedIds;
            postData.viewname = cvId;
            if(params){
				jQuery.extend(postData,params);
            }
			var actionParams = {
				"type":"POST",
				"url":massActionUrl,
				"dataType":"html",
				"data" : postData
			};

			Vtiger_Index_Js.showComposeEmailPopup(actionParams);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},
	/*
	 * function to trigger Send Sms
	 * @params: send email url , module name.
	 */
	triggerSendSms : function(massActionUrl, module){
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var progressIndicatorElement = jQuery.progressIndicator();
			Vtiger_Helper_Js.checkServerConfig(module).then(function(data){
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				if(data == true){
					Vtiger_List_Js.triggerMassAction(massActionUrl);
				} else {
					alert(app.vtranslate('JS_SMS_SERVER_CONFIGURATION'));
				}
			});
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	triggerTransferOwnership : function(massActionUrl){
		var thisInstance = this;
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var progressIndicatorElement = jQuery.progressIndicator();
			thisInstance.getRelatedModulesContainer = false;
			var actionParams = {
				"type":"POST",
				"url":massActionUrl,
				"dataType":"html",
				"data" : {}
			};
			AppConnector.request(actionParams).then(
				function(data) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					if(data) {
						var callback = function(data) {
							var params = app.validationEngineOptions;
							params.onValidationComplete = function(form, valid){
								if(valid){
									thisInstance.transferOwnershipSave(form)
								}
								return false;
							}
							jQuery('#changeOwner').validationEngine(app.validationEngineOptions);
						}
						app.showModalWindow(data, function(data){
							var selectElement = thisInstance.getRelatedModuleContainer();
							app.changeSelectElementView(selectElement, 'select2');
							if(typeof callback == 'function'){
								callback(data);
							}
						});
					}
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},

	transferOwnershipSave : function (form){
		var listInstance = Vtiger_List_Js.getInstance();
		var selectedIds = listInstance.readSelectedIds(true);
		var excludedIds = listInstance.readExcludedIds(true);
		var cvId = listInstance.getCurrentCvId();
		var transferOwner = jQuery('#transferOwnerId').val();
		var relatedModules = jQuery('#related_modules').val();

		var params = {
			'module': app.getModuleName(),
			'action' : 'TransferOwnership',
			"viewname" : cvId,
			"selected_ids":selectedIds,
			"excluded_ids" : excludedIds,
			'transferOwnerId' : transferOwner,
			'related_modules' : relatedModules
		}
		AppConnector.request(params).then(
			function(data) {
				if(data.success){
					app.hideModalWindow();
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY'),
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
					listInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				}
			}
		);
	},

	/*
	 * Function to get the related module container
	 */
	getRelatedModuleContainer  : function(){
		if(this.getRelatedModulesContainer == false){
			this.getRelatedModulesContainer = jQuery('#related_modules');
		}
		return this.getRelatedModulesContainer;
	},

	massDeleteRecords : function(url,instance) {
		var	listInstance = Vtiger_List_Js.getInstance();
		if(typeof instance != "undefined"){
			listInstance = instance;
		}
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {

					var deleteURL = url+'&viewname='+cvId+'&selected_ids='+selectedIds+'&excluded_ids='+excludedIds;
                    var listViewInstance = Vtiger_List_Js.getInstance();
                    var searchValue = listViewInstance.getAlphabetSearchValue();

                   	if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
                        deleteURL += '&search_key='+listViewInstance.getAlphabetSearchField();
                        deleteURL += '&search_value='+searchValue;
                        deleteURL += '&operator=s';
                    }
                    deleteURL += "&search_params="+JSON.stringify(listViewInstance.getListSearchParams());
					var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						'message' : deleteMessage,
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					});
					AppConnector.request(deleteURL).then(
						function(data){ 
							progressIndicatorElement.progressIndicator({
								'mode' : 'hide'
							});
							listInstance.postMassDeleteRecords(); 
                                                        if(data.error){ 
                                                            var  params = { 
                                                                text  : app.vtranslate(data.error.message), 
                                                                title : app.vtranslate('JS_LBL_PERMISSION') 
                                                            } 
                                                            Vtiger_Helper_Js.showPnotify(params); 
                                                        } 
						}
					);
				},
				function(error, err){
				Vtiger_List_Js.clearList();
				})
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},



	deleteRecord : function(recordId) {
		var listInstance = Vtiger_List_Js.getInstance();
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				var module = app.getModuleName();
				var postData = {
					"module": module,
					"action": "DeleteAjax",
					"record": recordId,
					"parent": app.getParentModuleName()
				}
				var deleteMessage = app.vtranslate('JS_RECORD_GETTING_DELETED');
				var progressIndicatorElement = jQuery.progressIndicator({
					'message' : deleteMessage,
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				AppConnector.request(postData).then(
					function(data){
						progressIndicatorElement.progressIndicator({
							'mode' : 'hide'
						})
						if(data.success) {
							var orderBy = jQuery('#orderBy').val();
							var sortOrder = jQuery("#sortOrder").val();
							var urlParams = {
								"viewname": data.result.viewname,
								"orderby": orderBy,
								"sortorder": sortOrder
							}
							jQuery('#recordsCount').val('');
							jQuery('#totalPageCount').text('');
							listInstance.getListViewRecords(urlParams).then(function(){
								listInstance.updatePagination();
							});
						} else {
							var  params = {
								text : app.vtranslate(data.error.message),
								title : app.vtranslate('JS_LBL_PERMISSION')
							}
							Vtiger_Helper_Js.showPnotify(params);
						}
					},
					function(error,err){

					}
				);
			},
			function(error, err){
			}
		);
	},


	triggerMassAction : function(massActionUrl,callBackFunction,beforeShowCb, css) {

		//TODO : Make the paramters as an object
		if(typeof beforeShowCb == 'undefined') {
			beforeShowCb = function(){return true;};
		}

		if(typeof beforeShowCb == 'object') {
			css = beforeShowCb;
			beforeShowCb = function(){return true;};
		}

		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var progressIndicatorElement = jQuery.progressIndicator();
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"viewname" : cvId,
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds
			};

			var listViewInstance = Vtiger_List_Js.getInstance();
			var searchValue = listViewInstance.getAlphabetSearchValue();

			if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
				postData['search_key'] = listViewInstance.getAlphabetSearchField();
				postData['search_value'] = searchValue;
				postData['operator'] = "s";
			}

			postData.search_params = JSON.stringify(listInstance.getListSearchParams());

			var actionParams = {
				"type":"POST",
				"url":massActionUrl,
				"dataType":"html",
				"data" : postData
			};

			if(typeof css == 'undefined'){
				css = {};
			}
			var css = jQuery.extend({'text-align' : 'left'},css);

			AppConnector.request(actionParams).then(
				function(data) {
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					if(data) {
						var result = beforeShowCb(data);
						if(!result) {
							return;
						}
						app.showModalWindow(data,function(data){
							if(typeof callBackFunction == 'function'){
								callBackFunction(data);
								//listInstance.triggerDisplayTypeEvent();
							}
						},css)

					}
				},
				function(error,err){
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	triggerMassEdit : function(massEditUrl) {
		var selectedCount = this.getSelectedRecordCount();
		if(selectedCount > 500) {
		    var params = {
			    title : app.vtranslate('JS_MESSAGE'),
			    text: app.vtranslate('JS_MASS_EDIT_LIMIT'),
			    animation: 'show',
			    type: 'error'
		    };
		    Vtiger_Helper_Js.showPnotify(params);
		    return;
		}
		Vtiger_List_Js.triggerMassAction(massEditUrl, function(container){
			var massEditForm = container.find('#massEdit');
			massEditForm.validationEngine(app.validationEngineOptions);
			var listInstance = Vtiger_List_Js.getInstance();
			listInstance.inactiveFieldValidation(massEditForm);
			listInstance.registerReferenceFieldsForValidation(massEditForm);
			listInstance.registerFieldsForValidation(massEditForm);
			listInstance.registerEventForTabClick(massEditForm);
			listInstance.registerRecordAccessCheckEvent(massEditForm);
			var editInstance = Vtiger_Edit_Js.getInstance();
			editInstance.registerBasicEvents(massEditForm);
			//To remove the change happended for select elements due to picklist dependency
			container.find('select').trigger('change',{'forceDeSelect':true});
			listInstance.postMassEdit(container);

			listInstance.registerSlimScrollMassEdit();
		},{'width':'65%'});
	},

	getSelectedRecordCount : function() {
	    var count;
	    var listInstance = Vtiger_List_Js.getInstance();
	    var cvId = listInstance.getCurrentCvId();
	    var selectedIdObj = jQuery('#selectedIds').data(cvId+'Selectedids');
        if(selectedIdObj != undefined){
            if(selectedIdObj != 'all') {
                count = selectedIdObj.length;
            } else {
                var excludedIdsCount = jQuery('#excludedIds').data(cvId+'Excludedids').length;
                var totalRecords = jQuery('#recordsCount').val();
                count = totalRecords - excludedIdsCount;
            }
        }
	    return count;
	},

	/*
	 * function to trigger export action
	 * returns UI
	 */
	triggerExportAction :function(exportActionUrl){
		var listInstance = Vtiger_List_Js.getInstance();
		// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
		var selectedIds = listInstance.readSelectedIds(true);
		var excludedIds = listInstance.readExcludedIds(true);
		var cvId = listInstance.getCurrentCvId();
		var pageNumber = jQuery('#pageNumber').val();

        exportActionUrl += '&selected_ids='+selectedIds+'&excluded_ids='+excludedIds+'&viewname='+cvId+'&page='+pageNumber;

        var listViewInstance = Vtiger_List_Js.getInstance();
        var searchValue = listViewInstance.getAlphabetSearchValue();

		if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
            exportActionUrl += '&search_key='+listViewInstance.getAlphabetSearchField()+'&search_value='+searchValue+'&operator=s';
        }
        exportActionUrl += '&search_params='+JSON.stringify(listInstance.getListSearchParams());
        window.location.href = exportActionUrl;
	},

	/**
	 * Function to reload list
	 */
	clearList : function() {
		jQuery('#deSelectAllMsg').trigger('click');
		jQuery("#selectAllMsgDiv").hide();
	},

	showDuplicateSearchForm : function(url) {
		var progressIndicatorElement = jQuery.progressIndicator();
		app.showModalWindow("", url, function() {
			progressIndicatorElement.progressIndicator({'mode' : 'hide'});
			Vtiger_List_Js.registerDuplicateSearchButtonEvent();
		});
	},

	/**
	 * Function that will enable Duplicate Search Find button
	 */
	registerDuplicateSearchButtonEvent : function() {
		jQuery('#fieldList').on('change', function(e) {
			var value = jQuery(e.currentTarget).val();
			var button = jQuery('#findDuplicate').find('button[type="submit"]');
			if(value != null) {
				button.attr('disabled', false);
			} else {
				button.attr('disabled', true);
			}
		})
	},

	generatePotentials : function() {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var progressIndicatorElement = jQuery.progressIndicator();
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			console.log(selectedIds);
			console.log(excludedIds);
			var params = {
				'module': 'Potentials',
				'action' : 'GeneratePotentials',
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname": cvId,
				'from_module' : app.getModuleName(),
			}
			AppConnector.request(params).then(
				function(data) {
					console.log(data.result);
					progressIndicatorElement.progressIndicator({'mode' : 'hide'});
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: data.result,
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
					Vtiger_List_Js.clearList();
					
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerListSearch : function() {
		var listInstance = Vtiger_List_Js.getInstance();
		var listViewContainer = listInstance.getListViewContentContainer();
		listViewContainer.find('button[data-trigger="listSearch"]').trigger( "click" );
	},
},{

	//contains the List View element.
	listViewContainer : false,

	//Contains list view top menu element
	listViewTopMenuContainer : false,

	//Contains list view content element
	listViewContentContainer : false,

	//Contains filter Block Element
	filterBlock : false,

	filterSelectElement : false,


	getListViewContainer : function() {
		if(this.listViewContainer == false){
			this.listViewContainer = jQuery('div.listViewPageDiv');
		}
		return this.listViewContainer;
	},

	getListViewTopMenuContainer : function(){
		if(this.listViewTopMenuContainer == false){
			this.listViewTopMenuContainer = jQuery('.listViewTopMenuDiv');
		}
		return this.listViewTopMenuContainer;
	},

	getListViewContentContainer : function(){
		if(this.listViewContentContainer == false){
			this.listViewContentContainer = jQuery('.listViewContentDiv');
		}
		return this.listViewContentContainer;
	},

	getFilterBlock : function(){
		if(this.filterBlock == false){
			var filterSelectElement = this.getFilterSelectElement();
            if(filterSelectElement.length <= 0) {
                this.filterBlock = jQuery();
            }else if(filterSelectElement.is('select')){
                this.filterBlock = filterSelectElement.data('select2').dropdown;
            }
		}
		return this.filterBlock;
	},

	getFilterSelectElement : function() {

		if(this.filterSelectElement == false) {
			this.filterSelectElement = jQuery('#customFilter');
		}
		return this.filterSelectElement;
	},


	getDefaultParams : function() {
		var pageNumber = jQuery('#pageNumber').val();
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var cvId = this.getCurrentCvId();
		var orderBy = jQuery('#orderBy').val();
		var sortOrder = jQuery("#sortOrder").val();
		var params = {
			'module': module,
			'parent' : parent,
			'page' : pageNumber,
			'view' : "List",
			'viewname' : cvId,
			'orderby' : orderBy,
			'sortorder' : sortOrder
		}

        var searchValue = this.getAlphabetSearchValue();

        if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
            params['search_key'] = this.getAlphabetSearchField();
            params['search_value'] = searchValue;
            params['operator'] = "s";
        }
        params.search_params = JSON.stringify(this.getListSearchParams());
        return params;
	},

	/*
	 * Function which will give you all the list view params
	 */
	getListViewRecords : function(urlParams) {
		var aDeferred = jQuery.Deferred();
		if(typeof urlParams == 'undefined') {
			urlParams = {};
		}

		var thisInstance = this;
		var loadingMessage = jQuery('.listViewLoadingMsg').text();
		var progressIndicatorElement = jQuery.progressIndicator({
			'message' : loadingMessage,
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});

		var defaultParams = this.getDefaultParams();
		var urlParams = jQuery.extend(defaultParams, urlParams);
		AppConnector.requestPjax(urlParams).then(
			function(data){
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				})
                var listViewContentsContainer = jQuery('#listViewContents')
                listViewContentsContainer.html(data);
                app.showSelect2ElementView(listViewContentsContainer.find('select.select2'));
				thisInstance.registerListViewSelect();
                app.changeSelectElementView(listViewContentsContainer);
                thisInstance.registerTimeListSearch(listViewContentsContainer);

                thisInstance.registerDateListSearch(listViewContentsContainer);
				thisInstance.calculatePages().then(function(data){
					//thisInstance.triggerDisplayTypeEvent();
					Vtiger_Helper_Js.showHorizontalTopScrollBar();

					var selectedIds = thisInstance.readSelectedIds();
					if(selectedIds != ''){
						if(selectedIds == 'all'){
							jQuery('.listViewEntriesCheckBox').each( function(index,element) {
								jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
							});
							jQuery('#deSelectAllMsgDiv').show();
							var excludedIds = thisInstance.readExcludedIds();
							if(excludedIds != ''){
								jQuery('#listViewEntriesMainCheckBox').attr('checked',false);
								jQuery('.listViewEntriesCheckBox').each( function(index,element) {
									if(jQuery.inArray(jQuery(element).val(),excludedIds) != -1){
										jQuery(element).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
									}
								});
							}
						} else {
							jQuery('.listViewEntriesCheckBox').each( function(index,element) {
								if(jQuery.inArray(jQuery(element).val(),selectedIds) != -1){
									jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
								}
							});
						}
						thisInstance.checkSelectAll();
					}
					aDeferred.resolve(data);

					// Let listeners know about page state change.
					app.notifyPostAjaxReady();
				});
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to calculate number of pages
	 */
	calculatePages : function() {
		var aDeferred = jQuery.Deferred();
		var element = jQuery('#totalPageCount');
		var totalPageNumber = element.text();
		if(totalPageNumber == ""){
			var totalRecordCount = jQuery('#totalCount').val();
			if(totalRecordCount != '') {
				var pageLimit = jQuery('#pageLimit').val();
				if(pageLimit == '0') pageLimit = 1;
				pageCount = Math.ceil(totalRecordCount/pageLimit);
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
				return aDeferred.promise();
			}
			this.getPageCount().then(function(data){
				var pageCount = data['result']['page'];
				if(pageCount == 0){
					pageCount = 1;
				}
				element.text(pageCount);
				aDeferred.resolve();
			});
		} else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},

	/*
	 * Function to return alerts if no records selected.
	 */
	noRecordSelectedAlert : function(){
		return alert(app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'));
	},

	massActionSave : function(form, isMassEdit){
		if(typeof isMassEdit == 'undefined') {
			isMassEdit = false;
		}
		var aDeferred = jQuery.Deferred();
		var massActionUrl = form.serializeFormData();
		if(isMassEdit) {
			var fieldsChanged = false;
            var massEditFieldList = jQuery('#massEditFieldsNameList').data('value');
			for(var fieldName in massEditFieldList){
                var fieldInfo = massEditFieldList[fieldName];
                var fieldElement = form.find('[name="'+fieldInfo.name+'"]');
                if(fieldInfo.type == "reference") {
                    //get the element which will be shown which has "_display" appended to actual field name
                    fieldElement = form.find('[name="'+fieldInfo.name+'_display"]');
                }else if(fieldInfo.type == "multipicklist" || fieldInfo.type == "sharedOwner") {
                    fieldElement = form.find('[name="'+fieldInfo.name+'[]"]');
                }

                //Not all fields will be enabled for mass edit
                if(fieldElement.length == 0) {
                    continue;
                }

                var validationElement = fieldElement.filter('[data-validation-engine]');
                //check if you have element enabled has changed
                if(validationElement.length == 0){
                    if(fieldInfo.type == "multipicklist" || fieldInfo.type == "sharedOwner") {
                        fieldName = fieldName+"[]";
                    }
                    delete massActionUrl[fieldName];
                    if(fieldsChanged != true){
                        fieldsChanged = false;
                    }
                } else {
                    fieldsChanged = true;
                }
			}
			if(fieldsChanged == false){
				Vtiger_Helper_Js.showPnotify(app.vtranslate('NONE_OF_THE_FIELD_VALUES_ARE_CHANGED_IN_MASS_EDIT'));
				form.find('[name="saveButton"]').removeAttr('disabled');
				aDeferred.reject();
				return aDeferred.promise();
			}
			//on submit form trigger the massEditPreSave event
			var massEditPreSaveEvent = jQuery.Event(Vtiger_List_Js.massEditPreSave);
			form.trigger(massEditPreSaveEvent);
			if(massEditPreSaveEvent.isDefaultPrevented()) {
				form.find('[name="saveButton"]').removeAttr('disabled');
				aDeferred.reject();
				return aDeferred.promise();
			}
		}
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
		AppConnector.request(massActionUrl).then(
			function(data) {
                progressIndicatorElement.progressIndicator({
                    'mode' : 'hide'
                });
				app.hideModalWindow();
				aDeferred.resolve(data);
			},
			function(error,err){
				app.hideModalWindow();
				aDeferred.reject(error,err);
			}
		);
		return aDeferred.promise();
	},

	/*
	 * Function to check the view permission of a record after save
	 */
	registerRecordAccessCheckEvent : function(form) {

		form.on(Vtiger_List_Js.massEditPreSave, function(e) {
			var assignedToSelectElement = form.find('[name="assigned_user_id"][data-validation-engine]');
			if(assignedToSelectElement.length > 0){
				if(assignedToSelectElement.data('recordaccessconfirmation') == true) {
					return;
				}else{
					if(assignedToSelectElement.data('recordaccessconfirmationprogress') != true) {
						var recordAccess = assignedToSelectElement.find('option:selected').data('recordaccess');
						if(recordAccess == false) {
							var message = app.vtranslate('JS_NO_VIEW_PERMISSION_AFTER_SAVE');
							Vtiger_Helper_Js.showConfirmationBox({
								'message' : message
							}).then(
								function(e) {
									assignedToSelectElement.data('recordaccessconfirmation',true);
									assignedToSelectElement.removeData('recordaccessconfirmationprogress');
									form.submit();
								},
								function(error, err){
									assignedToSelectElement.removeData('recordaccessconfirmationprogress');
									e.preventDefault();
								});
							assignedToSelectElement.data('recordaccessconfirmationprogress',true);
						} else {
							return true;
						}
					}
				}
			} else{
				return true;
			}
			e.preventDefault();
		});
	},

	checkSelectAll : function(){
		var state = true;
		jQuery('.listViewEntriesCheckBox').each(function(index,element){
			if(jQuery(element).is(':checked')){
				state = true;
			}else{
				state = false;
				return false;
			}
		});
		if(state == true){
			jQuery('#listViewEntriesMainCheckBox').attr('checked',true);
		} else {
			jQuery('#listViewEntriesMainCheckBox').attr('checked', false);
		}
	},

	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
		var recordCountVal = jQuery("#recordsCount").val();
		if(recordCountVal != ''){
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var cvId = this.getCurrentCvId();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var postData = {
				"module": module,
				"parent": parent,
				"view": "ListAjax",
				"viewname": cvId,
				"mode": "getRecordsCount"
			}

            var searchValue = this.getAlphabetSearchValue();
            if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
                postData['search_key'] = this.getAlphabetSearchField();
                postData['search_value'] = this.getAlphabetSearchValue();
                postData['operator'] = "s";
            }

            postData.search_params = JSON.stringify(this.getListSearchParams());

			AppConnector.request(postData).then(
				function(data) {
					var response = JSON.parse(data);
					jQuery("#recordsCount").val(response['result']['count']);
					count =  response['result']['count'];
					aDeferred.resolve(count);
				},
				function(error,err){

				}
			);
		}

		return aDeferred.promise();
	},

	getSelectOptionFromChosenOption : function(liElement){
		var classNames = liElement.attr("class");
		var classNamesArr = classNames.split(" ");
		var currentOptionId = '';
		jQuery.each(classNamesArr,function(index,element){
			if(element.match("^filterOptionId")){
				currentOptionId = element;
				return false;
			}
		});
		return jQuery('#'+currentOptionId);
	},

	readSelectedIds : function(decode){
		var cvId = this.getCurrentCvId();
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = cvId+'Selectedids';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes) ) {
			var selectedIds = new Array();
			this.writeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if(decode == true){
			if(typeof selectedIds == 'object'){
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
	readExcludedIds : function(decode){
		var cvId = this.getCurrentCvId();
		var exlcudedIdsElement = jQuery('#excludedIds');
		var excludedIdsDataAttr = cvId+'Excludedids';
		var excludedIdsElementDataAttributes = exlcudedIdsElement.data();
		if(!(excludedIdsDataAttr in excludedIdsElementDataAttributes)){
			var excludedIds = new Array();
			this.writeExcludedIds(excludedIds);
		}else{
			excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		}
		if(decode == true){
			if(typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
		}
		return excludedIds;
	},

	writeSelectedIds : function(selectedIds){
		var cvId = this.getCurrentCvId();
		jQuery('#selectedIds').data(cvId+'Selectedids',selectedIds);
	},

	writeExcludedIds : function(excludedIds){
		var cvId = this.getCurrentCvId();
		jQuery('#excludedIds').data(cvId+'Excludedids',excludedIds);
	},

	getCurrentCvId : function(){
		return jQuery('#customFilter').find('option:selected').data('id');
	},

	getAlphabetSearchField : function() {
		return jQuery("#alphabetSearchKey").val();
	},

	getAlphabetSearchValue : function() {
		return jQuery("#alphabetValue").val();
	},


	/*
	 * Function to check whether atleast one record is checked
	 */
	checkListRecordSelected : function(){
		var selectedIds = this.readSelectedIds();
		if(typeof selectedIds == 'object' && selectedIds.length <= 0) {
			return true;
		}
		return false;
	},

	postMassEdit : function(massEditContainer) {
		var thisInstance = this;
		massEditContainer.find('form').on('submit', function(e){
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length == 0){
				form.find('[name="saveButton"]').attr('disabled',"disabled");
			}
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length > 0){
				return;
			}
			thisInstance.massActionSave(form, true).then(
				function(data) {
					thisInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				},
				function(error,err){
				}
			)
		});
	},
	/*
	 * Function to register List view Page Navigation
	 */
	registerPageNavigationEvents : function(){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		jQuery('#listViewNextPageButton').on('click',function(){
			var pageLimit = jQuery('#pageLimit').val();
			var noOfEntries = jQuery('#noOfEntries').val();
			if(noOfEntries == pageLimit){
				var orderBy = jQuery('#orderBy').val();
				var sortOrder = jQuery("#sortOrder").val();
				var cvId = thisInstance.getCurrentCvId();
				var urlParams = {
					"orderby": orderBy,
					"sortorder": sortOrder,
					"viewname": cvId
				}
				var pageNumber = jQuery('#pageNumber').val();
				var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
				jQuery('#pageNumber').val(nextPageNumber);
				jQuery('#pageToJump').val(nextPageNumber);
				thisInstance.getListViewRecords(urlParams).then(
					function(data){
						thisInstance.updatePagination();
						aDeferred.resolve();
					},

					function(textStatus, errorThrown){
						aDeferred.reject(textStatus, errorThrown);
					}
				);
			}
			return aDeferred.promise();
		});
		jQuery('#listViewPreviousPageButton').on('click',function(){
			var aDeferred = jQuery.Deferred();
			var pageNumber = jQuery('#pageNumber').val();
			if(pageNumber > 1){
				var orderBy = jQuery('#orderBy').val();
				var sortOrder = jQuery("#sortOrder").val();
				var cvId = thisInstance.getCurrentCvId();
				var urlParams = {
					"orderby": orderBy,
					"sortorder": sortOrder,
					"viewname" : cvId
				}
				var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
				jQuery('#pageNumber').val(previousPageNumber);
				jQuery('#pageToJump').val(previousPageNumber);
				thisInstance.getListViewRecords(urlParams).then(
					function(data){
						thisInstance.updatePagination();
						aDeferred.resolve();
					},

					function(textStatus, errorThrown){
						aDeferred.reject(textStatus, errorThrown);
					}
				);
			}
		});

		jQuery('#listViewPageJump').on('click',function(e){
            if(typeof Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(jQuery('#pageToJump'))!= 'undefined') {
                var pageNo = jQuery('#pageNumber').val();
                jQuery("#pageToJump").val(pageNo);
            }
			jQuery('#pageToJump').validationEngine('hideAll');
			var element = jQuery('#totalPageCount');
			var totalPageNumber = element.text();
			if(totalPageNumber == ""){
				var totalCountElem = jQuery('#totalCount');
				var totalRecordCount = totalCountElem.val();
				if(totalRecordCount != '') {
					var recordPerPage = jQuery('#pageLimit').val();
					if(recordPerPage == '0') recordPerPage = 1;
					pageCount = Math.ceil(totalRecordCount/recordPerPage);
					if(pageCount == 0){
						pageCount = 1;
					}
					element.text(pageCount);
					return;
				}
				element.progressIndicator({});
				thisInstance.getPageCount().then(function(data){
					var pageCount = data['result']['page'];
					totalCountElem.val(data['result']['numberOfRecords']);
					if(pageCount == 0){
						pageCount = 1;
					}
					element.text(pageCount);
					element.progressIndicator({'mode': 'hide'});
			});
		}
		})

		jQuery('#listViewPageJumpDropDown').on('click','li',function(e){
			e.stopImmediatePropagation();
		}).on('keypress','#pageToJump',function(e){
			if(e.which == 13){
				e.stopImmediatePropagation();
				var element = jQuery(e.currentTarget);
				var response = Vtiger_WholeNumberGreaterThanZero_Validator_Js.invokeValidation(element);
				if(typeof response != "undefined"){
					element.validationEngine('showPrompt',response,'',"topLeft",true);
				} else {
					element.validationEngine('hideAll');
					var currentPageElement = jQuery('#pageNumber');
					var currentPageNumber = currentPageElement.val();
					var newPageNumber = parseInt(jQuery(e.currentTarget).val());
					var totalPages = parseInt(jQuery('#totalPageCount').text());
					if(newPageNumber > totalPages){
						var error = app.vtranslate('JS_PAGE_NOT_EXIST');
						element.validationEngine('showPrompt',error,'',"topLeft",true);
						return;
					}
					if(newPageNumber == currentPageNumber){
						var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER')+" "+newPageNumber;
						var params = {
							text: message,
							type: 'info'
						};
						Vtiger_Helper_Js.showMessage(params);
						return;
					}
					currentPageElement.val(newPageNumber);
					thisInstance.getListViewRecords().then(
						function(data){
							thisInstance.updatePagination();
							element.closest('.btn-group ').removeClass('open');
						},
						function(textStatus, errorThrown){
						}
					);
				}
				return false;
			}
		});
	},

	/**
	 * Function to get page count and total number of records in list
	 */
	getPageCount : function(){
		var aDeferred = jQuery.Deferred();
		var pageCountParams = this.getPageJumpParams();
		AppConnector.request(pageCountParams).then(
			function(data) {
				var response;
				if(typeof data != "object"){
					response = JSON.parse(data);
				} else{
					response = data;
				}
				aDeferred.resolve(response);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var params = this.getDefaultParams();
		params['view'] = "ListAjax";
		params['mode'] = "getPageCount";

		return params;
	},

	/**
	 * Function to update Pagining status
	 */
	updatePagination : function(){
		var previousPageExist = jQuery('#previousPageExist').val();
		var nextPageExist = jQuery('#nextPageExist').val();
		var previousPageButton = jQuery('#listViewPreviousPageButton');
		var nextPageButton = jQuery('#listViewNextPageButton');
		var pageJumpButton = jQuery('#listViewPageJump');
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		var pageStartRange = parseInt(jQuery('#pageStartRange').val());
		var pageEndRange = parseInt(jQuery('#pageEndRange').val());
		var pages = jQuery('#totalPageCount').text();
		var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
		var pageNumbersTextElem = jQuery('.pageNumbersText');

		if(pages > 1){
			pageJumpButton.removeAttr('disabled');
		}
		if(previousPageExist != ""){
			previousPageButton.removeAttr('disabled');
		} else if(previousPageExist == "") {
			previousPageButton.attr("disabled","disabled");
		}

		if((nextPageExist != "") && (pages >1)){
			nextPageButton.removeAttr('disabled');
		} else if((nextPageExist == "") || (pages == 1)) {
			nextPageButton.attr("disabled","disabled");
		}
		if(listViewEntriesCount != 0){
			var pageNumberText = pageStartRange+" "+app.vtranslate('JS_OF')+" "+pageEndRange;
			pageNumbersTextElem.html(pageNumberText);
			totalNumberOfRecords.removeClass('hide');
		} else {
			pageNumbersTextElem.html("<span>&nbsp;</span>");
			if(!totalNumberOfRecords.hasClass('hide')){
				totalNumberOfRecords.addClass('hide');
			}
		}

	},
	/*
	 * Function to register the event for changing the custom Filter
	 */
	registerChangeCustomFilterEvent : function(){
		var thisInstance = this;
		var filterSelectElement = this.getFilterSelectElement();
		filterSelectElement.change(function(e){
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var cvId = thisInstance.getCurrentCvId();
			selectedIds = new Array();
			excludedIds = new Array();

            var urlParams ={
                "viewname" : cvId,
                //to make alphabetic search empty
                "search_key" : thisInstance.getAlphabetSearchField(),
                "search_value" : "",
                "search_params" : ""
            }
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(urlParams).then (function(){
				thisInstance.ListViewPostOperation();
				thisInstance.updatePagination();
            });
		});
	},
        
         //Fix for empty Recycle bin 
        ListViewPostOperation : function(){  
            return true;    
        },

	/*
	 * Function to register the click event for list view main check box.
	 */
	registerMainCheckBoxClickEvent : function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click','#listViewEntriesMainCheckBox',function(){
			var selectedIds = thisInstance.readSelectedIds();
			var excludedIds = thisInstance.readExcludedIds();
			if(jQuery('#listViewEntriesMainCheckBox').is(":checked")){
				var recordCountObj = thisInstance.getRecordsCount();
				recordCountObj.then(function(data){
					jQuery('#totalRecordsCount').text(data);
					if(jQuery("#deSelectAllMsgDiv").css('display') == 'none'){
						jQuery("#selectAllMsgDiv").show();
					}
				});

				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
					if(selectedIds == 'all'){
						if((jQuery.inArray(jQuery(element).val(), excludedIds))!= -1){
							excludedIds.splice(jQuery.inArray(jQuery(element).val(),excludedIds),1);
						}
					} else if((jQuery.inArray(jQuery(element).val(), selectedIds)) == -1){
						selectedIds.push(jQuery(element).val());
					}
				});
			}else{
				jQuery("#selectAllMsgDiv").hide();
				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					jQuery(this).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
				if(selectedIds == 'all'){
					excludedIds.push(jQuery(element).val());
					selectedIds = 'all';
				} else {
					selectedIds.splice( jQuery.inArray(jQuery(element).val(), selectedIds), 1 );
				}
				});
			}
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);

		});
	},

	/*
	 * Function  to register click event for list view check box.
	 */
	registerCheckBoxClickEvent : function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.delegate('.listViewEntriesCheckBox','click',function(e){
			var selectedIds = thisInstance.readSelectedIds();
			var excludedIds = thisInstance.readExcludedIds();
			var elem = jQuery(e.currentTarget);
			if(elem.is(':checked')){
				elem.closest('tr').addClass('highlightBackgroundColor');
				if(selectedIds== 'all'){
					excludedIds.splice( jQuery.inArray(elem.val(), excludedIds), 1 );
				} else if((jQuery.inArray(elem.val(), selectedIds)) == -1) {
					selectedIds.push(elem.val());
				}
			} else {
				elem.closest('tr').removeClass('highlightBackgroundColor');
				if(selectedIds == 'all') {
					excludedIds.push(elem.val());
					selectedIds = 'all';
				} else {
					selectedIds.splice( jQuery.inArray(elem.val(), selectedIds), 1 );
				}
			}
			thisInstance.checkSelectAll();
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);
		});
	},

	/*
	 * Function to register the click event for select all.
	 */
	registerSelectAllClickEvent :  function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.delegate('#selectAllMsg','click',function(){
			jQuery('#selectAllMsgDiv').hide();
			jQuery("#deSelectAllMsgDiv").show();
			jQuery('#listViewEntriesMainCheckBox').attr('checked',true);
			jQuery('.listViewEntriesCheckBox').each( function(index,element) {
				jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
			});
			thisInstance.writeSelectedIds('all');
		});
	},

	/*
	* Function to register the click event for deselect All.
	*/
	registerDeselectAllClickEvent : function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.delegate('#deSelectAllMsg','click',function(){
			jQuery('#deSelectAllMsgDiv').hide();
			jQuery('#listViewEntriesMainCheckBox').attr('checked',false);
			jQuery('.listViewEntriesCheckBox').each( function(index,element) {
				jQuery(this).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
			});
			var excludedIds = new Array();
			var selectedIds = new Array();
			thisInstance.writeSelectedIds(selectedIds);
			thisInstance.writeExcludedIds(excludedIds);
		});
	},

	/*
	 * Function to register the click event for listView headers
	 */
	registerHeadersClickEvent :  function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click','.listViewHeaderValues',function(e){
			var fieldName = jQuery(e.currentTarget).data('columnname');
			var sortOrderVal = jQuery(e.currentTarget).data('nextsortorderval');
			var cvId = thisInstance.getCurrentCvId();
			var urlParams = {
				"orderby": fieldName,
				"sortorder": sortOrderVal,
				"viewname" : cvId
			}
			thisInstance.getListViewRecords(urlParams);
		});
	},

	/*
	 * function to register the click event event for create filter
	 */
	registerCreateFilterClickEvent : function(){
		var thisInstance = this;
		jQuery('#createFilter').on('click',function(event){
			//to close the dropdown
			thisInstance.getFilterSelectElement().data('select2').close();
			var currentElement = jQuery(event.currentTarget);
			var createUrl = currentElement.data('createurl');
			Vtiger_CustomView_Js.loadFilterView(createUrl);
		});
	},

	/*
	 * Function to register the click event for edit filter
	 */
	registerEditFilterClickEvent : function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if(listViewFilterBlock != false){
			listViewFilterBlock.on('mouseup','li i.editFilter',function(event){
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var editUrl = currentOptionElement.data('editurl');
				Vtiger_CustomView_Js.loadFilterView(editUrl);
				event.stopPropagation();
			});
		}
	},

	/*
	 * Function to register the click event for delete filter
	 */
	registerDeleteFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		if(listViewFilterBlock != false){
			//used mouseup event to stop the propagation of customfilter select change event.
			listViewFilterBlock.on('mouseup','li i.deleteFilter',function(event){
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
				Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
					function(e) {
						var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement); 
                                                var deleteUrl = currentOptionElement.data('deleteurl'); 
                                                var newEle = '<form action='+deleteUrl+' method="POST">'+ 
 		                                  '<input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>'+
 		                                  '</form>'; 
                                                var formElement = jQuery(newEle);  
                                                formElement.appendTo('body').submit(); 
					},
					function(error, err){
					}
				);
				event.stopPropagation();
			});
		}
	},

	/*
	 * Function to register the click event for approve filter
	 */
	registerApproveFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();

		if(listViewFilterBlock != false){
			listViewFilterBlock.on('mouseup','li i.approveFilter',function(event){
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var approveUrl = currentOptionElement.data('approveurl');
				window.location.href = approveUrl;
				event.stopPropagation();
			});
		}
	},

	/*
	 * Function to register the click event for deny filter
	 */
	registerDenyFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();

		if(listViewFilterBlock != false){
			listViewFilterBlock.on('mouseup','li i.denyFilter',function(event){
				//to close the dropdown
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
				var denyUrl = currentOptionElement.data('denyurl');
				window.location.href = denyUrl;
				event.stopPropagation();
			});
		}
	},

	/*
	 * Function to register the hover event for customview filter options
	 */
	registerCustomFilterOptionsHoverEvent : function(){
		var thisInstance = this;
		var listViewTopMenuDiv = this.getListViewTopMenuContainer();
		var filterBlock = this.getFilterBlock()
		if(filterBlock != false){
			filterBlock.on('hover','li.select2-result-selectable',function(event){
				var liElement = jQuery(event.currentTarget);
				var liFilterImages = liElement.find('.filterActionImgs');
				if (liElement.hasClass('group-result')){
					return;
				}

				if( event.type === 'mouseenter' ) {
					if(liFilterImages.length > 0){
						liFilterImages.show();
					}else{
						thisInstance.performFilterImageActions(liElement);
					}

				} else {
					liFilterImages.hide();
				}
			});
		}
	},

	performFilterImageActions : function(liElement) {
		jQuery('.filterActionImages').clone(true,true).removeClass('filterActionImages').addClass('filterActionImgs').appendTo(liElement.find('.select2-result-label')).show();
		var currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
		var deletable = currentOptionElement.data('deletable');
		if(deletable != '1'){
			liElement.find('.deleteFilter').remove();
		}
		var editable = currentOptionElement.data('editable');
		if(editable != '1'){
			liElement.find('.editFilter').remove();
		}
		var pending = currentOptionElement.data('pending');
		if(pending != '1'){
			liElement.find('.approveFilter').remove();
		}
		var approve = currentOptionElement.data('public');
		if(approve != '1'){
			liElement.find('.denyFilter').remove();
		}
	},

	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function(){
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.listViewEntries',function(e){
			if(jQuery(e.target).closest('a').hasClass('noLinkBtn')) return;
			if(jQuery(e.target, jQuery(e.currentTarget)).is('td:first-child')) return;
			if(jQuery(e.target).is('input[type="checkbox"]')) return;
			var elem = jQuery(e.currentTarget);
			var recordUrl = elem.data('recordurl');
            if(typeof recordUrl == 'undefined') {
                return;
            }
			window.location.href = recordUrl;
		});
	},

	/*
	 * Function to register the list view delete record click event
	 */
	registerDeleteRecordClickEvent: function(){
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.deleteRecordButton',function(e){
			var elem = jQuery(e.currentTarget);
			var recordId = elem.closest('tr').data('id');
			Vtiger_List_Js.deleteRecord(recordId);
			e.stopPropagation();
		});
	},
	/*
	 * Function to register the click event of email field
	 */
	registerEmailFieldClickEvent : function(){
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.emailField',function(e){
			e.stopPropagation();
		})
	},

	/*
	 * Function to register the click event of phone field
	 */
	registerPhoneFieldClickEvent : function(){
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.phoneField',function(e){
			e.stopPropagation();
		})
	},

	/*
	 * Function to register the click event of url field
	 */
	registerUrlFieldClickEvent : function(){
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','.urlField',function(e){
			e.stopPropagation();
		})
	},

	/**
	 * Function to inactive field for validation in a form
	 * this will remove data-validation-engine attr of all the elements
	 * @param Accepts form as a parameter
	 */
	inactiveFieldValidation : function(form){
        var massEditFieldList = jQuery('#massEditFieldsNameList').data('value');
		for(var fieldName in massEditFieldList){
            var fieldInfo = massEditFieldList[fieldName];

            var fieldElement = form.find('[name="'+fieldInfo.name+'"]');
            if(fieldInfo.type == "reference") {
                //get the element which will be shown which has "_display" appended to actual field name
                fieldElement = form.find('[name="'+fieldInfo.name+'_display"]');
            }else if(fieldInfo.type == "multipicklist" || fieldInfo.type == "sharedOwner") {
                fieldElement = form.find('[name="'+fieldInfo.name+'[]"]');
            }

            //Not all the fields will be enabled for mass edit
            if(fieldElement.length == 0 ) {
                continue;
            }

			var elemData = fieldElement.data();

            //Blank validation by default
            var validationVal = "validate[]"
            if('validationEngine' in elemData) {
                validationVal =  elemData.validationEngine;
                delete elemData.validationEngine;
            }
            fieldElement.data('invalidValidationEngine',validationVal);
			fieldElement.removeAttr('data-validation-engine');
		}
	},

	/**
	 * function to register field for validation
	 * this will add the data-validation-engine attr of all the elements
	 * make the field available for validation
	 * @param Accepts form as a parameter
	 */
	registerFieldsForValidation : function(form){
		form.find('.fieldValue').on('change','input,select,textarea',function(e, params){
			if(typeof params == 'undefined'){
				params = {};
			}

			if(typeof params.forceDeSelect == 'undefined') {
				params.forceDeSelect = false;
			}
			var element = jQuery(e.currentTarget);
			var fieldValue = element.val();
			var parentTd = element.closest('td');
			if(((fieldValue == "" || fieldValue == null) && (typeof(element.attr('data-validation-engine')) != "undefined")) || params.forceDeSelect){
				if(parentTd.hasClass('massEditActiveField')){
					parentTd.removeClass('massEditActiveField');
				}
				element.removeAttr('data-validation-engine');
				element.validationEngine('hide');
				var invalidFields = form.data('jqv').InvalidFields;
				var response = jQuery.inArray(element.get(0),invalidFields);
				if(response != '-1'){
					invalidFields.splice(response,1);
				}
			} else if((fieldValue != "") && (typeof(element.attr('data-validation-engine')) == "undefined")){
				element.attr('data-validation-engine', element.data('invalidValidationEngine'));
				parentTd.addClass('massEditActiveField');
			}
		})
	},

	registerEventForTabClick : function(form){
		var ulContainer = form.find('.massEditTabs');
		ulContainer.on('click','a[data-toggle="tab"]',function(e){
			form.validationEngine('validate');
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length > 0){
				e.stopPropagation();
			}
		});
	},

	registerReferenceFieldsForValidation : function(form){
		var referenceField = form.find('.sourceField');
		form.find('.sourceField').on(Vtiger_Edit_Js.referenceSelectionEvent,function(e,params){
			var element = jQuery(e.currentTarget);
			var elementName = element.attr('name');
			var fieldDisplayName = elementName+"_display";
			var fieldDisplayElement = form.find('input[name="'+fieldDisplayName+'"]');
			if(params.selectedName == ""){
				return;
			}
			fieldDisplayElement.attr('data-validation-engine', fieldDisplayElement.data('invalidValidationEngine'));
            var parentTd = fieldDisplayElement.closest('td');
            if(!parentTd.hasClass('massEditActiveField')){
                parentTd.addClass('massEditActiveField');
            }
		})
		form.find('.clearReferenceSelection').on(Vtiger_Edit_Js.referenceDeSelectionEvent,function(e){
			var sourceField = form.find('.sourceField');
			var sourceFieldName = sourceField.attr('name');
			var fieldDisplayName = sourceFieldName+"_display";
			var fieldDisplayElement = form.find('input[name="'+fieldDisplayName+'"]').removeAttr('data-validation-engine');
            var parentTd = fieldDisplayElement.closest('td');
            if(parentTd.hasClass('massEditActiveField')){
                parentTd.removeClass('massEditActiveField');
            }
		})
	},

	registerSlimScrollMassEdit : function() {
		app.showScrollBar(jQuery('div[name="massEditContent"]'), {'height':'400px'});
	},

	/*
	 * Function to register the submit event for mass Actions save
	 */
	registerMassActionSubmitEvent : function(){
        var thisInstance = this;
		jQuery('body').on('submit','#massSave',function(e){
			var form = jQuery(e.currentTarget);
			var commentContent = form.find('#commentcontent')
			var commentContentValue = commentContent.val();
			if(commentContentValue == "") {
				var errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY')
				commentContent.validationEngine('showPrompt', errorMsg , 'error','bottomLeft',true);
				e.preventDefault();
				return;
			}
			commentContent.validationEngine('hide');
			jQuery(form).find('[name=saveButton]').attr('disabled','disabled');
			thisInstance.massActionSave(form).then(function(data){
				Vtiger_List_Js.clearList();
			});
			e.preventDefault();
		});
	},

	changeCustomFilterElementView : function() {
		var filterSelectElement = this.getFilterSelectElement();
		if(filterSelectElement.length > 0 && filterSelectElement.is("select")) {
			app.showSelect2ElementView(filterSelectElement,{
				formatSelection : function(data, contianer){
					var resultContainer = jQuery('<span></span>');
					resultContainer.append(jQuery(jQuery('.filterImage').clone().get(0)).show());
					resultContainer.append(data.text);
					return resultContainer;
				},
				customSortOptGroup : true
			});

			var select2Instance = filterSelectElement.data('select2');
            jQuery('span.filterActionsDiv').appendTo(select2Instance.dropdown).removeClass('hide');
		}
	},

	triggerDisplayTypeEvent : function() {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if(widthType) {
			var elements = jQuery('.listViewEntriesTable').find('td,th');
			elements.attr('class', widthType);
		}
	},

	registerEventForAlphabetSearch : function() {
		var thisInstance = this;
		var listViewPageDiv = this.getListViewContentContainer();
		listViewPageDiv.on('click','.alphabetSearch',function(e) {
			var alphabet = jQuery(e.currentTarget).find('a').text();
			var cvId = thisInstance.getCurrentCvId();
			var AlphabetSearchKey = thisInstance.getAlphabetSearchField();
			var urlParams = {
				"viewname" : cvId,
				"search_key" : AlphabetSearchKey,
				"search_value" : alphabet,
				"operator" : 's',
				"page"	:	1
			}
			jQuery('#recordsCount').val('');
			//To Set the page number as first page
			jQuery('#pageNumber').val('1');
			jQuery('#pageToJump').val('1');
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(urlParams).then(
					function(data){
						thisInstance.updatePagination();
                        //To unmark the all the selected ids
                        jQuery('#deSelectAllMsg').trigger('click');
					},

					function(textStatus, errorThrown){
					}
			);
		});
	},

	/**
	 * Function to show total records count in listview on hover
	 * of pageNumber text
	 */
	registerEventForTotalRecordsCount : function(){
		var thisInstance = this;
		jQuery('.totalNumberOfRecords').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var totalRecordsElement = jQuery('#totalCount');
			var totalNumberOfRecords = totalRecordsElement.val();
			element.addClass('hide');
			element.parent().progressIndicator({});
			if(totalNumberOfRecords == '') {
				thisInstance.getPageCount().then(function(data){
					totalNumberOfRecords = data['result']['numberOfRecords'];
					totalRecordsElement.val(totalNumberOfRecords);
					thisInstance.showPagingInfo();
				});
			}else{
				thisInstance.showPagingInfo();
			}
			element.parent().progressIndicator({'mode':'hide'});
		})
	},

	showPagingInfo : function(){
		var totalNumberOfRecords = jQuery('#totalCount').val();
		var pageNumberElement = jQuery('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange+" "+app.vtranslate('JS_OF')+" "+totalNumberOfRecords;
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		if(listViewEntriesCount != 0){
			jQuery('.pageNumbersText').html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText').html("");
		}
	},

	registerEvents : function(){

		this.registerRowClickEvent();
		this.registerPageNavigationEvents();
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
		this.registerDeleteRecordClickEvent();
		this.registerHeadersClickEvent();
		this.registerMassActionSubmitEvent();
		this.registerEventForAlphabetSearch();

		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerCreateFilterClickEvent();
		this.registerEditFilterClickEvent();
		this.registerDeleteFilterClickEvent();
		this.registerApproveFilterClickEvent();
		this.registerDenyFilterClickEvent();
		this.registerCustomFilterOptionsHoverEvent();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.registerUrlFieldClickEvent();
		this.registerEventForTotalRecordsCount();

		//Just reset all the checkboxes on page load: added for chrome issue.
		var listViewContainer = this.getListViewContentContainer();
		listViewContainer.find('#listViewEntriesMainCheckBox,.listViewEntriesCheckBox').prop('checked', false);

        this.registerListSearch();
        this.registerDateListSearch(listViewContainer);
        this.registerTimeListSearch(listViewContainer);
		this.registerListViewSelect();
	},
	registerListViewSelect : function() {
		var listViewContainer = this.getListViewContentContainer();
		var select = listViewContainer.find('.listViewEntriesTable .select2noactive');
		select.select2({closeOnSelect:true});
		select.on("change", function(e) { 
			Vtiger_List_Js.triggerListSearch();
		})
	},
	/**
	 * Function that executes after the mass delete action
	 */
	postMassDeleteRecords : function() {
		var aDeferred = jQuery.Deferred();
		var listInstance = Vtiger_List_Js.getInstance();
		app.hideModalWindow();
		var module = app.getModuleName();
		listInstance.getListViewRecords().then(
			function(data) {
				jQuery('#recordsCount').val('');
				jQuery('#totalPageCount').text('');
				//listInstance.triggerDisplayTypeEvent();
				jQuery('#deSelectAllMsg').trigger('click');
				listInstance.calculatePages().then(function(){
					listInstance.updatePagination();
				});
				aDeferred.resolve();
		});
		jQuery('#recordsCount').val('');
		return aDeferred.promise();
	},

    getListSearchParams : function(){
        var listViewPageDiv = this.getListViewContainer();
        var listViewTable = listViewPageDiv.find('.listViewEntriesTable');
        var searchParams = new Array();
        listViewTable.find('.listSearchContributor').each(function(index,domElement){
            var searchInfo = new Array();
            var searchContributorElement = jQuery(domElement);
            var fieldInfo = searchContributorElement.data('fieldinfo');
            var fieldName = searchContributorElement.attr('name');

            var searchValue = searchContributorElement.val();

            if(typeof searchValue == "object") {
                if(searchValue == null) {
                   searchValue = "";
                }else{
                    searchValue = searchValue.join(',');
                }
            }
            searchValue = searchValue.trim();
            if(searchValue.length <=0 ) {
                //continue
                return true;
            }
            var searchOperator = 'c';
            if(fieldInfo.type == "date" || fieldInfo.type == "datetime") {
                searchOperator = 'bw';
            }else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
                || fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
                fieldInfo.type == "picklist") {
                searchOperator = 'e';
            }
            searchInfo.push(fieldName);
            searchInfo.push(searchOperator);
            searchInfo.push(searchValue);
            searchParams.push(searchInfo);
        });
        return new Array(searchParams);
    },

    registerListSearch : function() {
      var listViewPageDiv = this.getListViewContainer();
      var thisInstance = this;
      listViewPageDiv.on('click','[data-trigger="listSearch"]',function(e){
		thisInstance.getListViewRecords({'page': '1'}).then(
			function(data){
				//To unmark the all the selected ids
				jQuery('#deSelectAllMsg').trigger('click');

				 jQuery('#recordsCount').val('');
				//To Set the page number as first page
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#totalPageCount').text("");
				thisInstance.calculatePages().then(function(){
					thisInstance.updatePagination();
				});
			},

			function(textStatus, errorThrown){
			}
		);
      })

      listViewPageDiv.on('keypress','input.listSearchContributor',function(e){
          if(e.keyCode == 13){
              var element = jQuery(e.currentTarget);
              var parentElement = element.closest('tr');
              var searchTriggerElement = parentElement.find('[data-trigger="listSearch"]');
              searchTriggerElement.trigger('click');
          }
      });
    },

    registerDateListSearch : function(container) {
        container.find('.dateField').each(function(index,element){
            var dateElement = jQuery(element);
            var customParams = {
                calendars: 3,
                mode: 'range',
                className : 'rangeCalendar',
                onChange: function(formated) {
                    dateElement.val(formated.join(','));
                }
            }
            app.registerEventForDatePickerFields(dateElement,false,customParams);
        });

    },

    registerTimeListSearch : function(container) {
        app.registerEventForTimeFields(container,false);
	}
});