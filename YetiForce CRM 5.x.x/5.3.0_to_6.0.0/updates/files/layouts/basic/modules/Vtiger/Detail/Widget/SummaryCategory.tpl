{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-SummaryCategory -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="tpl-Detail-Widget-SummaryCategory c-detail-widget c-detail-widget--summmary-category mb-1 js-detail-widget recordDetails"
	data-js="container">
	{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex align-items-center py-1 w-100">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
					data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>

				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
						{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
				</div>
			</div>
		</div>
	{/if}
	<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse"
		data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
		<div class="mx-2">
			{foreach item=SUMMARY_CATEGORY from=$RECORD->getSummaryInfo()}
				<div class="row text-center o-summary-category__row">
					{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
						<div class="o-summary-category__card col-md-3" data-reference="{$FIELD_VALUE.reference}">
							<div class="o-summary-category__card__body mb-3 bg-light rounded py-2">
								<div>
									<label class="o-summary-category__card__label small">
										{\App\Language::translate($FIELD_VALUE.name,$MODULE_NAME)}
									</label>
								</div>
								<div>
									<label class="o-summary-category__card__label h5">
										{if !empty($FIELD_VALUE.data)}{$FIELD_VALUE.data}{else}0{/if}
									</label>
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-SummaryCategory -->
{/strip}
