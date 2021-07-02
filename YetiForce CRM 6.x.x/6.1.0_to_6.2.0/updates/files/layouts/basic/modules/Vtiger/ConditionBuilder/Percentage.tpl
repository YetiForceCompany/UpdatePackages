{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Percentage input-group input-group-sm">
		<input class="form-control js-condition-builder-value"
			   data-js="val"
			   title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			   value="{\App\Purifier::encodeHtml($VALUE)}"
			   autocomplete="off"/>
		<span class="input-group-append row">
				<span class="input-group-text">%</span>
			</span>
	</div>
{/strip}
