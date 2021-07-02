{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="suCount" value="{count($SWITCH_USERS)}" />
	{assign var="USERS" value=Users_Record_Model::getAll()}
	{assign var="ROLES" value=Settings_Roles_Record_Model::getAll()}
	<div class="tpl-Settings-Users-SwitchUsers widget_header row">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<span style="font-size:12px;color: black;">{\App\Language::translate('LBL_SWITCH_USERS_DESCRIPTION', $QUALIFIED_MODULE)}</span>
	<hr>
	<div class="container-fluid ">
		<div class="contents">
			<table class="switchUsersTable table table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th class="u-w-37per">{\App\Language::translate('LBL_SU_BASE_ACCESS', $QUALIFIED_MODULE)}</th>
						<th class="w-50">{\App\Language::translate('LBL_SU_AVAILABLE_ACCESS', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_TOOLS', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=SUSERS key=ID from=$MODULE_MODEL->getSwitchUsers()}
						{include file=\App\Layout::getTemplatePath('SwitchUsersItem.tpl', $QUALIFIED_MODULE) SELECT=true}
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="row">
			<button class="btn btn-info addItem"><span class="fa fa-plus u-mr-5px"></span><strong>{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
			<button class="btn btn-success saveItems"><strong><span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
		</div>
		<table class="table table-bordered cloneItem d-none">
			{assign var="SUSERS" value=[]}
			{include file=\App\Layout::getTemplatePath('SwitchUsersItem.tpl', $QUALIFIED_MODULE) SELECT=false}
		</table>
	</div>
{/strip}
