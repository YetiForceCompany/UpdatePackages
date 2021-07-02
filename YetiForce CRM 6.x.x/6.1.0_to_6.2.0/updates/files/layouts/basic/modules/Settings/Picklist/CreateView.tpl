{*
<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
<div class='tpl-Settings-Picklist-CreateView modelContainer modal fade basicCreateView' tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{\App\Language::translate('LBL_ADD_ITEM_TO', $QUALIFIED_MODULE)}
					&nbsp;{\App\Language::translate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form name="addItemForm" class="form-horizontal" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}"/>
				<input type="hidden" name="parent" value="Settings"/>
				<input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}"/>
				<input type="hidden" name="action" value="SaveAjax"/>
				<input type="hidden" name="mode" value="add"/>
				<input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}"/>
				<input type="hidden" name="pickListValues"
						value='{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_PICKLISTFIELD_ALL_VALUES))}'/>
				{assign var=FIELD_INFO value=$SELECTED_PICKLIST_FIELDMODEL->getFieldInfo()}
				<div class="modal-body tabbable">
					<div class="form-group row align-items-center">
						<div class="col-md-3 col-form-label text-right">
							<span class="redColor">*</span>
							{\App\Language::translate('LBL_ITEM_VALUE',$QUALIFIED_MODULE)}
						</div>
						<div class="col-md-9 controls">
							<input class="form-control" type="text"
									data-prompt-position="topLeft:70"
									data-validation-engine="validate[required, maxSize[{$FIELD_INFO['maximumlength']}], funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
									data-validator='{\App\Purifier::encodeHtml(\App\Json::encode([['name'=>'FieldLabel']]))}' name="newValue">
						</div>
					</div>
					{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
						<div class="form-group row align-items-center">
							<div class="col-md-3 col-form-label text-right">{\App\Language::translate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</div>
							<div class="col-md-9 controls">
								<select class="rolesList form-control" name="rolesSelected[]" multiple="multiple"
										data-placeholder="{\App\Language::translate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
									<option value="all"
											selected>{\App\Language::translate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
									{foreach from=$ROLES_LIST item=ROLE}
										<option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
					<div class="form-group row align-items-center">
						<div class="col-md-3 col-form-label text-right">
							{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
							<span class="js-popover-tooltip fas fa-info-circle ml-2" data-js="popover"  data-trigger="hover focus" data-content="{\App\Language::translate('LBL_DESCRIPTION_VALUE_LIST', $QUALIFIED_MODULE)}" data-original-title="" title=""></span>
						</div>
						<div class="col-md-9 controls">
							<textarea class="form-control js-editor" name="description"
										data-js="ckeditor"></textarea>
						</div>
					</div>
					<div class="form-group row align-items-center">
						<div class="col-md-3 col-form-label text-right">
							{\App\Language::translate('LBL_PREFIX',$QUALIFIED_MODULE)}
							<span class="js-popover-tooltip fas fa-info-circle ml-2" data-js="popover"  data-trigger="hover focus" data-content="{\App\Language::translate('LBL_DESCRIPTION_PREFIXES', $QUALIFIED_MODULE)}" data-original-title="" title=""></span>
						</div>
						<div class="col-md-9 controls">
							<input name="prefix" type="text"
									class="form-control"
									value="">
						</div>
					</div>
				</div>
				{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</form>
		</div>
	</div>
</div>
{/strip}
