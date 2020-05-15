<?php
/**
 * Class to read and save configuration for integration with magento.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento;

use App\Db\Query;

/**
 * Class Config.
 */
class Config extends \App\Base
{
	/**
	 * Instance class.
	 *
	 * @var self
	 */
	private static $instance;
	/**
	 * Table name.
	 */
	private const TABLE_NAME = 'i_#__magento_config';

	/**
	 * Get all servers.
	 *
	 * @return array
	 */
	public static function getAllServers(): array
	{
		if (\App\Cache::has('Magento|getAllServers', '')) {
			return \App\Cache::get('Magento|getAllServers', '');
		}
		$servers = (new Query())->from('i_#__magento_servers')->indexBy('id')->all(\App\Db::getInstance('admin'));
		\App\Cache::save('Magento|getAllServers', '', $servers);
		return $servers;
	}

	/**
	 * Get server by id.
	 *
	 * @param int $id
	 *
	 * @return string[]
	 */
	public static function getServer(int $id): array
	{
		if (\App\Cache::has('Magento|getServer', $id)) {
			return \App\Cache::get('Magento|getServer', $id);
		}
		$server = (new \App\Db\Query())->from('i_#__magento_servers')->where(['id' => $id])->one(\App\Db::getInstance('admin')) ?: [];
		\App\Cache::save('Magento|getServer', $id, $server);
		return $server;
	}

	/**
	 * Function to get object to read configuration.
	 *
	 * @param int $serverId
	 *
	 * @return self
	 */
	public static function getInstance(int $serverId)
	{
		$servers = self::getAllServers();
		$instance = new self();
		$instance->setData(array_merge(
			$servers[$serverId],
			(new Query())->select(['name', 'value'])->from(self::TABLE_NAME)->where(['server_id' => $serverId])->createCommand()->queryAllByGroup()
			));
		return $instance;
	}

	/**
	 * Save in db last scanned id.
	 *
	 * @param string      $type
	 * @param bool|string $name
	 * @param bool|int    $id
	 *
	 * @throws \yii\db\Exception
	 */
	public function setScan(string $type, $name = false, $id = false): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (false !== $name) {
			$data = [
				'name' => "{$type}_last_scan_{$name}",
				'value' => $id
			];
		} else {
			$data = [
				'name' => $type . '_start_scan_date',
				'value' => date('Y-m-d H:i:s')
			];
		}
		if (!(new Query())->from(self::TABLE_NAME)->where(['server_id' => $this->get('id'), 'name' => $data['name']])->exists()) {
			$data['server_id'] = $this->get('id');
			$dbCommand->insert(self::TABLE_NAME, $data)->execute();
		}
		$dbCommand->update(self::TABLE_NAME, $data, ['server_id' => $this->get('id'), 'name' => $data['name']])->execute();
		$this->set($data['name'], $data['value']);
	}

	/**
	 * Set end scan.
	 *
	 * @param string $type
	 * @param string $date
	 *
	 * @throws \yii\db\Exception
	 */
	public function setEndScan(string $type, string $date): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (!$date) {
			$date = date('Y-m-d H:i:s');
		}
		$saveData = [
			[
				'name' => $type . '_end_scan_date',
				'value' => $date
			], [
				'name' => $type . '_last_scan_id',
				'value' => 0
			]
		];
		foreach ($saveData as $data) {
			if (!(new Query())->from(self::TABLE_NAME)->where(['server_id' => $this->get('id'), 'name' => $data['name']])->exists()) {
				$data['server_id'] = $this->get('id');
				$dbCommand->insert(self::TABLE_NAME, $data)->execute();
			} else {
				$dbCommand->update(self::TABLE_NAME, $data, ['server_id' => $this->get('id'), 'name' => $data['name']])->execute();
			}
			$this->set($data['name'], $data['value']);
		}
	}

	/**
	 * Get last scan information.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getLastScan(string $type): array
	{
		return [
			'id' => $this->get($type . '_last_scan_id') ?? 0,
			'start_date' => $this->get($type . '_start_scan_date') ?? false,
			'end_date' => $this->get($type . '_end_scan_date') ?? false,
		];
	}

	/**
	 * Reload integration with magento.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public static function reload(int $id): void
	{
		\App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['server_id' => $id])->execute();
	}
}
