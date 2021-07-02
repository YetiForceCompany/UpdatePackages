<?php
/**
 * Connector to find routing. Connector based on service YOURS.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see      https://wiki.openstreetmap.org/wiki/YOURS
 */

namespace App\Map\Routing;

/**
 * Connector for service YOURS to get routing.
 */
class Yours extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function calculate()
	{
		if (!\App\RequestUtil::isNetConnection()) {
			throw new \App\Exceptions\AppException('ERR_NO_INTERNET_CONNECTION');
		}
		$coordinates = [];
		$travel = $distance = 0;
		$description = '';
		foreach ($this->parsePoints() as $track) {
			$url = $this->url . '?format=geojson&flat=' . $track['startLat'] . '&flon=' . $track['startLon'] . '&tlat=' . $track['endLat'] . '&tlon=' . $track['endLon'] . '&lang=' . \App\Language::getLanguage() . '&instructions=1';
			\App\Log::beginProfile("GET|Yours::calculate|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url, [
				'timeout' => 60,
				'http_errors' => false,
			]);
			\App\Log::endProfile("GET|Yours::calculate|{$url}", __NAMESPACE__);
			if (200 === $response->getStatusCode()) {
				$json = \App\Json::decode($response->getBody());
			} else {
				throw new \App\Exceptions\AppException('Error with connection |' . $response->getReasonPhrase() . '|' . $response->getBody());
			}
			$coordinates = array_merge($coordinates, $json['coordinates']);
			$description .= $json['properties']['description'];
			$travel += $json['properties']['traveltime'];
			$distance += $json['properties']['distance'];
		}
		$this->geoJson = [
			'type' => 'LineString',
			'coordinates' => $coordinates,
		];
		$this->travelTime = $travel;
		$this->distance = $distance;
		$this->description = $description;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parsePoints(): array
	{
		$tracks = [];
		$startLat = $this->start['lat'];
		$startLon = $this->start['lon'];
		if (!empty($this->indirectPoints)) {
			foreach ($this->indirectPoints as $tempLon) {
				$endLon = $tempLon['lon'];
				$endLat = $tempLon['lat'];
				$tracks[] = [
					'startLat' => $startLat,
					'startLon' => $startLon,
					'endLat' => $endLat,
					'endLon' => $endLon,
				];
				$startLat = $endLat;
				$startLon = $endLon;
			}
		}
		$tracks[] = [
			'startLat' => $startLat,
			'startLon' => $startLon,
			'endLat' => $this->end['lat'],
			'endLon' => $this->end['lon'],
		];
		return $tracks;
	}
}
