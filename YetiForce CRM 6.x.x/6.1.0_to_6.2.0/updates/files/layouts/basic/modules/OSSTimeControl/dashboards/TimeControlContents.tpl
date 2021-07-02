{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $DATA['show_chart'] }
		{assign var=SHOWING_ICON value=$TCPMODULE_MODEL->get('timeControlWidget')}
		<div class="clearfix"></div>
		<div class="widgetChartContainer"><canvas></canvas></div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
	<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}' />
{/strip}
