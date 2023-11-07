<?php

/**
 * Service Model.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_SMS_Service extends Settings_WebserviceUsers_ManageConsents_Service
{
	/** {@inheritdoc} */
	public $baseTable = 'w_#__sms_user';

	/** {@inheritdoc} */
	public $baseIndex = 'id';
}
