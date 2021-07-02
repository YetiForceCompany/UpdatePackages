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
{strip}
{if !empty($CUSTOM_VIEWS)}
	<div class="relatedContainer listViewPageDiv m-0 js-list__form" data-js="container">
		{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
		{assign var=RELATION_MODEL value=$VIEW_MODEL->getRelationModel()}
		<input type="hidden" name="emailEnabledModules" value=true/>
		<input type="hidden" id="view" value="{$VIEW}"/>
		<input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}"/>
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}"/>
		<input type="hidden" id="orderBy" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}">
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries"/>
		<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'/>
		<input type="hidden" id="recordsCount" value=""/>
		<input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids="{if $SELECTED_IDS === 'all'}all{else}{App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}{/if}"/>
		<input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids="{App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}"/>
		<input type="hidden" id="recordsCount" name="recordsCount"/>
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'/>
		<input type="hidden" id="autoRefreshListOnChange" value="{App\Config::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
		<input type="hidden" id="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}">
		<div class="relatedHeader">
			<div class="btn-toolbar row">
				<div class="col-lg-9">
					{if isset($RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS'])}
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS'] TEXT_HOLDER='LBL_ACTIONS' BTN_ICON='fa fa-list' CLASS='btn-group mr-sm-1 relatedViewGroup c-btn-block-sm-down mb-1 mb-sm-0'}
					{/if}
					<div class="btn-group col-md-3 mb-2">
						<span class="customFilterMainSpan">
							{if isset($CUSTOM_VIEWS)}
								<select id="customFilter" class="col-md-12"
										data-placeholder="{\App\Language::translate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE->getName())}">
									{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
										<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
											{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
												<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}"
														value="{$CUSTOM_VIEW->get('cvid')}"
														class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}"
														data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE->getName())} {\App\Language::translate($RELATED_MODULE->getName(), $RELATED_MODULE->getName())}{else}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE->getName())}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
								<span class="filterImage">
									<span class="fas fa-filter"></span>
								</span>
							{else}
								<input type="hidden" value="0" id="customFilter"/>
							{/if}
						</span>
					</div>
					<div class="btn-group pr-2 mb-2">
						<button type="button" class="btn btn-light loadFormFilterButton js-popover-tooltip"
								data-js="popover"
								data-content="{\App\Language::translate('LBL_LOAD_RECORDS_INFO',$MODULE)}">
							<span class="fas fa-filter"></span>&nbsp;
							<strong>{\App\Language::translate('LBL_LOAD_RECORDS',$MODULE)}</strong>
						</button>
					</div>
					{if isset($RELATED_LIST_LINKS['LISTVIEWBASIC'])}
						{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
							<div class="btn-group pr-2 mb-2">
								{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
								{assign var=IS_SEND_EMAIL_BUTTON value={$RELATED_LINK->get('_sendEmail')}}
								<button type="button" class="btn btn-light addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE_NAME} {if !empty($RELATED_LINK->linkqcs) && $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
										{if $IS_SELECT_BUTTON eq true} data-moduleName='{$RELATED_LINK->get('_module')->get('name')}'{/if}
										{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
										{if $IS_SEND_EMAIL_BUTTON eq true}    onclick="{$RELATED_LINK->getUrl()}" {else} data-url="{$RELATED_LINK->getUrl()}"{/if}
										{if ($IS_SELECT_BUTTON eq false) and ($IS_SEND_EMAIL_BUTTON eq false)}
										name="addButton">
									{else}
									> {* closing the button tag *}
									{/if}
									{if $RELATED_LINK->get('linkicon') neq ''}
									<span class="{$RELATED_LINK->get('linkicon')}"></span>
									{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong>
								</button>
							</div>
						{/foreach}
					{/if}
				</div>
				<div class="col-lg-3 mb-2">
					<div class="float-right">
						{if $VIEW_MODEL}
							<div class="float-right pl-1">
								{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
								<input type="hidden" class="entityState"
										value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}"/>
								<div class="dropdown dropdownEntityState u-remove-dropdown-icon">
									<button class="btn btn-light dropdown-toggle" type="button"
											id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true"
											aria-expanded="true">
										{if $VIEW_MODEL->get('entityState') === 'Archived'}
											<span class="fas fa-archive"></span>
										{elseif $VIEW_MODEL->get('entityState') === 'Trash'}
											<span class="fas fa-trash-alt"></span>
										{elseif $VIEW_MODEL->get('entityState') === 'All'}
											<span class="fas fa-bars"></span>
										{else}
											<span class="fas fa-undo-alt"></span>
										{/if}
									</button>
									<ul class="dropdown-menu dropdown-menu-right"
										aria-labelledby="dropdownEntityState">
										<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
											<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') === 'Active'} active{/if}"
												href="#" data-value="Active">
												<span class="fas fa-undo-alt mr-1"></span>&nbsp;
												{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}
											</a>
										</li>
										<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Archived'} active{/if}"
												href="#" data-value="Archived">
												<span class="fas fa-archive mr-1"></span>
												{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
											</a>
										</li>
										<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Trash'} active{/if}"
												href="#" data-value="Trash">
												<span class="fas fa-trash-alt mr-1"></span>
												{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
											</a>
										</li>
										<li>
											<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'All'} active{/if}"
												href="#" data-value="All">
												<span class="fas fa-bars mr-1"></span>
												{\App\Language::translate('LBL_ALL')}
											</a>
										</li>
									</ul>
								</div>
							</div>
						{/if}
					</div>
					{assign var=CUSTOM_VIEW_LIST value=$RELATION_MODEL->getCustomViewList()}
					{if $CUSTOM_VIEW_LIST}
						<div class="d-flex justify-content-start">
							{if count($CUSTOM_VIEW_LIST) === 1}
								<input type="hidden" class="js-relation-cv-id" value="{array_key_first($CUSTOM_VIEW_LIST)}" data-js="value" />
							{else}
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text">
											<span class="fas fa-filter"></span>
										</div>
									</div>
									<div class="input-group-append">
										<select class="form-control select2 js-relation-cv-id" data-js="change|select2|value">
											{foreach key=CV_ID item=CV_NAME from=$CUSTOM_VIEW_LIST}
												<option value="{$CV_ID}" {if $CV_ID == $VIEW_MODEL->get('cvId')}selected{/if}>{$CV_NAME}</option>
											{/foreach}
										</select>
									</div>
								</div>
							{/if}
						</div>
					{/if}
					<div class="paginationDiv pl-1 d-flex justify-content-end">
						{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE) VIEWNAME='related'}
					</div>
				</div>
			</div>
		</div>
		<div id="selectAllMsgDiv" class="alert-block msgDiv">
			<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}
					&nbsp;{\App\Language::translate($RELATED_MODULE_NAME)}&nbsp;(<span
							id="totalRecordsCount"></span>)</a></strong>
		</div>
		<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
			<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
		</div>
		{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $RELATED_MODULE->getName()) MODULE_MODEL=$RELATED_MODULE}
		<div class="relatedContents">
			<table class="table tableBorderHeadBody listViewEntriesTable {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
				<thead>
				<tr class="listViewHeaders">
					<th>
						<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL')}"
								id="relatedListViewEntriesMainCheckBox"/>
					</th>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						{assign var=HEADER_FIELD_NAME value=$HEADER_FIELD->getFullName()}
						<th nowrap class="{if isset($ORDER_BY[$HEADER_FIELD_NAME])} columnSorted{/if}">
							{if $HEADER_FIELD->getColumnName() eq 'access_count' or $HEADER_FIELD->getColumnName() eq 'idlists' }
								<a href="javascript:void(0);"
									class="noSorting">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE_NAME)}</a>
							{elseif $HEADER_FIELD->getColumnName() eq 'time_start'}
							{else}
								<span class="listViewHeaderValues float-left  {if $HEADER_FIELD->isListviewSortable()} js-change-order u-cursor-pointer{/if}"
									data-nextsortorderval="{if isset($ORDER_BY[$HEADER_FIELD_NAME]) && $ORDER_BY[$HEADER_FIELD_NAME] eq \App\Db::ASC}{\App\Db::DESC}{else}{\App\Db::ASC}{/if}"
									data-columnname="{$HEADER_FIELD_NAME}"
									data-js="click">
									{$HEADER_FIELD->getFullLabelTranslation($RELATED_MODULE)}
									{if isset($ORDER_BY[$HEADER_FIELD_NAME])}
										&nbsp;&nbsp;<span class="fas {if $ORDER_BY[$HEADER_FIELD_NAME] eq \App\Db::DESC}fa-chevron-down{else}fa-chevron-up{/if}"></span>
									{/if}
								</span>
							{/if}
						</th>
					{/foreach}
				</tr>
				</thead>
				{if $RELATED_MODULE->isQuickSearchEnabled()}
					<tr>
						<td class="listViewSearchTd">
							<div class="flexWrapper">
								<a class="btn btn-light" role="button" data-trigger="listSearch"
									href="javascript:void(0);">
									<span class="fas fa-search"
											title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</a>
								<button type="button" class="btn btn-light removeSearchConditions">
									<span class="fas fa-times"
											title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
								</button>
							</div>
						</td>
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<td>
								{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
								{assign var=HEADER_FIELD_NAME value=$HEADER_FIELD->getName()}
								{if isset($SEARCH_DETAILS[$HEADER_FIELD_NAME])}
									{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
								{else}
									{assign var=SEARCH_INFO value=[]}
								{/if}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $RELATED_MODULE->getName())
								FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE}
							</td>
						{/foreach}
					</tr>
				{/if}
				{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
					{assign var="RECORD_COLORS" value=$RELATED_RECORD->getListViewColor()}
					<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}'
						data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
						<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}"
							{if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
							<div>
								<input type="checkbox" value="{$RELATED_RECORD->getId()}"
										title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}"
										class="relatedListViewEntriesCheckBox"/>
							</div>
							{if !empty($IS_FAVORITES)}
								{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
								<div class="ml-1">
									<a class="favorites btn btn-light btn-sm" data-state="{$RECORD_IS_FAVORITE}">
									<span class="fas fa-star {if !$RECORD_IS_FAVORITE}d-none{/if}"
											title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}"></span>
										<span class="far fa-star {if $RECORD_IS_FAVORITE}d-none{/if}"
												title="{\App\Language::translate('LBL_ADD_TO_FAVORITES', $MODULE)}"></span>
									</a>
								</div>
							{/if}
							<div class="actions">
								<div class="dropright u-remove-dropdown-icon">
									<button class="btn btn-sm btn-light toolsAction dropdown-toggle"
											type="button" data-toggle="dropdown" aria-haspopup="true"
											aria-expanded="false">
											<span class="fas fa-wrench"
													title="{\App\Language::translate('LBL_ACTIONS')}"></span>
									</button>
									<div class="dropdown-menu"
											aria-label="{\App\Language::translate('LBL_ACTIONS')}">
										<div class="c-btn-link btn-group mr-1">
											<a role="button" class="btn btn-sm btn-default"
												href="{$RELATED_RECORD->getFullDetailViewUrl()}">
													<span class="fas fa-th-list align-middle"
															title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}"></span>
											</a>
										</div>
										{if $IS_EDITABLE}
											<div class="c-btn-link btn-group mr-1">
												<a role="button" class="btn btn-sm btn-default"
													href='{$RELATED_RECORD->getEditViewUrl()}'>
														<span class="yfi yfi-full-editing-view align-middle"
																title="{\App\Language::translate('LBL_EDIT', $MODULE)}"></span>
												</a>
											</div>
										{/if}
										{if $IS_DELETABLE}
											<div class="c-btn-link btn-group">
												<button type="button"
														class="relationDelete btn btn-sm btn-danger entityStateBtn">
														<span class="fas fa-trash-alt align-middle"
																title="{\App\Language::translate('LBL_DELETE', $MODULE)}"></span>
												</button>
											</div>
										{/if}
									</div>
								</div>
							</div>
						</td>
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
							<td nowrap class="{$WIDTHTYPE}">
								{if ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') && $RELATED_RECORD->isViewable()}
									<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
								{elseif $RELATED_HEADERNAME eq 'access_count'}
									{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
								{elseif $RELATED_HEADERNAME eq 'time_start'}
								{else}
									{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
{else}
	{include file=\App\Layout::getTemplatePath('RelatedList.tpl')}
{/if}
{/strip}
