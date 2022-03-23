<?php

/**
 * Watching Model Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Watchdog_Model extends Vtiger_Base_Model
{

	const RECORD_ACTIVE = 1;

	protected static $cacheFile = 'user_privileges/watchdogModule.php';
	protected static $cache = false;
	protected $watchingUsers = false;
	private $isAcive = true;
	public $noticeDefaultType = 'PLL_SYSTEM';

	/**
	 * Function to get the instance by id
	 * @param int $record
	 * @param int|string $moduleName
	 * @param int $userId
	 * @return Vtiger_Watchdog_Model
	 */
	public static function getInstanceById($record, $moduleName, $userId = false)
	{
		$instance = self::getInstance($moduleName, $userId);
		$instance->set('record', $record);
		return $instance;
	}

	/**
	 * Function to get the instance by module
	 * @param int|string $moduleName
	 * @param int $userId
	 * @return Vtiger_Watchdog_Model
	 */
	public static function getInstance($moduleName, $userId = false)
	{
		$moduleId = false;
		if (is_numeric($moduleName)) {
			$moduleId = $moduleName;
			$moduleName = \App\Module::getModuleName($moduleName);
		}
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserId();
		}
		$cacheName = $moduleName . $userId;
		if (\App\Cache::staticHas('WatchdogModel', $cacheName)) {
			return \App\Cache::staticGet('WatchdogModel', $cacheName);
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Watchdog', $moduleName);
		$instance = new $modelClassName();
		$instance->set('module', $moduleName);
		$instance->set('moduleId', $moduleId ? $moduleId : \App\Module::getModuleId($moduleName));
		$instance->set('userId', $userId);
		if (static::$cache === false) {
			static::$cache = require static::$cacheFile;
		}
		if (AppConfig::module('ModTracker', 'WATCHDOG') === false) {
			$instance->isActive = false;
		}
		\App\Cache::staticSave('WatchdogModel', $cacheName, $instance);
		return $instance;
	}

	/**
	 * Function checks if module is watched
	 * @return boolean
	 */
	public function isWatchingModule()
	{
		if (!$this->isActive()) {
			return false;
		}
		$tabId = $this->get('moduleId');
		return isset(static::$cache[$tabId][$this->get('userId')]);
	}

	/**
	 * Function verifies if module is active
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->isAcive;
	}

	/**
	 * Function verifies if module is locked
	 * @return boolean
	 */
	public function isLock($moduleId = false)
	{
		$userId = $this->get('userId');
		if (empty($moduleId)) {
			$moduleId = $this->get('moduleId');
		}
		return isset(static::$cache[$moduleId][$userId]) ? (bool) static::$cache[$moduleId][$userId] : false;
	}

	/**
	 * Function verifies if module is watching in database
	 * @return boolean
	 */
	public function isWatchingModuleConfig($member)
	{
		$isExists = (new \App\Db\Query())
			->from('u_#__watchdog_module')
			->where(['member' => $member, 'module' => $this->get('moduleId')])
			->exists();
		return $isExists;
	}

	/**
	 * Function verifies if rekord is watching
	 * @return boolean
	 */
	public function isWatchingRecord()
	{
		if (!$this->isActive()) {
			return false;
		}
		$userId = $this->get('userId');
		$cacheName = $userId . '_' . $this->get('record');
		if (\App\Cache::staticHas('isWatchingRecord', $cacheName)) {
			return (bool) \App\Cache::staticGet('isWatchingRecord', $cacheName);
		}
		$return = $this->isWatchingModule($userId);

		$state = (new \App\Db\Query())->select('state')->from('u_#__watchdog_record')->where(['userid' => $userId, 'record' => $this->get('record')])->scalar();
		$this->set('isRecordExists', false);
		if ($state !== false) {
			$this->set('isRecordExists', true);
			$return = $state;
		}
		$return = intval($return);
		\App\Cache::staticSave('isWatchingRecord', $cacheName, $return);
		return $return;
	}

	/**
	 * Function get watching modules
	 * @param int $userId - User ID
	 * @return array - List of modules
	 */
	public static function getWatchingModules($userId = false)
	{
		if ($userId === false) {
			$userId = \App\User::getCurrentUserId();
		}
		if (\App\Cache::staticHas('getWatchingModules', $userId)) {
			return \App\Cache::staticGet('getWatchingModules', $userId);
		}
		$modules = [];
		if (static::$cache === false) {
			static::$cache = require static::$cacheFile;
		}
		foreach (static::$cache as $moduleId => $users) {
			if (isset($users[$userId])) {
				$modules[] = $moduleId;
			}
		}
		\App\Cache::staticSave('getWatchingModules', $userId, $modules);
		return $modules;
	}

	/**
	 * Function get watching modules by schedule
	 * @param int $ownerId - User ID
	 * @return array - List of modules
	 */
	public static function getWatchingModulesSchedule($ownerId = false, $isName = false)
	{
		if ($ownerId === false) {
			$ownerId = \App\User::getCurrentUserId();
		}
		$cacheName = $ownerId . '_' . intval($isName);
		if (\App\Cache::staticHas('getWatchingModulesSchedule', $cacheName)) {
			return \App\Cache::staticGet('getWatchingModulesSchedule', $cacheName);
		}
		$data = (new \App\Db\Query())
			->from('u_#__watchdog_schedule')
			->where(['userid' => $ownerId])
			->one();
		$data['modules'] = explode(',', $data['modules']);
		if ($isName) {
			foreach ($data['modules'] as $key => &$moduleId) {
				$moduleId = \App\Module::getModuleName($moduleId);
			}
		}
		\App\Cache::staticSave('getWatchingModulesSchedule', $cacheName, $data);
		return $data;
	}

	/**
	 * Function to change the state of the observed record
	 * @param int $state
	 * @return int|boolean
	 */
	public function changeRecordState($state)
	{
		$isWatchingRecord = $this->isWatchingRecord();
		if ($isWatchingRecord && $state === self::RECORD_ACTIVE) {
			return true;
		}
		$db = \App\Db::getInstance();
		$row = ['state' => $state];
		if (!$this->get('isRecordExists')) {
			$row['userid'] = $this->get('userId');
			$row['record'] = $this->get('record');
			return $db->createCommand()->insert('u_#__watchdog_record', $row)->execute();
		} else {
			return $db->createCommand()->update(('u_#__watchdog_record'), $row, ['userid' => $this->get('userId'), 'record' => $this->get('record')])->execute();
		}
	}

	/**
	 * Function to change the state of the observed module
	 * @param int $state
	 * @param string $member
	 * @return int|boolean
	 */
	public function changeModuleState($state, $member = false)
	{
		$config = false;
		if (empty($member)) {
			$member = 'Users:' . $this->get('userId');
			$isExists = $this->isWatchingModule();
		} else {
			$isExists = $this->isWatchingModuleConfig($member);
		}
		$state = intval($state);
		if ($isExists && $state === 1) {
			return true;
		}

		$db = \App\Db::getInstance();
		$moduleId = $this->get('moduleId');
		if ($state === 1) {
			return $db->createCommand()->insert('u_#__watchdog_module', [
					'member' => $member,
					'module' => $moduleId
				])->execute();
		} else {
			return $db->createCommand()->delete('u_#__watchdog_module', ['member' => $member, 'module' => $moduleId])->execute();
		}
	}

	/**
	 * Function to change the state of the locked module
	 * @param int $state
	 * @param string $member
	 * @return int
	 */
	public function lock($state, $member)
	{
		return App\Db::getInstance()
				->createCommand()
				->update('u_#__watchdog_module', ['lock' => $state], ['member' => $member, 'module' => $this->get('moduleId')])
				->execute();
	}

	/**
	 * Function to change the exceptions of the module
	 * @param array|string $exceptions
	 * @param string $member
	 * @return int
	 */
	public function exceptions($exceptions, $member)
	{
		if (is_array($exceptions)) {
			$exceptions = implode(',', $exceptions);
		}
		return \App\Db::getInstance()
				->createCommand()
				->update('u_#__watchdog_module', ['exceptions' => $exceptions], ['member' => $member, 'module' => $this->get('moduleId')])
				->execute();
	}

	/**
	 * Function to set user's schedule
	 * @param array $sendNotifications
	 * @param int $frequency
	 * @param int $ownerId
	 * @return int
	 */
	public static function setSchedulerByUser($sendNotifications, $frequency, $ownerId = false)
	{
		if ($ownerId === false) {
			$ownerId = \App\User::getCurrentUserId();
		}
		$db = \App\Db::getInstance();
		if (empty($sendNotifications)) {
			$db->createCommand()->delete('u_#__watchdog_schedule', ['userid' => $ownerId])->execute();
		} else {
			if (is_array($sendNotifications)) {
				$sendNotifications = implode(',', $sendNotifications);
			}
			$isExists = (new \App\Db\Query())->from('u_#__watchdog_schedule')->where(['userid' => $ownerId])->exists();
			if ($isExists) {
				$db->createCommand()->update('u_#__watchdog_schedule', ['frequency' => $frequency, 'modules' => $sendNotifications], ['userid' => $ownerId])->execute();
			} else {
				$db->createCommand()->insert('u_#__watchdog_schedule', ['frequency' => $frequency, 'modules' => $sendNotifications, 'userid' => $ownerId])->execute();
			}
		}
	}

	/**
	 * Function get watching users
	 * @param array $restrictUsers
	 * @return array - List of users
	 */
	public function getWatchingUsers($restrictUsers = [])
	{
		if (!$this->watchingUsers) {
			$users = $this->getModuleUsers();
			if ($this->has('record')) {
				$dataReader = (new App\Db\Query())->select(['userid', 'state'])
						->from('u_#__watchdog_record')
						->where(['record' => (int) $this->get('record')])
						->createCommand()->query();
				while ($row = $dataReader->read()) {
					if ($row['state'] === self::RECORD_ACTIVE) {
						$users[$row['userid']] = $row['userid'];
					} else {
						unset($users[$row['userid']]);
					}
				}
			}
			$this->watchingUsers = $users;
		} else {
			$users = $this->watchingUsers;
		}
		if ($restrictUsers) {
			foreach ($restrictUsers as $user) {
				if (isset($users[$user])) {
					unset($users[$user]);
				}
			}
		}
		return $users;
	}

	/**
	 * Function get watching members
	 * @param boolean $getData
	 * @return array - List of members
	 */
	public function getWatchingMembers($getData = false)
	{
		$query = (new App\Db\Query())
			->select(['member', 'lock', 'exceptions'])
			->from('u_#__watchdog_module')
			->where(['module' =>  (int)$this->get('moduleId')]);
		if ($getData) {
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$data = explode(':', $row['member']);
				switch ($data[0]) {
					case 'Users':
						$name = \App\Fields\Owner::getUserLabel($data[1]);
						break;
					case 'Groups':
						$name = \App\Language::translate(\App\Fields\Owner::getGroupName($data[1]), $this->get('module'));
						break;
					default:
						$name = \App\Language::translate(\App\PrivilegeUtil::getRoleName($data[1]), $this->get('module'));
						break;
				}
				$row['type'] = $data[0];
				$row['name'] = $name;
				$row['exceptions'] = explode(',', $row['exceptions']);
				$members[] = $row;
			}
		} else {
			$members = $query->column();
		}
		return $members;
	}

	/**
	 * Function get watching exceptions
	 * @param string $member
	 * @return array - List of exceptions
	 */
	public function getWatchingExceptions($member)
	{
		$exceptions = (new App\Db\Query())
				->select(['exceptions'])
				->from('u_#__watchdog_module')
				->where(['module' => \App\Module::getModuleId($this->get('module')), 'member' => $member])->scalar();
		return explode(',', $exceptions);
	}

	/**
	 * Update watchdog module permissions cache.
	 */
	public static function reloadCache()
	{
		$members = $users = [];
		$dataReader = (new App\Db\Query())->from('u_#__watchdog_module')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$type = explode(':', $row['member']);
			$exceptions = explode(',', $row['exceptions']);
			$users = \App\PrivilegeUtil::getUserByMember($row['member']);
			if (!empty($exceptions)) {
				$users = array_diff($users, $exceptions);
			}
			if (isset($members[$row['module']])) {
				$members[$row['module']]['byUsers'] = array_merge($members[$row['module']]['byUsers'], $users);
			} else {
				$members[$row['module']]['byUsers'] = $users;
			}
			$members[$row['module']][$type[0]] = array_fill_keys($users, $row['lock']);
		}
		$cache = [];
		foreach ($members as $module => $usersByType) {
			$users = array_unique($usersByType['byUsers']);
			foreach ($users as $user) {
				if (isset($usersByType['Users']) && isset($usersByType['Users'][$user])) {
					$cache[$module][$user] = $usersByType['Users'][$user];
				} elseif (isset($usersByType['Groups']) && isset($usersByType['Groups'][$user])) {
					$cache[$module][$user] = $usersByType['Groups'][$user];
				} elseif (isset($usersByType['Roles']) && isset($usersByType['Roles'][$user])) {
					$cache[$module][$user] = $usersByType['Roles'][$user];
				} elseif (isset($usersByType['RoleAndSubordinates']) && isset($usersByType['RoleAndSubordinates'][$user])) {
					$cache[$module][$user] = $usersByType['RoleAndSubordinates'][$user];
				}
			}
		}
		$content = '<?php return ' . \vtlib\Functions::varExportMin($cache) . ';' . PHP_EOL;
		file_put_contents(static::$cacheFile, $content, LOCK_EX);
	}

	/**
	 * Function get users
	 * @return array - List of users
	 */
	public function getModuleUsers()
	{
		$tabid = $this->get('moduleId');
		$users = [];
		if (isset(static::$cache[$tabid])) {
			$usersKey = array_keys(static::$cache[$tabid]);
			$users = array_combine($usersKey, $usersKey);
		}
		return $users;
	}

	/**
	 * Function get supported modules
	 * @return array - List of modules
	 */
	public static function getSupportedModules()
	{
		return Vtiger_Module_Model::getAll([0], ['SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter', 'Notification'], true);
	}
}
