{strip}
    {*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
    {include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailTop'}
    {include file=\App\Layout::getTemplatePath('Detail/BlocksView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
    {if $MODULE_TYPE == '1'}
        {include file=\App\Layout::getTemplatePath('Detail/InventoryView.tpl', $MODULE_NAME) MODULE_NAME=$MODULE_NAME}
    {/if}
    {include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='DetailBottom'}
{/strip}
