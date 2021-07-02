<?php
/**
 * CalDAV Cron Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Calendar_CalDav_Cron class.
 */
class Calendar_CalDav_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Log::trace('Start cron CalDAV');
		$dav = new API_DAV_Model();
		$davUsers = API_DAV_Model::getAllUser(2);
		foreach (Users_Record_Model::getAll() as $id => $user) {
			if (isset($davUsers[$id])) {
				$user->set('david', $davUsers[$id]['david']);
				$user->set('calendarsid', $davUsers[$id]['calendarsid']);
				$user->set('groups', \App\User::getUserModel($id)->getGroups());
				$dav->davUsers[$id] = $user;
				\App\Log::trace(__METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				\App\Log::info(__METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CalDAV_Model();
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->calDavCrm2Dav();
		if ($this->checkTimeout()) {
			return;
		}
		$cardDav->calDav2Crm();
		\App\Log::trace('End cron CalDAV');
	}
}
