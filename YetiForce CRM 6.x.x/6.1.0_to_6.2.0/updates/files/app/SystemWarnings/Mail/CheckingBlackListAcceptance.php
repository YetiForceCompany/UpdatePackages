<?php

/**
 * Checking blacklist acceptance file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\SystemWarnings\Mail;

/**
 * Checking blacklist acceptance class.
 */
class CheckingBlackListAcceptance extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CHECK_BLACKLIST_REPORTS';

	/**
	 * Checks if there are entries on the blacklist with the status approved.
	 */
	public function process()
	{
		$count = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['type' => \App\Mail\Rbl::LIST_TYPE_BLACK_LIST, 'status' => 0])->count();
		if (0 === $count) {
			$this->status = 1;
		} else {
			$this->status = 0;
			$this->description = \App\Language::translateArgs('LBL_CHECK_BLACKLIST_REPORTS_DESC', 'Settings:SystemWarnings', $count);
			if (\App\Security\AdminAccess::isPermitted('MailRbl')) {
				$this->link = 'index.php?parent=Settings&module=MailRbl&view=Index';
				$this->linkTitle = \App\Language::translate('MailRbl', 'Settings:MailRbl');
			}
		}
	}
}
