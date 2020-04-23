{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-YetiForce-Shop-ProductPremium -->
	{assign var=PRODUCT_ALERT value=$PRODUCT->showAlert()}
	<div class="dashboardWidget marketplace-product mt-3 mr-3 flex-grow-1 js-product position-relative" data-js="showProductModal | click | container" data-product="{$PRODUCT->getName()}">
		{if !empty($PRODUCT->expirationDate) && $PRODUCT_ALERT}
			<span class="text-danger fas fa-exclamation animated flash infinite slow mr-1 mt-1 u-cursor-pointer js-popover-tooltip position-absolute u-position-r-0" data-toggle="popover" data-js="popover | mouseenter"
			data-content="{\App\Language::translate($PRODUCT_ALERT, $QUALIFIED_MODULE)}"></span>
		{/if}
		<div class="o-small-product pl-2 {if empty($PRODUCT->expirationDate)}bg-light u-bg-light-darken{elseif $PRODUCT_ALERT}bg-danger{else}bg-yellow{/if}">
			<div class="o-small-product__container d-flex u-min-h-120px-rem no-wrap py-2 px-1{if !empty($PRODUCT->expirationDate)} bg-white u-bg-white-darken{/if}">
				<div class="o-small-product__img d-flex">
					{if $PRODUCT->getImage()}
						<img src="{$PRODUCT->getImage()}" class="my-auto grow thumbnail-image card-img-top intrinsic-item"
							alt="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" title="{\App\Purifier::encodeHtml($PRODUCT->getLabel())}" />
					{else}
						<div class="product-no-image m-auto">
								<span class="fa-stack fa-2x product-no-image">
										<i class="fas fa-camera fa-stack-1x"></i>
										<i class="fas fa-ban fa-stack-2x"></i>
								</span>
						</div>
					{/if}
				</div>
				<div class="py-0 pl-2 pr-3 d-flex flex-wrap justify-between align-items-center w-100">
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetTitle.tpl', $QUALIFIED_MODULE) TITLE=$PRODUCT->getLabel() CLASS="u-cursor-pointer js-text-search"}
					{include file=\App\Layout::getTemplatePath('DashBoard/WidgetDescription.tpl', $QUALIFIED_MODULE) DESCRIPTION=$PRODUCT->getIntroduction() CLASS="mb-0"}
					{if empty($PRODUCT->expirationDate)}
						<button class="btn btn-dark btn-block mt-auto js-buy-modal" data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
							{if 'manual'===$PRODUCT->getPriceType()}
								{\App\Language::translate("LBL_SUPPORT_US", $QUALIFIED_MODULE)}
							{else}
								{$PRODUCT->getPrice()} {$PRODUCT->currencyCode} / {\App\Language::translate($PRODUCT->getPeriodLabel(), $QUALIFIED_MODULE)}
							{/if}
						</button>
					{else}
						{if $PRODUCT_ALERT}
							<button class="btn btn-danger btn-block mt-auto js-buy-modal"
							data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}">
								{\App\Language::translate('LBL_SHOP_RENEW', $QUALIFIED_MODULE)}
							</button>
						{else}
							<button class="btn btn-block bg-yellow mt-auto js-buy-modal"
							data-js="showBuyModal | click" data-product="{$PRODUCT->getName()}" disabled>
								{\App\Fields\Date::formatToDisplay($PRODUCT->expirationDate)}
							</button>
						{/if}
					{/if}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-YetiForce-Shop-ProductPremium -->
{/strip}
