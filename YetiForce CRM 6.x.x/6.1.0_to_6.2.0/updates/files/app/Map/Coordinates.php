<?php
/**
 * Class to get coordinates.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Map;

/**
 * Base Connector to get coordinates.
 */
class Coordinates
{
	/**
	 * Type of addresss.
	 */
	const TYPE_ADDRES = ['a', 'b', 'c'];

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * Function to get connector.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \App\Map\Coordinates\Base
	 */
	public static function getInstance()
	{
		if (static::$instance) {
			return static::$instance;
		}
		$coordinateProvider = \App\Config::module('OpenStreetMap', 'coordinatesServers')[\App\Config::module('OpenStreetMap', 'coordinatesServer')];
		$className = "\\App\\Map\\Coordinates\\{$coordinateProvider['driverName']}";
		if (!class_exists($className)) {
			throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
		}
		static::$instance = new $className($coordinateProvider);
		return static::$instance;
	}

	/**
	 * Function to get base information about address from Vtiger_Record_Model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $type
	 *
	 * @return string[]
	 */
	public static function getAddressParams(\Vtiger_Record_Model $recordModel, string $type)
	{
		return [
			'state' => $recordModel->get('addresslevel2' . $type),
			'county' => $recordModel->get('addresslevel3' . $type),
			'city' => $recordModel->get('addresslevel5' . $type),
			'street' => $recordModel->get('addresslevel8' . $type) . ' ' . $recordModel->get('buildingnumber' . $type),
			'country' => $recordModel->get('addresslevel1' . $type),
		];
	}

	/**
	 * Get coordinates drivers.
	 *
	 * @return string[]
	 */
	public static function getDrivers(): array
	{
		$drivers = [];
		foreach (new \DirectoryIterator(ROOT_DIRECTORY . '/app/Map/Coordinates/') as $item) {
			if ($item->isFile() && 'Base' !== $item->getBasename('.php')) {
				$drivers[] = $item->getBasename('.php');
			}
		}
		return $drivers;
	}
}
