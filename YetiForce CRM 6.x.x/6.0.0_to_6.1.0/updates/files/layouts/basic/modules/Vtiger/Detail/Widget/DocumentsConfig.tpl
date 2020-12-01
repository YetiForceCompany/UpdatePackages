{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-DocumentsConfig -->
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-modalAddWidget form-horizontal validateForm">
					{if !empty($WID)}<input type="hidden" name="wid" value="{$WID}" />{/if}
					<input type="hidden" name="type" value="{$TYPE}"/>
					<div class="modal-header">
						<h5 id="massEditHeader" class="modal-title">
							<span class="fas fa-plus mr-1"></span>{\App\Language::translate('Add widget', $QUALIFIED_MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal"
								aria-label="{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						{if empty($WIDGETINFO['data']['relatedmodule'])}
							{assign var=RELATED_MODULE_ID value=\App\Module::getModuleId('Documents')}
						{else}
							{assign var=RELATED_MODULE_ID value=$WIDGETINFO['data']['relatedmodule']}
						{/if}
						{if empty($WIDGETINFO['data']['relation_id'])}
							{assign var=RELATED_ID value=0}
						{else}
							{assign var=RELATED_ID value=$WIDGETINFO['data']['relation_id']}
						{/if}
						<div class="form-container-sm">
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Type widget', $QUALIFIED_MODULE)}
									:</label>
								<div class="col-md-7 col-form-label">
									{\App\Language::translate($TYPE, $QUALIFIED_MODULE)}
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">
									{\App\Language::translate('Label', $QUALIFIED_MODULE)}:
								</label>
								<div class="col-md-7 py-1">
									<input name="label" class="form-control form-control-sm" data-validation-engine="validate[required]" type="text" value="{$WIDGETINFO['label']}"/>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Related module', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top" data-content="{\App\Language::translate('Related module info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Related module', $QUALIFIED_MODULE)}">
									   <i class="fas fa-info-circle"></i>
									</a>:
								</label>
								<div class="col-md-7 py-1">
									<select name="relation_id"{if $RELATED_ID} readonly="readonly"{/if} class="select2 form-control form-control-sm" data-validation-engine="validate[required]">
										{foreach from=$RELATEDMODULES item=item key=key}
											{if $item['name'] neq 'Documents'} {continue} {/if}
											<option value="{$item['relation_id']}" {if $RELATED_ID == $item['relation_id']}selected{/if} data-relatedmodule="{$item['related_tabid']}">
												{\App\Language::translate($item['label'], $item['name'])}
											</option>
										{/foreach}
									</select>
									<input name="relatedmodule" type="hidden" value="{$RELATED_MODULE_ID}"/>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECTING_FIELDS', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('LBL_SELECTING_FIELDS_INFO', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('LBL_SELECTING_FIELDS', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<select name="relatedfields" multiple class="select2 form-control form-control-sm" data-validation-engine="validate[required]" data-select-cb="registerSelectSortable">
										{foreach from=$RELATEDMODULES item=RELATED_MODULE key=key}
												{foreach from=Vtiger_Module_Model::getInstance($RELATED_MODULE['name'])->getFieldsByBlocks() key=BLOCK_NAME item=FIELDS}
													<optgroup label="{\App\Language::translate($RELATED_MODULE['label'], $RELATED_MODULE['name'])}:{\App\Language::translate($BLOCK_NAME, $RELATED_MODULE['name'])}" data-module="{$RELATED_MODULE['related_tabid']}">
														{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME}
															{assign var=VALUE_NAME value="{$RELATED_MODULE['related_tabid']}::{$FIELD_NAME}"}
															<option value="{$VALUE_NAME}"{' '}
																	{if !empty($WIDGETINFO['data']['relatedfields']) && in_array($VALUE_NAME, $WIDGETINFO['data']['relatedfields'])}
																		selected="selected" data-sort-index="{array_search($VALUE_NAME, $WIDGETINFO['data']['relatedfields'])}"
																	{/if} data-module="{$RELATED_MODULE['related_tabid']}">
																{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $RELATED_MODULE['name'])}
															</option>
														{/foreach}
													</optgroup>
												{/foreach}
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_VIEW_TYPE', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('LBL_VIEW_TYPE_INFO', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('LBL_VIEW_TYPE', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<select name="viewtype" class="select2">
										<option value="List"
												{if !empty($WIDGETINFO['data']['viewtype']) && $WIDGETINFO['data']['viewtype'] == 'List'}selected{/if}>{\App\Language::translate('LBL_LIST', $QUALIFIED_MODULE)}</option>
										<option value="Summary"
												{if !empty($WIDGETINFO['data']['viewtype']) && $WIDGETINFO['data']['viewtype'] == 'Summary'}selected{/if}>{\App\Language::translate('LBL_SUMMARY', $QUALIFIED_MODULE)}</option>
										<option value="ListWithSummary"
												{if !empty($WIDGETINFO['data']['viewtype']) && $WIDGETINFO['data']['viewtype'] == 'ListWithSummary'}selected{/if}>{\App\Language::translate('LBL_LIST_WITH_SUMMARY', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('Limit entries info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Limit entries', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<input name="limit" class="form-control form-control-sm" type="text"
										   data-validation-engine="validate[required,custom[integer],min[1]]"
										   value="{$WIDGETINFO['data']['limit']}"/>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini row">
								<label class="col-md-5 col-form-label">{\App\Language::translate('Add button', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('Add button info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Add button', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7">
									{assign var=ACTION_STATUS value=isset($WIDGETINFO['data']['action']) && $WIDGETINFO['data']['action'] == 1}
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary {if $ACTION_STATUS} active{/if}">
											<input type="radio" name="action"
												   id="option1" autocomplete="off" value="1"
												   {if $ACTION_STATUS}checked{/if}
											> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
										</label>
										<label class="btn btn-sm btn-outline-primary {if !$ACTION_STATUS} active{/if}">
											<input type="radio" name="action"
												   id="option2" autocomplete="off" value="0"
												   {if !$ACTION_STATUS}checked{/if}
											> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini row">
								<label class="col-md-5 col-form-label">{\App\Language::translate('Select button', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('LBL_SELECT_BUTTON_INFO', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Select button', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									{assign var=ACTION_SELECT_STATUS isset($WIDGETINFO['data']['actionSelect']) && $WIDGETINFO['data']['actionSelect'] == 1}
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary {if $ACTION_SELECT_STATUS} active{/if}">
											<input type="radio" name="actionSelect"
												   id="option1" autocomplete="off" value="1"
												   {if $ACTION_SELECT_STATUS}checked{/if}
											> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
										</label>
										<label class="btn btn-sm btn-outline-primary {if !$ACTION_SELECT_STATUS} active{/if}">
											<input type="radio" name="actionSelect"
												   id="option2" autocomplete="off" value="0"
												   {if !$ACTION_SELECT_STATUS}checked{/if}
											> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-sm form-switch-mini row">
								<label class="col-md-5 col-form-label">{\App\Language::translate('No message', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('No message info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('No message', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									{assign var=NO_RESULT_TEXT isset($WIDGETINFO['data']['no_result_text']) && $WIDGETINFO['data']['no_result_text'] == 1}
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-sm btn-outline-primary {if $NO_RESULT_TEXT} active{/if}">
											<input type="radio" name="no_result_text"
												   id="option1" autocomplete="off" value="1"
												   {if $NO_RESULT_TEXT}checked{/if}
											> {\App\Language::translate('LBL_YES', $QUALIFIED_MODULE)}
										</label>
										<label class="btn btn-sm btn-outline-primary {if !$NO_RESULT_TEXT} active{/if}">
											<input type="radio" name="no_result_text"
												   id="option2" autocomplete="off" value="0"
												   {if !$NO_RESULT_TEXT}checked{/if}
											> {\App\Language::translate('LBL_NO', $QUALIFIED_MODULE)}
										</label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-sm d-none row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('LBL_SHITCH_HEADER_INFO', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('LBL_SHITCH_HEADER', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7">
									<input type="hidden" id="switchHeader_selected"
										   value="{$WIDGETINFO['data']['switchHeader']}">
									<select name="switchHeader" class="select2 form-control form-control-sm">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Filter', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('Filter info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Filter', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<input type="hidden" id="filter_selected" value="{$WIDGETINFO['data']['filter']}">
									<select name="filter" class="select2 form-control form-control-sm">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('Switch', $QUALIFIED_MODULE)}
									<a href="#" class="js-help-info" title="" data-placement="top"
									   data-content="{\App\Language::translate('Switch info', $QUALIFIED_MODULE)}"
									   data-original-title="{\App\Language::translate('Switch', $QUALIFIED_MODULE)}"><i
												class="fas fa-info-circle"></i></a>:</label>
								<div class="col-md-7 py-1">
									<input type="hidden" id="checkbox_selected"
										   value="{$WIDGETINFO['data']['checkbox']}">
									<select name="checkbox" class="select2 form-control form-control-sm">
										<option value="-">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_EMAIL_TEMPLATE', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 py-1">
									<select name="email_template" class="select2 form-control form-control-sm">
										<option value="0">{\App\Language::translate('None', $QUALIFIED_MODULE)}</option>
										{foreach from=App\Mail::getTemplateList(\App\Module::getModuleName($SOURCE),'PLL_RECORD') key=key item=item}
											<option {if isset($WIDGETINFO['data']['email_template']) && $WIDGETINFO['data']['email_template'] eq $item['id']}selected=""{/if}
												value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}
											</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group form-group-sm row">
								<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SHOW_DOC_RELATIONS', $QUALIFIED_MODULE)}:</label>
								<div class="col-md-7 py-1">
									{assign var=VALUE value=[]}
									{if !empty($WIDGETINFO['data']['fromRelation'])}
										{assign var=VALUE value=$WIDGETINFO['data']['fromRelation']}
										{assign var=SETTYPE value=settype($VALUE, 'array')}
									{/if}
									<select name="fromRelation" multiple="multiple" class="select2 form-control form-control-sm">
										{foreach from=$WIDGET->getRelations($SOURCE) key=key item=item}
											<option {if in_array($key, $VALUE)}selected=""{/if}
												value="{\App\Purifier::encodeHtml($key)}">{$item['translate']} ({\App\Language::translate($item['label'], $item['moduleName'])})
											</option>
										{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-Detail-Widget-DocumentsConfig -->
{/strip}
