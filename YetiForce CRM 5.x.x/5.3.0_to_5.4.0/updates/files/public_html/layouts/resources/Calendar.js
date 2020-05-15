/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/** Class representing a calendar. */
window.Calendar_Js = class {
	/**
	 * Create calendar's options.
	 * @param {jQuery} container
	 * @param {bool} readonly
	 */
	constructor(container = $('.js-base-container'), readonly = false, browserHistory = true) {
		this.calendarView = false;
		this.calendarCreateView = false;
		this.container = container;
		this.readonly = readonly;
		this.eventCreate = app.getMainParams('eventCreate');
		this.eventEdit = app.getMainParams('eventEdit');
		this.browserHistory = !readonly && browserHistory;
		this.sidebarView = {
			length: 0
		};
		this.browserHistoryConfig = this.browserHistory ? {} : this.setBrowserHistoryOptions();
		this.calendarOptions = this.setCalendarOptions();
		this.eventTypeKeyName = false;
		this.module = app.getModuleName();
	}

	/**
	 * Set calendar's options.
	 * @returns {object}
	 */
	setCalendarOptions() {
		return Object.assign(
			this.setCalendarBasicOptions(),
			this.setCalendarAdvancedOptions(),
			this.setCalendarModuleOptions(),
			this.browserHistoryConfig
		);
	}

	/**
	 * Set calendar's basic options.
	 * @returns {object}
	 */
	setCalendarBasicOptions() {
		let eventLimit = app.getMainParams('eventLimit'),
			userDefaultActivityView = app.getMainParams('activity_view'),
			defaultView = app.moduleCacheGet('defaultView'),
			userDefaultTimeFormat = CONFIG.hourFormat;
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = app.getMainParams('dayView');
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = app.getMainParams('weekView');
		} else if (userDefaultActivityView === 'This Year') {
			userDefaultActivityView = 'year';
		} else {
			userDefaultActivityView = 'month';
		}
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}

		let options = {
			timeFormat: userDefaultTimeFormat,
			slotLabelFormat: userDefaultTimeFormat,
			defaultView: userDefaultActivityView,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			eventLimitText: app.vtranslate('JS_MORE'),
			selectHelper: true,
			scrollTime: app.getMainParams('startHour') + ':00',
			monthNamesShort: App.Fields.Date.monthsTranslated,
			dayNames: App.Fields.Date.fullDaysTranslated,
			buttonText: {
				today: app.vtranslate('JS_CURRENT'),
				year: app.vtranslate('JS_YEAR'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY')
		};
		if (app.moduleCacheGet('start') !== null) {
			let s = moment(app.moduleCacheGet('start')).valueOf();
			let e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + (e - s) / 2).format('YYYY-MM-DD'));
		}
		return Object.assign(this.setCalendarMinimalOptions(), options);
	}

	/**
	 * Converts the date format.
	 * @param {string} partOfDate
	 */
	parseDateFormat(partOfDate) {
		let parseMonthFormat = {
			'yyyy-mm-dd': 'YYYY-MMMM',
			'mm-dd-yyyy': 'MMMM-YYYY',
			'dd-mm-yyyy': 'MMMM-YYYY',
			'yyyy.mm.dd': 'YYYY.MMMM',
			'mm.dd.yyyy': 'MMMM.YYYY',
			'dd.mm.yyyy': 'MMMM.YYYY',
			'yyyy/mm/dd': 'YYYY/MMMM',
			'mm/dd/yyyy': 'MMMM/YYYY',
			'dd/mm/yyyy': 'MMMM/YYYY'
		};
		let parseWeekAndDayFormat = {
			'yyyy-mm-dd': 'YYYY-MMM-D',
			'mm-dd-yyyy': 'MMM-D-YYYY',
			'dd-mm-yyyy': 'D-MMM-YYYY',
			'yyyy.mm.dd': 'YYYY.MMM.D',
			'mm.dd.yyyy': 'MMM.D.YYYY',
			'dd.mm.yyyy': 'D.MMM.YYYY',
			'yyyy/mm/dd': 'YYYY/MMM/D',
			'mm/dd/yyyy': 'MMM/D/YYYY',
			'dd/mm/yyyy': 'D/MMM/YYYY'
		};
		let formatDate = CONFIG.dateFormat;
		switch (partOfDate) {
			case 'month':
				return parseMonthFormat[formatDate];
				break;
			case 'week':
				return parseWeekAndDayFormat[formatDate];
				break;
			case 'day':
				return parseWeekAndDayFormat[formatDate];
				break;
		}
	}

	/**
	 * Set calendar's minimal options.
	 * @returns {object}
	 */
	setCalendarMinimalOptions() {
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		return {
			firstDay: CONFIG.firstDayOfWeekNo,
			selectable: true,
			hiddenDays: hiddenDays,
			monthNames: App.Fields.Date.fullMonthsTranslated,
			dayNamesShort: App.Fields.Date.daysTranslated
		};
	}

	/**
	 * Set calendar's advanced options.
	 * @returns {object}
	 */
	setCalendarAdvancedOptions() {
		let self = this;
		return {
			editable: !this.readonly && this.eventEdit,
			selectable: !this.readonly && this.eventCreate,
			header: {
				left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
				center: 'title today',
				right: 'prev,next'
			},
			allDaySlot: app.getMainParams('allDaySlot'),
			views: {
				basic: {
					eventLimit: false
				},
				month: {
					titleFormat: this.parseDateFormat('month')
				},
				week: {
					titleFormat: this.parseDateFormat('week')
				},
				day: {
					titleFormat: this.parseDateFormat('day')
				}
			},
			eventDrop: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			viewRender: function () {
				self.loadCalendarData();
			},
			eventRender: self.eventRenderer,
			height: this.setCalendarHeight(this.container)
		};
	}

	/**
	 * Update calendar's event.
	 * @param {Object} event
	 * @param {Object} delta
	 * @param {Object} revertFunc
	 */
	updateEvent(event, delta, revertFunc) {
		let progressInstance = jQuery.progressIndicator({ blockInfo: { enabled: true } });
		let start = event.start.format();
		let params = {
			module: CONFIG.module,
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data,
			allDay: event.allDay
		};
		AppConnector.request(params)
			.done(function (response) {
				if (!response['result']) {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
					revertFunc();
				} else {
					window.popoverCache = {};
				}
				progressInstance.progressIndicator({ mode: 'hide' });
			})
			.fail(function () {
				progressInstance.progressIndicator({ mode: 'hide' });
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			});
	}

	/**
	 * Render event.
	 * @param {Object} event
	 * @param {jQuery} element
	 */
	eventRenderer(event, element) {
		element.find('.fc-title').html(event.title);
		if (event.rendering === 'background') {
			element.append(
				`<span class="js-popover-text d-block"><span class="${event.icon} js-popover-icon mr-1"></span>${event.title}</span>`
			);
			element.addClass('js-popover-tooltip--ellipsis').attr('data-content', event.title);
			app.registerPopoverEllipsis({ element });
		}
	}

	/**
	 * Returns counted calendar height.
	 * @returns {(number|string)}
	 */
	setCalendarHeight() {
		const defaultHeightValue = 'auto';
		if ($(window).width() > 993) {
			let calendarContainer = this.container.find('.js-calendar__container'),
				calendarPadding;
			if (this.container.hasClass('js-modal-container')) {
				calendarPadding = this.container.find('.js-modal-header').outerHeight(); // modal needs bigger padding to prevent modal's scrollbar
			} else {
				calendarPadding = this.container
					.find('.js-contents-div')
					.css('margin-left')
					.replace('px', ''); //equals calendar padding bottom to left margin
			}
			let setCalendarH = () => {
				return (
					$(window).height() -
					this.container.find('.js-calendar__container').offset().top -
					$('.js-footer').height() -
					calendarPadding
				);
			};
			new ResizeSensor(this.container.find('.contentsDiv'), () => {
				calendarContainer.fullCalendar('option', 'height', setCalendarH());
			});
		}
		return defaultHeightValue;
	}

	/**
	 * Set calendar module's options.
	 * @returns {object}
	 */
	setCalendarModuleOptions() {
		return {};
	}

	/**
	 * Set calendar options from browser history.
	 * @returns {object}
	 */
	setBrowserHistoryOptions() {
		let historyParams = app.getMainParams('historyParams', true),
			options = null;
		if (historyParams && (historyParams.length || Object.keys(historyParams).length)) {
			options = {
				start: historyParams.start,
				end: historyParams.end,
				user: historyParams.user.split(',').map((x) => {
					return parseInt(x);
				}),
				time: historyParams.time,
				hiddenDays: historyParams.hiddenDays.split(',').map((x) => {
					let parsedValue = parseInt(x);
					return isNaN(parsedValue) ? '' : parsedValue;
				}),
				cvid: historyParams.cvid,
				defaultView: historyParams.viewType
			};
			let dateFormat = CONFIG.dateFormat.toUpperCase();
			let s = moment(options.start, dateFormat).valueOf();
			let e = moment(options.end, dateFormat).valueOf();
			options.defaultDate = moment(moment(s + (e - s) / 2).format('YYYY-MM-DD'));
			Object.keys(options).forEach((key) => options[key] === 'undefined' && delete options[key]);
			app.moduleCacheSet('browserHistoryEvent', false);
			app.setMainParams('showType', options.time);
			app.setMainParams('usersId', options.user);
			app.setMainParams('defaultView', options.defaultView);
		}
		window.addEventListener(
			'popstate',
			function (event) {
				app.moduleCacheSet('browserHistoryEvent', true);
			},
			false
		);
		return options;
	}
	/**
	 * Register filters
	 */
	registerFilters() {
		const thisInstance = this;
		let aDeferred = jQuery.Deferred();

		let sideBar = thisInstance.getSidebarView();
		if (!sideBar || sideBar.length <= 0) {
			aDeferred.resolve(true);
			return aDeferred.promise();
		}
		sideBar.find('.js-sidebar-filter-container').each((_, row) => {
			if (row.dataset.url) {
				AppConnector.request(row.dataset.url).done(function (data) {
					if (data) {
						let formContainer = $(row).find('.js-sidebar-filter-body');
						formContainer.html(data);
						thisInstance.registerUsersChange(formContainer);
						App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
						app.showNewScrollbar(formContainer, {
							suppressScrollX: true
						});
						thisInstance.registerFilterForm(formContainer);
					}
				});
			}
		});
		return aDeferred.promise();
	}
	/**
	 * Register filter for users and groups
	 * @param {jQuery} container
	 */
	registerFilterForm(container) {
		if (container.find('.js-filter__search').length) {
			container.find('.js-filter__search').on('keyup', this.findElementOnList.bind(self));
		}
		container.find('.js-calendar__filter__select, .filterField').each((_, e) => {
			let element = $(e);
			let name = element.data('cache');
			let cachedValue = app.moduleCacheGet(name);
			if (element.length > 0 && cachedValue !== undefined) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(cachedValue);
				}
			} else if (
				name &&
				element.length > 0 &&
				cachedValue === undefined &&
				!element.find(':selected').length
			) {
				let allOptions = [];
				element.find('option').each((i, option) => {
					allOptions.push($(option).val());
				});
				element.val(allOptions);
				app.moduleCacheSet(name, cachedValue);
			}

			element.off('change');
			App.Fields.Picklist.showSelect2ElementView(element);
			element.on('change', (e) => {
				let item = $(e.currentTarget);
				let value = item.val();
				if (value == null) {
					value = '';
				}
				if (item.attr('type') == 'checkbox') {
					value = element.is(':checked');
				}
				app.moduleCacheSet(item.data('cache'), value);
				this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
			});
		});
		container
			.find('.js-filter__container_checkbox_list .js-filter__item__val')
			.off('change')
			.on('change', (e) => {
				this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
			});
	}

	registerUsersChange(formContainer) {
		formContainer.find('.js-input-user-owner-id-ajax, .js-input-user-owner-id').on('change', () => {
			this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
	}
	/**
	 * Register events.
	 * @returns {object}
	 */
	registerEvents() {
		this.registerFilters();
		this.registerSitebarEvents();
		this.renderCalendar();
		this.registerButtonSelectAll();
		this.registerAddButton();
	}

	/**
	 * Invokes fullcalendar with options.
	 */
	renderCalendar() {
		this.getCalendarView().fullCalendar(this.calendarOptions);
	}

	/**
	 * Register sitebar events.
	 */
	registerSitebarEvents() {
		$('.bodyContents').on('Vtiger.Widget.Load.undefined', () => {
			this.registerSelect2Event();
		});
	}

	/**
	 * Load calendar data
	 */
	loadCalendarData() {
		const defaultParams = this.getDefaultParams();
		this.getCalendarView().fullCalendar('removeEvents');
		if (!defaultParams.emptyFilters) {
			const progressInstance = $.progressIndicator();
			AppConnector.request(defaultParams).done((events) => {
				this.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.hide();
			});
		}
	}
	getSidebarView() {
		if (!this.sidebarView.length) {
			this.sidebarView = this.container.find('.js-calendar-right-panel');
		}
		return this.sidebarView;
	}
	getCurrentCvId() {
		return $('.js-calendar__extended-filter-tab .active').parent('.js-filter-tab').data('cvid');
	}
	/**
	 * Default params
	 * @returns {{module: *, action: string, mode: string, start: *, end: *, user: *, emptyFilters: boolean}}
	 */
	getDefaultParams() {
		let formatDate = CONFIG.dateFormat.toUpperCase(),
			view = this.getCalendarView().fullCalendar('getView'),
			cvid = this.getCurrentCvId(),
			users = app.moduleCacheGet('calendar-users') || CONFIG.userId,
			sideBar = this.getSidebarView();
		let params = {
			module: this.module ? this.module : CONFIG.module,
			action: 'Calendar',
			mode: 'getEvents',
			start: view.start.format(formatDate),
			end: view.end.format(formatDate),
			user: users,
			cvid: cvid,
			emptyFilters: users.length === 0
		};
		let filters = [];
		sideBar.find('.calendarFilters .filterField').each(function () {
			let element = $(this),
				name,
				value;
			if (element.attr('type') == 'checkbox') {
				name = element.val();
				value = element.prop('checked') ? 1 : 0;
			} else {
				name = element.attr('name');
				value = element.val();
			}
			filters.push({ name: name, value: value });
		});
		if (filters.length) {
			params.filters = filters;
		}
		sideBar.find('.js-sidebar-filter-container').each((_, e) => {
			let element = $(e);
			let name = element.data('name');
			let cacheName = element.data('cache');
			if (name && cacheName && app.moduleCacheGet(cacheName)) {
				params[name] = app.moduleCacheGet(cacheName);
				params.emptyFilters = !params.emptyFilters && params[name].length === 0;
			}
		});
		sideBar.find('.js-filter__container_checkbox_list').each((_, e) => {
			let filters = [];
			let element = $(e);
			let name = element.data('name');
			element.find('.js-filter__item__val:checked').each(function () {
				filters.push($(this).val());
			});
			if (name) {
				params[name] = filters;
			}
		});
		sideBar.find('.js-calendar__filter__select').each((_, e) => {
			let element = $(e);
			let name = element.attr('name');
			let cacheName = element.data('cache');
			if (name) {
				params[name] =
					cacheName && app.moduleCacheGet(cacheName)
						? app.moduleCacheGet(cacheName)
						: element.val();
				params.emptyFilters = !params.emptyFilters && params[name].length === 0;
			}
		});
		return params;
	}

	/**
	 * Register select2 event.
	 */
	registerSelect2Event() {
		let self = this;
		$('.siteBarRight .js-calendar__filter__select').each(function () {
			let element = $(this);
			let name = element.data('cache');
			let cachedValue = app.moduleCacheGet(name);
			if (element.length > 0 && cachedValue !== undefined) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(cachedValue);
				}
			} else if (
				element.length > 0 &&
				cachedValue === undefined &&
				!element.find(':selected').length
			) {
				let allOptions = [];
				element.find('option').each((i, option) => {
					allOptions.push($(option).val());
				});
				element.val(allOptions);
				app.moduleCacheSet(name, cachedValue);
			}
		});
		let selectsElements = $('.siteBarRight .select2, .siteBarRight .filterField');
		selectsElements.off('change');
		App.Fields.Picklist.showSelect2ElementView(selectsElements);
		selectsElements.on('change', function () {
			let element = $(this);
			let value = element.val();
			if (value == null) {
				value = '';
			}
			if (element.attr('type') == 'checkbox') {
				value = element.is(':checked');
			}
			app.moduleCacheSet(element.data('cache'), value);
			self.loadCalendarData();
		});
	}

	/**
	 * Register button select all.
	 */
	registerButtonSelectAll() {
		let selectBtn = $('.selectAllBtn');
		selectBtn.on('click', function (e) {
			let selectAllLabel = $(this).find('.selectAll');
			let deselectAllLabel = $(this).find('.deselectAll');
			if (selectAllLabel.hasClass('d-none')) {
				selectAllLabel.removeClass('d-none');
				deselectAllLabel.addClass('d-none');
				$(this).closest('.quickWidget').find('select option').prop('selected', false);
			} else {
				$(this).closest('.quickWidget').find('select option').prop('selected', true);
				deselectAllLabel.removeClass('d-none');
				selectAllLabel.addClass('d-none');
			}
			$(this).closest('.quickWidget').find('select').trigger('change');
		});
	}

	/**
	 * Register add button.
	 */
	registerAddButton() {
		const self = this;
		$('.js-add').on('click', (e) => {
			self.getCalendarCreateView().done((data) => {
				const headerInstance = new Vtiger_Header_Js();
				headerInstance.handleQuickCreateData(data, {
					callbackFunction: (data) => {
						self.addCalendarEvent(data.result);
					}
				});
			});
		});
	}

	/**
	 * Get calendar create view.
	 * @returns {promise}
	 */
	getCalendarCreateView() {
		let self = this;
		let aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		let progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView()
			.done(function (data) {
				progressInstance.hide();
				self.calendarCreateView = data;
				aDeferred.resolve(data.clone(true, true));
			})
			.fail(function () {
				progressInstance.hide();
			});
		return aDeferred.promise();
	}

	/**
	 * Load calendar create view.
	 * @returns {promise}
	 */
	loadCalendarCreateView() {
		let aDeferred = jQuery.Deferred();
		let moduleName = app.getModuleName();
		let url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		let headerInstance = Vtiger_Header_Js.getInstance();
		headerInstance
			.getQuickCreateForm(url, moduleName)
			.done(function (data) {
				aDeferred.resolve(jQuery(data));
			})
			.fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
		return aDeferred.promise();
	}

	/**
	 * Add event data to render.
	 */
	getEventRenderData(calendarDetails) {
		const calendar = this.getCalendarView();
		const eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails._recordLabel,
			start: calendar
				.fullCalendar(
					'moment',
					calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value
				)
				.format(),
			end: calendar
				.fullCalendar(
					'moment',
					calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value
				)
				.format(),
			start_display:
				calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value,
			end_display:
				calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value,
			url: `index.php?module=${CONFIG.module}&view=Detail&record=${calendarDetails._recordId}`,
			className: `js-popover-tooltip--record ownerCBg_${
				calendarDetails.assigned_user_id.value
			} picklistCBr_${CONFIG.module}_${
				$('.js-calendar__filter__select[data-cache="calendar-types"]').length &&
				calendarDetails[this.eventTypeKeyName]
					? this.eventTypeKeyName + '_' + calendarDetails[this.eventTypeKeyName]['value']
					: ''
			}`,
			allDay:
				typeof calendarDetails.allday === 'undefined' ? false : calendarDetails.allday.value == 'on'
		};
		return eventObject;
	}

	isNewEventToDisplay(eventObject) {
		let ownerSelects = $('.js-calendar__filter__select[data-cache="calendar-users"]').add(
			$('.js-calendar__filter__select[data-cache="calendar-groups"]')
		);
		if ($.inArray(eventObject.assigned_user_id.value, ownerSelects.val()) < 0) {
			this.refreshFilterValues(eventObject, ownerSelects);
			return false;
		}
		let calendarTypes = $('.js-calendar__filter__select[data-cache="calendar-types"]');
		if (calendarTypes.length) {
			if (!this.eventTypeKeyName) {
				this.setEventTypeKey(eventObject, calendarTypes.data('name'));
			}
			if (
				this.eventTypeKeyName &&
				calendarTypes.val().length &&
				$.inArray(eventObject[this.eventTypeKeyName]['value'], calendarTypes.val()) < 0
			) {
				return false;
			}
		}
		return true;
	}

	setEventTypeKey(eventObject, keyName) {
		let self = this;
		if (keyName && eventObject.keyName !== undefined) {
			self.eventTypeKeyName = keyName;
		} else {
			Object.keys(eventObject).forEach(function (key, index) {
				if (key.endsWith('type')) {
					// there are different names for event types in modules
					self.eventTypeKeyName = key;
				}
			});
		}
	}

	refreshFilterValues(eventObject, filtersValues) {
		if (CONFIG.searchShowOwnerOnlyInList) {
			let allOptions = [];
			filtersValues.find('option').each((i, option) => {
				allOptions.push($(option).val());
			});
			if ($.inArray(eventObject.assigned_user_id.value, allOptions) < 0) {
				AppConnector.request(`module=${CONFIG.module}&view=RightPanel&mode=getUsersList`).done(
					(usersData) => {
						let filterUsers = $('.js-calendar__filter--users');
						let filterGroups = $('.js-calendar__filter--groups');
						filterUsers.html(usersData);
						if (usersData) {
							filterUsers.closest('.js-toggle-panel').removeClass('d-none');
						}
						if (filterGroups.length) {
							AppConnector.request(
								`module=${CONFIG.module}&view=RightPanel&mode=getGroupsList`
							).done((groupsData) => {
								filterGroups.html(groupsData);
								if (groupsData) {
									filterGroups.closest('.js-toggle-panel').removeClass('d-none');
								}
								this.registerSelect2Event();
							});
						} else {
							this.registerSelect2Event();
						}
					}
				);
			}
		}
	}

	/**
	 * Add calendar event.
	 */
	addCalendarEvent(eventObject) {
		if (this.isNewEventToDisplay(eventObject)) {
			this.getCalendarView().fullCalendar('renderEvent', this.getEventRenderData(eventObject));
		}
	}

	/**
	 * Get calendar container.
	 * @returns {(boolean|jQuery)}
	 */
	getCalendarView() {
		if (this.calendarView == false) {
			this.calendarView = this.container.find('.js-calendar__container');
		}
		return this.calendarView;
	}
};

/**
 *  Class representing a calendar with creating events by day click instead of selecting days.
 * @extends Calendar_Js
 */
window.Calendar_Unselectable_Js = class extends Calendar_Js {
	/**
	 * Set calendar module options.
	 * @returns {{allDaySlot: boolean, dayClick: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		let self = this;
		return {
			allDaySlot: false,
			dayClick: this.eventCreate
				? function (date) {
						self.registerDayClickEvent(date.format());
						self.getCalendarView().fullCalendar('unselect');
				  }
				: false,
			selectable: false
		};
	}

	/**
	 * Register day click event.
	 * @param {string} date
	 */
	registerDayClickEvent(date) {
		let self = this;
		self.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase(),
				timeFormat = data.find('[name="time_start"]').data('format'),
				defaultTimeFormat = 'hh:mm A';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			}
			let startDateInstance = Date.parse(date);
			let startDateString = moment(date).format(dateFormat);
			let startTimeString = moment(date).format(defaultTimeFormat);
			let endDateInstance = Date.parse(date);
			let endDateString = moment(date).format(dateFormat);

			let view = self.getCalendarView().fullCalendar('getView');
			let endTimeString;
			if ('month' == view.name) {
				let diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					let defaultFirstHour = app.getMainParams('startHour');
					let explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					let defaultLastHour = app.getMainParams('endHour');
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					let now = new Date();
					startTimeString = moment(now).format(defaultTimeFormat);
					endTimeString = moment(now).add(15, 'minutes').format(defaultTimeFormat);
				}
			} else {
				endTimeString = moment(endDateInstance).add(30, 'minutes').format(defaultTimeFormat);
			}
			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			let headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction(data) {
					self.addCalendarEvent(data.result, dateFormat);
				}
			});
		});
	}
};
