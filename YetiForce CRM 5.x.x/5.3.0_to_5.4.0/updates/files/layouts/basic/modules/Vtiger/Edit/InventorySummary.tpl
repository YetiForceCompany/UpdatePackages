{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventorySummary -->
	<div class="row">
		{if $INVENTORY_MODEL->isField('discount') && $INVENTORY_MODEL->isField('discountmode')}
			<div class="col-md-4">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryDiscounts">
					<div class="card-header">
						<div class="form-row">
							<div class="col-12 col-lg-9 mb-1 p-0 u-text-ellipsis">
								 <span class="mr-1 small">
									<span class="fas fa-long-arrow-alt-down"></span>
									<span class="fas fa-percent"></span>
								</span>
								<strong>{\App\Language::translate('LBL_DISCOUNTS_SUMMARY',$MODULE)}</strong>
							</div>
							<div class="col-12 col-lg-3 p-0 groupDiscount changeDiscount  {if isset($INVENTORY_ROW['discountmode']) && $INVENTORY_ROW['discountmode'] === 1}d-none{/if}">
								<button type="button"
										class="btn btn-primary btn-sm c-btn-block-md-down float-right">{\App\Language::translate('LBL_SET_GLOBAL_DISCOUNT', $MODULE)}</button>
							</div>
						</div>
					</div>
					<div class="card-body js-panel__body m-0 p-0" data-js="value">
						<div class="form-group p-1 m-0">
							<div class="input-group">
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if $INVENTORY_MODEL->isField('currency')}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{if ($INVENTORY_MODEL->isField('tax') || $INVENTORY_MODEL->isField('tax_percent')) && $INVENTORY_MODEL->isField('taxmode')}
			<div class="col-md-4 px-1">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryTaxes">
					<div class="card-header">
						<div class="form-row">
							<div class="col-12 col-lg-9 mb-1 p-0 u-text-ellipsis">
								 <span class="mr-1 small">
									<span class="fas fa-long-arrow-alt-up"></span>
									<span class="fas fa-percent"></span>
								</span>
								<strong>{\App\Language::translate('LBL_TAX_SUMMARY',$MODULE)}</strong>
							</div>
							<div class="col-12 col-lg-3 p-0 groupTax changeTax {if isset($INVENTORY_ROW['taxmode']) && $INVENTORY_ROW['taxmode'] === 1}d-none{/if}">
								<button type="button"
										class="btn btn-primary btn-sm float-right c-btn-block-md-down">{\App\Language::translate('LBL_SET_GLOBAL_TAX', $MODULE)}</button>
							</div>
						</div>
					</div>
					<div class="card-body js-panel__body p-0 m-0 js-default-tax" data-js="data-tax-default-value|value" data-tax-default-value="{if $TAX_DEFAULT}{$TAX_DEFAULT['value']}{/if}"></div>
					<div class="card-footer js-panel__footer p-1 m-0 " data-js="value">
						<div class="form-group m-0 p-0">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">{\App\Language::translate('LBL_AMOUNT', $MODULE)}</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if $INVENTORY_MODEL->isField('currency')}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
					<div class="d-none">
						<div class="form-group m-1">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center"></div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if $INVENTORY_MODEL->isField('currency')}
										<div class="input-group-text currencySymbol">{$CURRENCY_SYMBOLAND['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card mb-3 mb-md-0 inventorySummaryContainer inventorySummaryCurrencies">
					<div class="card-header u-text-ellipsis">
						<span class="small mr-1">
							<span class="fas fa-dollar-sign"></span>
						</span>
						<strong>{\App\Language::translate('LBL_CURRENCIES_SUMMARY',$MODULE)}</strong>
					</div>
					<div class="card-body js-panel__body p-0 m-0"></div>
					<div class="card-footer js-panel__footer p-1" data-js="value">
						<div class="form-group m-0 p-0">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">{\App\Language::translate('LBL_AMOUNT', $MODULE)}</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if $INVENTORY_MODEL->isField('currency')}
										<div class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
					<div class="d-none">
						<div class="form-group m-1">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text percent u-w-85px d-flex justify-content-center">

									</div>
								</div>
								<input type="text" class="form-control text-right" readonly="readonly"/>
								<div class="input-group-append">
									{if $INVENTORY_MODEL->isField('currency')}
										<div class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</div>
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
	<!-- /tpl-Base-Edit-InventorySummary -->
{/strip}
