{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var="ANNOUNCEMENTS" value=Vtiger_Module_Model::getInstance('Announcements')}
	{if $ANNOUNCEMENTS->checkActive()}
		{include file=\App\Layout::getTemplatePath('Announcement.tpl', $MODULE_NAME)}
	{/if}
	{assign var=LEFTPANELHIDE value=$USER_MODEL->get('leftpanelhide')}
	<div class="container-fluid container-fluid-main o-{$MODULE_NAME|lower}-{$VIEW|lower}-container">
		<div class="o-base-container js-base-container c-menu--animation {if $LEFTPANELHIDE} c-menu--open{/if}" data-js="container | class: c-menu--animation">
			<div class="js-sidebar c-menu__container noSpaces" data-js="class: .js-expand">
				{include file=\App\Layout::getTemplatePath('BodyLeft.tpl', $MODULE_NAME)}
			</div>
			{include file=\App\Layout::getTemplatePath('BodyHeader.tpl', $MODULE_NAME)}
			<div class="basePanel">
				<div class="mainBody">
					{include file=\App\Layout::getTemplatePath('BodyContent.tpl', $MODULE_NAME)}
				{/strip}
