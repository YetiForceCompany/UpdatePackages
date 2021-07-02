<?php
/**
 * Cron task to update coordinates.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * OSSSoldServices_Renewal_Cron class.
 */
class OpenStreetMap_UpdaterCoordinates_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$db = App\Db::getInstance();
		$lastUpdatedCrmId = (new App\Db\Query())->select(['crmid'])
			->from('u_#__openstreetmap_address_updater')
			->scalar();
		if (false !== $lastUpdatedCrmId) {
			$dataReader = (new App\Db\Query())->select(['crmid', 'setype', 'deleted'])
				->from('vtiger_crmentity')
				->where(['setype' => \App\Config::module('OpenStreetMap', 'ALLOW_MODULES', [])])
				->andWhere(['>', 'crmid', $lastUpdatedCrmId])
				->limit(App\Config::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES'))
				->createCommand()->query();
			$moduleModel = OpenStreetMap_Module_Model::getInstance('OpenStreetMap');
			$coordinatesConnector = \App\Map\Coordinates::getInstance();
			while ($row = $dataReader->read()) {
				if ($moduleModel->isAllowModules($row['setype']) && 0 == $row['deleted']) {
					$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid']);
					foreach (\App\Map\Coordinates::TYPE_ADDRES as $typeAddress) {
						$addressInfo = \App\Map\Coordinates::getAddressParams($recordModel, $typeAddress);
						$coordinatesDetails = $coordinatesConnector->getCoordinates($addressInfo);
						if (false === $coordinatesDetails) {
							break;
						}
						if (empty($coordinatesDetails)) {
							continue;
						}
						$coordinatesDetails = reset($coordinatesDetails);
						$coordinate = [
							'lat' => $coordinatesDetails['lat'],
							'lon' => $coordinatesDetails['lon'],
						];
						$isCoordinateExists = (new App\Db\Query())->from('u_#__openstreetmap')->where(['type' => $typeAddress, 'crmid' => $recordModel->getId()])->exists();
						if ($isCoordinateExists) {
							if (empty($coordinate['lat']) && empty($coordinate['lon'])) {
								$db->createCommand()->delete('u_#__openstreetmap', ['type' => $typeAddress, 'crmid' => $recordModel->getId()])->execute();
							} else {
								$db->createCommand()->update('u_#__openstreetmap', $coordinate, ['type' => $typeAddress, 'crmid' => $recordModel->getId()])->execute();
							}
						} else {
							if (!empty($coordinate['lat']) && !empty($coordinate['lon'])) {
								$coordinate['type'] = $typeAddress;
								$coordinate['crmid'] = $recordModel->getId();
								$db->createCommand()->insert('u_#__openstreetmap', $coordinate)->execute();
							}
						}
					}
				}
				$lastUpdatedCrmId = $row['crmid'];
				if ($this->checkTimeout()) {
					break;
				}
			}
			$dataReader->close();
			$lastRecordId = $db->getUniqueID('vtiger_crmentity', 'crmid', false);
			if ($dataReader->count() || $lastRecordId === $lastUpdatedCrmId) {
				$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
				$this->cronTask->updateStatus(\vtlib\Cron::$STATUS_DISABLED);
				$this->cronTask->set('lockStatus', true);
			} else {
				$db->createCommand()->update('u_#__openstreetmap_address_updater', ['crmid' => $lastUpdatedCrmId])->execute();
			}
		}
	}
}
