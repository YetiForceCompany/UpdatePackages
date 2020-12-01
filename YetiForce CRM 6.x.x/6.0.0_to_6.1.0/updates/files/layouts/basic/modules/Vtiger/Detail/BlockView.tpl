{strip}
	{*<!--
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
<!-- tpl-Base-Detail-BlockView -->
<div class="detailViewTable">
	<div class="js-toggle-panel c-panel" data-js="click|data-dynamic" {if isset($IS_DYNAMIC) && $IS_DYNAMIC} data-dynamic="true"{/if} data-label="{$BLOCK_LABEL_KEY}">
		<div class="blockHeader c-panel__header">
			{if isset($BLOCK)}
				<div class="m-2">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right {if !($IS_HIDDEN)}d-none{/if}" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down {if $IS_HIDDEN}d-none{/if}" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}"></span>
				</div>
			{/if}
			<h5>{if !empty($BLOCK_ICON)}<span class="{$BLOCK_ICON} mr-2"></span>{/if}{\App\Language::translate($BLOCK_LABEL_KEY,$MODULE_NAME)}</h5>
		</div>
		<div class="c-panel__body blockContent {if $IS_HIDDEN}d-none{/if}">
			{assign var=COUNTER value=0}
			<div class="form-row border-bottom u-border-bottom-0-sm">
				{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
				{if !$FIELD_MODEL->isViewableInDetailView()}
					{continue}
				{/if}
				{if $FIELD_MODEL->getUIType() eq "20" or $FIELD_MODEL->getUIType() eq '300'}
					{if $COUNTER eq '1'}
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				{if $COUNTER eq 2}
			</div>
			<div class="form-row border-bottom u-border-bottom-0-sm">
				{assign var=COUNTER value=1}
				{else}
				{assign var=COUNTER value=$COUNTER+1}
				{/if}
				<div class="col-sm">
					<div class="form-row border-right h-100 align-items-start js-field-block-column{if $FIELD_MODEL->get('hideField')} d-none{/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
						<div class="fieldLabel u-border-bottom-label-md u-border-right-0-md c-panel__label {if $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '300'}  col-lg-3  {else} col-lg-6 {/if} {$WIDTHTYPE} text-right" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
							{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
							<label class="flCT_{$MODULE_NAME}_{$FIELD_MODEL->getFieldName()} u-text-small-bold">
								{\App\Language::translate({$FIELD_MODEL->getFieldLabel()},{$MODULE_NAME})}
								{if $HELPINFO_LABEL}
									<a href="#" class="js-help-info float-right u-cursor-pointer"
										title=""
										data-placement="top"
										data-content="{$HELPINFO_LABEL}"
										data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
										<span class="fas fa-info-circle"></span>
									</a>
								{/if}
							</label>
						</div>
						<div class="fieldValue u-border-bottom-value-sm col-12 d-flex align-items-center justify-content-between {$WIDTHTYPE} {if $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '300'} col-lg-9 {else} col-lg-6 {/if}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '300'} {assign var=COUNTER value=$COUNTER+1} {/if}>
							<span class="value flex-grow-1" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->getUIType() eq '20' or $FIELD_MODEL->getUIType() eq '21' or $FIELD_MODEL->getUIType() eq '300'} style="white-space:normal;" {/if}>
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD SOURCE_TPL='BlockView'}
							</span>
							{assign var=EDIT value=false}
							{if in_array($FIELD_MODEL->getName(),['date_start','due_date']) && $MODULE_NAME eq 'Calendar'}
								{assign var=EDIT value=true}
							{/if}
							{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true' && !$EDIT}
								<span class="js-detail-quick-edit u-cursor-pointer">
									<span class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}"></span>
								</span>
								<span class="d-none edit col-12 px-0">
									{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
									{if $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
										<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->getName()}' data-prev-value='{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}'/>
									{else}
										{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
										{if $FIELD_VALUE|is_array}
										{assign var=FIELD_VALUE value=\App\Json::encode($FIELD_VALUE)}
									{/if}
										<input type="hidden" class="fieldname" value='{$FIELD_MODEL->getName()}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{\App\Purifier::encodeHtml($FIELD_VALUE)}'/>
									{/if}
								</span>
							{/if}
						</div>
					</div>
				</div>
				{/foreach}
				{if $COUNTER eq 1}
					<div class="col-md-6 fieldsLabelValue"></div>
				{/if}
			</div>
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-BlockView -->
{/strip}
