/* {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';

jQuery.Class('Faq_KnowledgeBase_Js', {
	registerEvents: function () {
		KnowledgeBase.mount({
			el: '#FaqKnowledgeBaseContainer',
			state: {
				moduleName: 'Faq'
			}
		});
	}
});
