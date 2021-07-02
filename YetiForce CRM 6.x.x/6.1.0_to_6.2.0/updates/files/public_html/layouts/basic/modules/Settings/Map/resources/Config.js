/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Map_Config_Js',
	{},
	{
		registerTileLayer: function () {
			let tab = $('#TileLayer');
			tab.find('input').on('change', function () {
				AppConnector.request({
					module: 'Map',
					parent: 'Settings',
					action: 'Config',
					mode: 'setTileLayer',
					vale: this.value
				})
					.done(function (data) {
						app.showNotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerCoordinates: function () {
			let tab = $('#Coordinates');
			tab.find('input').on('change', function () {
				AppConnector.request({
					module: 'Map',
					parent: 'Settings',
					action: 'Config',
					mode: 'setCoordinate',
					vale: this.value
				})
					.done(function (data) {
						app.showNotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerRouting: function () {
			let tab = $('#Routing');
			tab.find('input').on('change', function () {
				AppConnector.request({
					module: 'Map',
					parent: 'Settings',
					action: 'Config',
					mode: 'setRouting',
					vale: this.value
				})
					.done(function (data) {
						app.showNotify({
							text: data['result']['message'],
							type: 'success'
						});
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerEvents: function () {
			this.registerTileLayer();
			this.registerCoordinates();
			this.registerRouting();
		}
	}
);
