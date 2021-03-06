{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<div class="container-fluid" id="menuEditorContainer">
    <div class="widget_header row-fluid">
        <div class="span8"><h3>{vtranslate($MODULE_NAME, $QUALIFIED_MODULE)}</h3></div>
    </div>
    <hr>
    <div id="my-tab-content" class="tab-content" style="margin: 0 20px;" >
        <div class='editViewContainer' id="tpl" style="min-height:300px">
            <div class="row-fluid">
                <span class="span4 btn-toolbar">
                    <a class="btn addButton" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1">
                        <strong>{vtranslate('LBL_NEW_TPL', $QUALIFIED_MODULE)}</strong>
                    </a>
                </span>
                <span class="span4 btn-toolbar" >
                    <select class="chzn-select" id="moduleFilter" >
                        <option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
                        {foreach item=item key=key from=$SUPPORTED_MODULE_MODELS}
                            <option value="{$item}">{vtranslate($item, $item)}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
            <br>
            <div class="row-fluid" id="list_doc">
                <table class="table table-bordered table-condensed listViewEntriesTable">
                    <thead>
                        <tr class="listViewHeaders" >
                            <th width="30%">{vtranslate('LBL_MODULE_NAME',$QUALIFIED_MODULE)}</th>
                            <th>{vtranslate('DOC_NAME',$QUALIFIED_MODULE)}</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    {if !empty($DOC_TPL_LIST)}

                    <tbody>
                        {foreach from=$DOC_TPL_LIST item=item key=key}
                        <tr class="listViewEntries" data-id="{$item.id}">
                                <td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{vtranslate($item.module, $item.module)}</td>
                                <td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}"> {vtranslate($item.summary, $QUALIFIED_MODULE)}</td>
                                <td><a class="pull-right edit_tpl" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}"><!--<i title="{vtranslate('LBL_EDIT')}" class="icon-pencil alignMiddle"></i>--></a>
                                    <a href='index.php?module={$MODULE_NAME}&parent=Settings&action=DeleteTemplate&tpl_id={$item.id}' class="pull-right marginRight10px">
                                        <i type="{vtranslate('REMOVE_TPL', $QUALIFIED_MODULE)}" class="icon-trash alignMiddle"></i></a>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                {else}
                    <table class="emptyRecordsDiv">
                        <tbody>
                            <tr>
                                <td>
                                    {vtranslate('LBL_NO_TPL_ADDED',$QUALIFIED_MODULE)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                {/if}
            </div>
        </div>
    </div>
</div>