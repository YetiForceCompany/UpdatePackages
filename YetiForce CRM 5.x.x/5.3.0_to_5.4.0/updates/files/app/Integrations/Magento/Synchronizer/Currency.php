<?php

/**
 * Synchronization currencies file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Synchronization currencies class.
 */
class Currency extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		try {
			if ($currency = $this->getCurrenciesFromApi()) {
				$crm = array_column(\App\Fields\Currency::getAll(), 'id', 'currency_code');
				$supported = \App\Fields\Currency::getSupported();
				$dbCommand = \App\Db::getInstance()->createCommand();
				foreach ($currency['available_currency_codes'] as $code) {
					if (empty($crm[$code])) {
						if (isset($supported[$code])) {
							$dbCommand->insert('vtiger_currency_info', [
								'currency_name' => $supported[$code]['currency_name'],
								'currency_code' => $code,
								'currency_symbol' => $supported[$code]['currency_symbol'],
								'conversion_rate' => 1,
								'currency_status' => 'Active',
							])->execute();
						} else {
							$this->log('Currency is not supported by the system: ' . $code);
							\App\Log::error('Currency is not supported by the system: ' . $code, 'Integrations/Magento');
						}
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->log('Import currencies', $ex);
			\App\Log::error('Error during import currencies: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
	}

	/**
	 * Method to get customers form Magento.
	 *
	 * @return array
	 */
	public function getCurrenciesFromApi(): array
	{
		return \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/directory/currency')) ?? [];
	}
}
