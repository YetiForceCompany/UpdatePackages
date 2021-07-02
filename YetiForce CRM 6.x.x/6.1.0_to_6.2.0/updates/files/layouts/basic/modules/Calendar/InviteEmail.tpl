{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-InviteEmail -->
	<form class="js-form" data-js="validationEngine">
		<div class="modal-body">
			<div class="alert alert-info mb-0" role="alert">
				{\App\Language::translate('LBL_ADD_PARTICIPANT', $MODULE_NAME)}
			</div>
			<div class="fieldLabel">
				<span class="redColor">*</span> {\App\Language::translate('LBL_PARTICIPANTS_EMAIL', $MODULE_NAME)}:
			</div>
			<div class="fieldValue" >
				<input type="text" class="js-invite-email-input form-control" data-validation-engine="validate[required,custom[email],funcCall[Calendar_Edit_Js.checkEmail],maxSize[100]]" />
			</div>
			<div class="fieldLabel">
				{\App\Language::translate('LBL_PARTICIPANTS_NAME', $MODULE_NAME)}:
			</div>
			<div class="fieldValue" >
				<input type="text" class="js-invite-name-input form-control" />
			</div>
		</div>
	</form>
	<!-- /tpl-Calendar-InviteEmail -->
{/strip}
