{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-BlocksView -->
{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
	{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}
		{continue}
	{/if}
	{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
	{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
	{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
	{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
	{if $BLOCKS_HIDE}
		{include file=\App\Layout::getTemplatePath('Detail/BlockView.tpl', $MODULE_NAME)}
	{/if}
{/foreach}
<!-- /tpl-Base-Detail-BlocksView -->
{/strip}
