{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-LayoutEditor-Modals-CreateSystemFields -->
<div class="modal-body">
	{if $FIELDS}
		{App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}<br />
		<select class="select2 form-control js-system-fields" data-js="value">
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
				<option value="{$FIELD_NAME}">{{$FIELD_MODEL->getFullLabelTranslation()}}</option>
			{/foreach}
		</select>
	{else}
		<div class="alert alert-warning text-center" role="alert">
			<span class="fas fa-info-circle u-mr-5px mr-2"></span><strong>{App\Language::translate('LBL_NO_FIELDS_ADD', $QUALIFIED_MODULE)}</strong>
		</div>
	{/if}
</div>
<!-- /tpl-Settings-LayoutEditor-Modals-CreateSystemFields -->
{/strip}
