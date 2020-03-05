<?php
/**
 * Nominatim driver file to get coordinates.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @see      https://wiki.openstreetmap.org/wiki/Nominatim
 */

namespace App\Map\Coordinates;

/**
 * Nominatim driver class to get coordinates.
 */
class Nominatim extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function getCoordinates(array $addressInfo)
	{
		$coordinates = false;
		if (empty($addressInfo) || !\App\RequestUtil::isNetConnection()) {
			return $coordinates;
		}
		$url = $this->url . '/?' . \http_build_query(array_merge([
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1,
		], $addressInfo));
		try {
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url);
			if (200 === $response->getStatusCode()) {
				$coordinates = \App\Json::decode($response->getBody());
			} else {
				\App\Log::error('Error with connection - ' . __CLASS__);
			}
		} catch (\Exception $ex) {
			\App\Log::error('Error - ' . __CLASS__ . ' - ' . $ex->getMessage());
		}
		return $coordinates;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCoordinatesByValue(string $value)
	{
		$coordinatesDetails = $this->getCoordinates(['q' => $value]);
		if ($coordinatesDetails) {
			$coordinatesDetails = reset($coordinatesDetails);
			return ['lat' => $coordinatesDetails['lat'], 'lon' => $coordinatesDetails['lon']];
		}
		return false;
	}
}