{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o
********************************************************************************/
-->*}
{strip}
	<!-- /tpl-HelpDesk-DetailViewHeaderTitle -->
	{if App\Config::module('HelpDesk','CHECK_ACCOUNT_EXISTS') && $RECORD->get('parent_id') == 0}
		<div class="alert alert-danger w-100 mt-1 mb-2 mx-3" role="alert">
			<strong>{\App\Language::translate('LBL_NO_ACCOUNTS_IN_HELPDESK',{$MODULE})}</strong>
			<span class="text-right">
				<a href="javascript:HelpDesk_Detail_Js.setAccountsReference();">
					<strong> [ <span class="fas fa-search"></span> ]</strong>
				</a>
			</span>
		</div>
	{elseif App\Config::module('HelpDesk','CHECK_SERVICE_CONTRACTS_EXISTS') && Vtiger_Module_Model::getInstance('ServiceContracts')->isActive() && $RECORD->get('servicecontractsid') == 0}
		{assign var=SERVICE_CONTRACTS value=$RECORD->getActiveServiceContracts()}
		<div class="alert {if $SERVICE_CONTRACTS}alert-warning{else}alert-danger{/if} selectServiceContracts w-100 mt-1 mb-2 mx-3 d-flex flex-column flex-sm-row justify-content-between u-overflow-x-hidden" role="alert">
			{if $SERVICE_CONTRACTS}
				<strong class="u-white-space-nowrap mr-2 align-self-center">{\App\Language::translate('LBL_NO_SERVICE_CONTRACTS_IN_HELPDESK',$MODULE)}</strong>
				<ul class="nav nav-pills flex-nowrap js-scrollbar" role="tablist" data-js="scroll">
					{foreach item=ROW from=$SERVICE_CONTRACTS}
						<li role="presentation" class="btn btn-light js-popover-tooltip  mr-1" data-js="popover" data-id="{$ROW['servicecontractsid']}" title="{$ROW['subject']}" data-content="{\App\Language::translate('LBL_SET_SERVICE_CONTRACTS_REFERENCE_DESC',$MODULE)}">
							<span class="fas fa-link"></span> {$ROW['subject']} {if $ROW['due_date']}({$ROW['due_date']}){/if}
						</li>
					{/foreach}
				</ul>
			{else}
				<strong>{\App\Language::translate('LBL_ACCOUNTS_NO_ACTIVE_SERVICE_CONTRACTS',$MODULE)}</strong>
			{/if}
		</div>
	{/if}
	{include file=\App\Layout::getTemplatePath('DetailViewHeaderTitle.tpl', 'Vtiger')}
	<!-- /tpl-HelpDesk-DetailViewHeaderTitle -->
{/strip}
