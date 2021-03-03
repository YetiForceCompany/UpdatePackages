{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{assign var="PARAMS" value=$FIELD_MODEL->getFieldParams()}
	<div class="tpl-Edit-Field-Date input-group {$WIDTHTYPE_GROUP} date">
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		<input name="{$FIELD_MODEL->getFieldName()}" class="{if !$FIELD_MODEL->isEditableReadOnly()}dateField datepicker{/if} form-control"
		title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text"
		{if $PARAMS && $PARAMS['onChangeCopyValue']}data-copy-to-field="{$PARAMS['onChangeCopyValue']}"{/if} data-date-format="{$dateFormat}"
		value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}" tabindex="{$FIELD_MODEL->getTabIndex()}"
		data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		{if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}'{/if} data-fieldinfo='{$FIELD_INFO}'
			   {if !empty($MODE) && $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} autocomplete="off"/>
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>

{/strip}
