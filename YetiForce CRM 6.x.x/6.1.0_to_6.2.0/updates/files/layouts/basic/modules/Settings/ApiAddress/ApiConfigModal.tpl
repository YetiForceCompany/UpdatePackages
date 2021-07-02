{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-ApiAddress-ApiConfigModal -->
<div class="modal-body pb-0">
	<form class="js-form-validation">
		<div class="row no-gutters" >
			<div class="col-sm-18 col-md-12">
				<table class="table table-sm mb-0">
					<tbody class="u-word-break-all small">
						{foreach key=FIELD_NAME item=FIELD_DATA from=$PROVIDER->getCustomFields()}
							<tr>
								<td class="py-2 u-font-weight-550 align-middle border-bottom">
									{App\Language::translate('LBL_'|cat:$FIELD_NAME|upper, $QUALIFIED_MODULE)}
								</td>
								<td class="py-2 position-relative w-50 border-bottom">
									<div {if isset($FIELD_DATA['info'])}class="js-popover-tooltip" data-trigger="focus" data-toggle="popover" data-placement="top" data-trigger="focus" data-content="{App\Language::translate($FIELD_DATA['info'], $QUALIFIED_MODULE)}"{/if}>
										<div class="input-group input-group-sm position-relative">
											{if $FIELD_DATA['type'] === 'text' || $FIELD_DATA['type'] === 'url'}
												<input type="{$FIELD_DATA['type']}" class="form-control js-custom-field" placeholder="{\App\Language::translate('LBL_'|cat:$FIELD_NAME|upper|cat:'_PLACEHOLDER', $QUALIFIED_MODULE)}" name="{$FIELD_NAME}" value="{if isset($CONFIG[$FIELD_NAME])}{$CONFIG[$FIELD_NAME]}{/if}"
												data-validation-engine="validate[{if isset($FIELD_DATA['validator'])}{$FIELD_DATA['validator']}{else}funcCall[Vtiger_Base_Validator_Js.invokeValidation]{/if}]"/>
											{/if}
											{if isset($FIELD_DATA['link'])}
												<div class="input-group-append">
													<a href="{$FIELD_DATA['link']}" class="btn btn-primary btn-sm" role="button" rel="noreferrer noopener" target="_blank">
														<span class="fas fa-link"></span>
													</a>
												</div>
											{/if}
										</div>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</form>
</div>
<!-- /tpl-Settings-YetiForce-Shop-BuyModal -->
{/strip}
