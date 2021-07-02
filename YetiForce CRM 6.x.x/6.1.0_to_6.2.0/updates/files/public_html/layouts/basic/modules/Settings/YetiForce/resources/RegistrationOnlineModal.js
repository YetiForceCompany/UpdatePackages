/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_YetiForce_RegistrationOnlineModal_Js',
	{
		/**
		 * Register events for form checkbox element
		 */
		registerNewsletter() {
			const form = $('[data-view="RegistrationOnlineModal"]').find('form');
			form.find('[id$="newsletter"]').on('click', (e) => {
				let inputsContainer = $(e.target).closest('.js-card-body');
				if ($(e.target).prop('checked')) {
					inputsContainer.find('[id$="firstname]"]').attr('data-validation-engine', 'validate[required]');
					inputsContainer.find('[id$="lastname]"]').attr('data-validation-engine', 'validate[required]');
					inputsContainer.find('[id$="email]"]').attr('data-validation-engine', 'validate[required,custom[email]]');
					inputsContainer.find('.js-newsletter-content').removeClass('d-none');
				} else {
					inputsContainer.find('[id$="firstname]"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('[id$="lastname]"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('[id$="email]"]').removeAttr('data-validation-engine').val('');
					inputsContainer.find('.js-newsletter-content').addClass('d-none');
				}
			});
		},
		getCompanies(form) {
			let companies = [];
			form.each(function () {
				companies.push($(this).serializeFormData());
			});
			return companies;
		},
		registerEvents() {
			const self = this;
			const container = $("[data-view='RegistrationOnlineModal']");
			const form = container.find('form');
			form.validationEngine(app.validationEngineOptions);
			form.on('submit', function (e) {
				e.preventDefault();
				container.find('[name="saveButton"]').click();
			});
			container.find('[name="saveButton"]').on('click', function (e) {
				if (!form.validationEngine('validate')) {
					e.preventDefault();
					app.showNotify({
						text: app.vtranslate('JS_ENTER_ALL_REGISTRATION_DATA'),
						type: 'error'
					});
					return false;
				}
				container.find('button[name=saveButton]').prop('disabled', true);
				let progress = $.progressIndicator({
					message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'YetiForce',
					parent: 'Settings',
					action: 'Register',
					mode: 'online',
					companies: self.getCompanies(form)
				})
					.done(function (data) {
						app.showNotify({
							text: data['result']['message'],
							type: data['result']['type']
						});
						progress.progressIndicator({ mode: 'hide' });
						if (data['result']['type'] === 'success') {
							let href = location.href.replace('displayModal=online', '');
							location.replace(href);
						}
						container.find('button[name=saveButton]').prop('disabled', false);
						return data['result'];
					})
					.fail(function (error, title) {
						progress.progressIndicator({ mode: 'hide' });
						app.showNotify({
							title: title,
							text: error,
							type: 'error'
						});
					});
			});
			this.registerNewsletter();
		}
	},
	{}
);
Settings_YetiForce_RegistrationOnlineModal_Js.registerEvents();
