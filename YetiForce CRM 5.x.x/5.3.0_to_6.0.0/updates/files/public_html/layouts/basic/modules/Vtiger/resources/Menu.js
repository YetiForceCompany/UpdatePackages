/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

jQuery.Class('Vtiger_Menu_Js', {
	registerMenu: function () {
		var largeNav = jQuery('#largeNavDiv nav').width();
		var tabsWidth = 0;

		jQuery('#largeNavDiv ul.nav.modulesList')
			.children('li')
			.each(function () {
				var eWidth = jQuery(this).width();
				var moreMenuElement = jQuery('#commonMoreMenu li[data-id="' + jQuery(this).data('id') + '"]');
				tabsWidth += eWidth;
				if (tabsWidth > largeNav) {
					jQuery(this).hide();
					moreMenuElement.show();
				} else {
					jQuery(this).show();
					moreMenuElement.hide();
				}
			});
		if (tabsWidth < largeNav) jQuery('#commonMoreMenu').hide();
	}
});
var menu = new Vtiger_Menu_Js();
jQuery(window).on('resize', () => {
	menu.registerMenu();
});
jQuery(function () {
	menu.registerMenu();
});
