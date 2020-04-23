<?php

namespace App;

/**
 * Event Handler main class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class EventHandler
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected static $baseTable = 'vtiger_eventhandlers';
	private static $handlerByType;
	private $recordModel;
	private $moduleName;
	private $params;
	private static $handlersInstance;
	private $exceptions;
	private static $mandatoryEventClass = ['ModTracker_ModTrackerHandler_Handler', 'Vtiger_RecordLabelUpdater_Handler'];

	/** Edit view, validation before saving */
	public const EDIT_VIEW_PRE_SAVE = 'EditViewPreSave';

	/**
	 * Get all event handlers.
	 *
	 * @param bool    $active  true/false
	 * @param ?string $groupBy
	 *
	 * @return array
	 */
	public static function getAll(?string $groupBy = null)
	{
		if (Cache::has('EventHandler', 'All')) {
			$handlers = Cache::get('EventHandler', 'All');
		} else {
			$handlers = (new \App\Db\Query())->from(self::$baseTable)->orderBy(['priority' => SORT_DESC])->all();
			Cache::save('EventHandler', 'All', $handlers);
		}
		if ($groupBy) {
			$groupHandlers = [];
			foreach ($handlers as $handler) {
				$groupHandlers[$handler[$groupBy]][$handler['eventhandler_id']] = $handler;
			}
			$handlers = $groupHandlers;
		}
		return $handlers;
	}

	/**
	 * Get active event handlers by type (event_name).
	 *
	 * @param string $name
	 * @param mixed  $moduleName
	 * @param bool   $active
	 *
	 * @return array
	 */
	public static function getByType($name, $moduleName = false): array
	{
		if (!isset(self::$handlerByType)) {
			$handlers = [];
			foreach (static::getAll() as &$handler) {
				$handlers[$handler['event_name']][$handler['handler_class']] = $handler;
			}
			self::$handlerByType = $handlers;
		}
		$handlers = self::$handlerByType[$name] ?? [];
		if ($moduleName) {
			foreach ($handlers as $key => &$handler) {
				if ((!empty($handler['include_modules']) && !\in_array($moduleName, explode(',', $handler['include_modules']))) || (!empty($handler['exclude_modules']) && \in_array($moduleName, explode(',', $handler['exclude_modules'])))) {
					unset($handlers[$key]);
				}
			}
		}
		return $handlers;
	}

	/**
	 * Register an event handler.
	 *
	 * @param string $eventName      The name of the event to handle
	 * @param string $className
	 * @param string $includeModules
	 * @param string $excludeModules
	 * @param int    $priority
	 * @param bool   $isActive
	 * @param mixed  $ownerId
	 */
	public static function registerHandler($eventName, $className, $includeModules = '', $excludeModules = '', $priority = 5, $isActive = true, $ownerId = 0)
	{
		$isExists = (new \App\Db\Query())->from(self::$baseTable)->where(['event_name' => $eventName, 'handler_class' => $className])->exists();
		if (!$isExists) {
			\App\Db::getInstance()->createCommand()
				->insert(self::$baseTable, [
					'event_name' => $eventName,
					'handler_class' => $className,
					'is_active' => $isActive,
					'include_modules' => $includeModules,
					'exclude_modules' => $excludeModules,
					'priority' => $priority,
					'owner_id' => $ownerId,
				])->execute();
			static::clearCache();
		}
	}

	/**
	 * Clear cache.
	 */
	public static function clearCache()
	{
		self::$handlerByType = null;
		Cache::delete('EventHandler', 'All');
	}

	/**
	 * Unregister a registered handler.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function deleteHandler($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, $params)->execute();
		static::clearCache();
	}

	/**
	 * Update an event handler.
	 *
	 * @param array $params
	 * @param int   $id
	 *
	 * @return void
	 */
	public static function update(array $params, int $id)
	{
		Db::getInstance()->createCommand()->update(self::$baseTable, $params, ['eventhandler_id' => $id])->execute();
		static::clearCache();
	}

	/**
	 * Set an event handler as inactive.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function setInActive($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()
			->update(self::$baseTable, ['is_active' => false], $params)->execute();
		static::clearCache();
	}

	/**
	 * Set an event handler as active.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function setActive($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, ['is_active' => true], $params)->execute();
		static::clearCache();
	}

	/**
	 * Set record model.
	 *
	 * @param \App\Vtiger_Record_Model $recordModel
	 *
	 * @return $this
	 */
	public function setRecordModel(\Vtiger_Record_Model $recordModel)
	{
		$this->recordModel = $recordModel;
		return $this;
	}

	/**
	 * Set module name.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public function setModuleName($moduleName)
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	/**
	 * Set params.
	 *
	 * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Add param.
	 *
	 * @param array $params
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function addParams($key, $value)
	{
		$this->params[$key] = $value;
	}

	/**
	 * Get record model.
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function getRecordModel()
	{
		return $this->recordModel;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Get params.
	 *
	 * @return array Additional parameters
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Set exceptions.
	 *
	 * @param array $exceptions
	 */
	public function setExceptions($exceptions)
	{
		$this->exceptions = $exceptions;
	}

	/**
	 * @param string $name Event name
	 *
	 * @return array Handlers list
	 */
	public function getHandlers($name)
	{
		$handlers = static::getByType($name, $this->moduleName);
		if ($this->exceptions) {
			if (!empty($this->exceptions['disableHandlers'])) {
				$mandatory = [];
				foreach (self::$mandatoryEventClass as &$className) {
					if (isset($handlers[$className])) {
						$mandatory[$className] = $handlers[$className];
					}
				}
				unset($handlers);
				$handlers = $mandatory;
			}
			if (!empty($this->exceptions['disableWorkflow'])) {
				unset($handlers['Vtiger_Workflow_Handler']);
			}
			if (!empty($this->exceptions['disableHandlerByName'])) {
				foreach ($this->exceptions['disableHandlerByName'] as &$className) {
					unset($handlers[$className]);
				}
			}
		}
		return $handlers;
	}

	/**
	 * Trigger an event.
	 *
	 * @param string $name Event name
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function trigger($name)
	{
		foreach ($this->getHandlers($name) as &$handler) {
			if (isset(self::$handlersInstance[$handler['handler_class']])) {
				$handlerInstance = self::$handlersInstance[$handler['handler_class']];
			} else {
				$handlerInstance = new $handler['handler_class']();
				self::$handlersInstance[$handler['handler_class']] = $handlerInstance;
			}
			$function = lcfirst($name);
			if (method_exists($handlerInstance, $function)) {
				$handlerInstance->{$function}($this);
			} else {
				Log::error("Handler not found, class: {$handler['handler_class']} | $function");
				throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND');
			}
		}
	}

	/**
	 * Trigger an event.
	 *
	 * @param array $handler
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function triggerHandler(array $handler)
	{
		$handlerInstance = new $handler['handler_class']();
		$function = lcfirst($handler['event_name']);
		$result = false;
		if (method_exists($handlerInstance, $function)) {
			$result = $handlerInstance->{$function}($this);
		} else {
			Log::error("Handler not found, class: {$handler['handler_class']} | $function");
			throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND');
		}
		return $result;
	}
}
