{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{assign var=ICON value=Vtiger_Menu_Model::getMenuIcon($MENU, Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE))}
	{if (isset($MENU['active']) && $MENU['active']) || $PARENT_MODULE == $MENU['id']}
		{assign var=ACTIVE value='true'}
	{else}
		{assign var=ACTIVE value='false'}
	{/if}
	<li class="tpl-menu-Label c-menu__item js-menu__item nav-item menuLabel {if !$HASCHILDS}hasParentMenu{/if}" data-id="{$MENU['id']}" data-js="mouseenter mouseleave">
		<a class="nav-link {if $ACTIVE=='true'}active{else}collapsed{/if}{if $ICON} hasIcon{/if}{if $HASCHILDS == 'true'} js-submenu-toggler is-submenu-toggler{/if}" href="#"
				{if $HASCHILDS == 'true'} data-toggle="collapse" data-target="#submenu-{$MENU['id']}" role="button" aria-haspopup="true" aria-expanded="{$ACTIVE}" aria-controls="submenu-{$MENU['id']}"{else} role="heading"{/if} >
			{$ICON}
			<span class="c-menu__item__text js-menu__item__text" title="{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}" data-js="class: u-white-space-n">{Vtiger_Menu_Model::vtranslateMenu($MENU['name'],$MENU_MODULE)}</span>
			{if $HASCHILDS == 'true'}
				<span class="toggler" aria-hidden="true"><span class="fas fa-plus-circle"></span><span class="fas fa-minus-circle"></span></span>
			{/if}
		</a>
		{include file=\App\Layout::getTemplatePath('menu/SubMenu.tpl', $MODULE)}
	</li>
{/strip}
