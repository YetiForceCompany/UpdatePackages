{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Projects-Hierarchy modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="modCT_Project yfm-Project mr-2"></span>
						{\App\Language::translate('LBL_SHOW_HIERARCHY', $MODULE_NAME)}
					</h5>
					<button type="button" class="close" data-dismiss="modal"
							title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="hierarchyScroll">
						<table class="table table-bordered">
							<thead>
							<tr class="blockHeader">
								{foreach item=HEADERNAME from=$HIERARCHY['header']}
									<th class="text-center text-nowrap">{\App\Language::translate($HEADERNAME, $MODULE_NAME)}</th>
								{/foreach}
							</tr>
							</thead>
							{foreach item=ENTRIES from=$HIERARCHY['entries']}
								<tbody>
								<tr>
									{foreach item=LISTFIELDS from=$ENTRIES}
										<td class="text-nowrap">{$LISTFIELDS}</td>
									{/foreach}
								</tr>
								</tbody>
							{/foreach}
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<div class="float-right cancelLinkContainer">
						<button class="btn btn-warning" type="reset" data-dismiss="modal">
							<strong>{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
