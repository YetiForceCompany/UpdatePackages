/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class Vtiger_ConditionBuilder_Js {
	/**
	 * Constructor
	 * @param {jQuery} container
	 */
	constructor(container, sourceModuleName, onChange) {
		this.container = container;
		this.sourceModuleName = sourceModuleName;
		if (onChange) {
			this.onChange = onChange;
		} else {
			this.onChange = () => {};
		}
	}

	/**
	 * Register change value event
	 *
	 * @param   {jQuery}  container
	 */
	registerChangeValueEvent(container) {
		container.find('.js-condition-builder-value').on('change', (e) => {
			this.onChange(this);
		});
	}

	/**
	 * Register events when change conditions
	 * @param {jQuery} container
	 */
	registerChangeConditions(container) {
		let self = this;
		container.find('.js-conditions-fields, .js-conditions-operator').on('change', function (e) {
			let progress = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let currentTarget = $(e.currentTarget);
			let requestParams = {};
			if (currentTarget.hasClass('js-conditions-fields')) {
				requestParams = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'ConditionBuilder',
					sourceModuleName: self.sourceModuleName,
					fieldname: currentTarget.val()
				};
			} else {
				requestParams = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'ConditionBuilder',
					sourceModuleName: self.sourceModuleName,
					fieldname: container.find('.js-conditions-fields').val(),
					operator: currentTarget.val()
				};
			}
			AppConnector.request(requestParams).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				container.html($(data).html());
				self.registerChangeConditions(container);
				self.registerField(container);
				self.registerChangeValueEvent(container);
				self.onChange(self);
			});
		});
	}

	/**
	 * register field types related events
	 * @param {jQuery} container
	 */
	registerField(container) {
		App.Fields.Picklist.showSelect2ElementView(container.find('select.select2'));
		App.Fields.Date.register(container, true, {}, 'js-date-field');
		App.Fields.Date.registerRange(container.find('.js-date-range-field'), { ranges: false });
		App.Fields.DateTime.register(container.find('.js-datetime-range-field'));
		app.registerEventForClockPicker($(container.find('.clockPicker')));
		App.Fields.Tree.register(container);
	}

	/**
	 * Register events to add condition
	 */
	registerAddCondition() {
		let self = this;
		this.container.on('click', '.js-condition-add', function (e) {
			let progress = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let container = $(this)
				.closest('.js-condition-builder-group-container')
				.find('> .js-condition-builder-conditions-container');
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'ConditionBuilder',
				sourceModuleName: self.sourceModuleName
			}).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				data = $(data);
				App.Fields.Picklist.showSelect2ElementView(data.find('select.select2'));
				self.registerChangeConditions(data);
				self.registerChangeValueEvent(data);
				container.append(data);
				self.onChange(self);
			});
		});
	}

	/**
	 * Register events to add group
	 */
	registerAddGroup() {
		this.container.on('click', '.js-group-add', (e) => {
			let template = this.container.find('.js-condition-builder-group-template').clone();
			template.removeClass('hide');
			$(e.target)
				.closest('.js-condition-builder-group-container')
				.find('> .js-condition-builder-conditions-container')
				.append(template.html());
			this.onChange(this);
		});
	}

	/**
	 * Register events to remove group
	 */
	registerDeleteGroup() {
		this.container.on('click', '.js-group-delete', (e) => {
			$(e.target).closest('.js-condition-builder-group-container').remove();
			this.onChange(this);
		});
	}

	/**
	 * Register events to remove condition
	 */
	registerDeleteCondition() {
		this.container.on('click', '.js-condition-delete', (e) => {
			$(e.target).closest('.js-condition-builder-conditions-row').remove();
			this.onChange(this);
		});
	}

	/**
	 * Block submit on press enter key
	 */
	registerDisableSubmitOnEnter() {
		this.container.find('.js-condition-builder-value').keydown(function (e) {
			if (e.keyCode === 13) {
				e.preventDefault();
			}
		});
	}

	/**
	 * Read conditions in group
	 * @param {jQuery} container
	 * @returns {object}
	 */
	readCondition(container) {
		let self = this;
		let condition = container.find('> .js-condition-switch .js-condition-switch-value').hasClass('active')
			? 'AND'
			: 'OR';
		let arr = {};
		arr['condition'] = condition;
		let rules = [];
		container.find('> .js-condition-builder-conditions-container >').each(function () {
			let element = $(this);
			if (element.hasClass('js-condition-builder-conditions-row')) {
				rules.push({
					fieldname: element.find('.js-conditions-fields').val(),
					operator: element.find('.js-conditions-operator').val(),
					value: element.find('.js-condition-builder-value').val()
				});
			} else if (element.hasClass('js-condition-builder-group-container')) {
				rules.push(self.readCondition(element));
			}
		});
		arr['rules'] = rules;
		return arr;
	}

	/**
	 * Returns conditions
	 */
	getConditions() {
		return this.readCondition(this.container.find('> .js-condition-builder-group-container'));
	}

	/**
	 * Main function to regsiter events
	 */
	registerEvents() {
		let self = this;
		this.registerAddCondition();
		this.registerAddGroup();
		this.registerDeleteGroup();
		this.registerDeleteCondition();
		this.registerDisableSubmitOnEnter();
		this.container.find('.js-condition-builder-conditions-row').each(function () {
			self.registerChangeConditions($(this));
			self.registerField($(this));
		});
	}
}
