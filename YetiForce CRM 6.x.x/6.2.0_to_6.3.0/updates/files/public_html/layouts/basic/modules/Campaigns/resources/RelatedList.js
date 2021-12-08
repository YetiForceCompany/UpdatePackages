/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_RelatedList_Js(
	'Campaigns_RelatedList_Js',
	{},
	{
		getCompleteParams: function () {
			var params = this._super();
			var container = this.getRelatedContainer();
			params['selectedIds'] = container.find('#selectedIds').data('selectedIds');
			params['excludedIds'] = container.find('#excludedIds').data('excludedIds');
			return params;
		}
	}
);
