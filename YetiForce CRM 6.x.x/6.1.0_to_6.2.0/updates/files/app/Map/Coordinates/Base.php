<?php
/**
 * Class to get coordinates.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Map\Coordinates;

/**
 * Base Connector to get coordinates.
 */
abstract class Base
{
	/**
	 * API url.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Construct.
	 *
	 * @param array $provider
	 */
	public function __construct(array $provider)
	{
		$this->url = $provider['apiUrl'];
	}

	/**
	 * Function to get coordinates from base information about address.
	 *
	 * @param array $addressInfo
	 *
	 * @return bool|string[]
	 */
	abstract public function getCoordinates(array $addressInfo);

	/**
	 * Function to get coordinates from string.
	 *
	 * @param string $value
	 *
	 * @return bool|string[]
	 */
	abstract public function getCoordinatesByValue(string $value);
}
