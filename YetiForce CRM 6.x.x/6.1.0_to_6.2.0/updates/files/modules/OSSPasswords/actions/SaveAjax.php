<?php

/**
 * OSSPasswords SaveAjax action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	public function process(App\Request $request)
	{
		\App\Log::trace('Starting Quick Edit OSSPasswords');
		// czy to 'password'????
		$isPassword = 'password' == $request->get('field') ? true : false;
		// check if password was added thrue related module view
		$isRelatedPassword = '' != $request->get('password') && '**********' != $request->get('password') ? true : false;
		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}
		// force updateing password
		if ($isPassword) {
			$recordId = $request->getInteger('record');
			$properPassword = $isPassword ? $request->get('value') : '**********';
			\App\Log::trace('recordid: ' . $recordId . ' properpass:' . $properPassword);
			// if the password is hidden, get the proper one
			if (0 == strcmp($properPassword, '**********')) {
				\App\Log::trace('Hidden password...');
				if ($config) { // when encryption is on
					\App\Log::trace('Get encrypted password.');
					$properPassword = (new \App\Db\Query())->select(['pass' => new \yii\db\Expression('AES_DECRYPT(`password`, :configKey)', [':configKey' => $config['key']])])->from('vtiger_osspasswords')->where(['osspasswordsid' => $recordId])->scalar();
				} else {  // encryption mode is off
					\App\Log::trace('Get plain text password.');
					$properPassword = (new \App\Db\Query())->select(['pass' => 'password'])->from('vtiger_osspasswords')->where(['osspasswordsid' => $recordId])->scalar();
					\App\Log::trace('Plain text pass: ' . $properPassword);
				}
			}
			$request->set('value', $properPassword);
		}
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$this->saveRecord($request);

		// apply encryption if encryption mode is on
		if ($isPassword && $config) {
			\App\Log::trace('Encrypt new password: ' . $properPassword);
			\App\Db::getInstance()->createCommand()
				->update('vtiger_osspasswords', [
					'password' => new \yii\db\Expression('AES_ENCRYPT(:properPass,:configKey)', [':properPass' => $properPassword, ':configKey' => $config['key']])
				], ['osspasswordsid' => $recordId])
				->execute();
		} // encrypt password added thrue related module
		elseif ($isRelatedPassword && $config) {
			$record = $this->record->getId();
			$properPassword = $request->get('password');
			\App\Log::trace('Encrypt new related module password: ' . $properPassword);
			\App\Db::getInstance()->createCommand()
				->update('vtiger_osspasswords', [
					'password' => new \yii\db\Expression('AES_ENCRYPT(:properPass,:configKey)', [':properPass' => $properPassword, ':configKey' => $config['key']])
				], ['osspasswordsid' => $record])
				->execute();
		}

		$fieldModelList = $this->record->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordFieldValue = $this->record->get($fieldName);
			if (\is_array($recordFieldValue) && 'multipicklist' == $fieldModel->getFieldDataType()) {
				$recordFieldValue = implode(' |##| ', $recordFieldValue);
			}
			$fieldValue = $displayValue = \App\Purifier::encodeHtml($recordFieldValue);
			if ('currency' !== $fieldModel->getFieldDataType() && 'datetime' !== $fieldModel->getFieldDataType() && 'time' !== $fieldModel->getFieldDataType() && 'date' !== $fieldModel->getFieldDataType()) {
				$displayValue = $fieldModel->getDisplayValue($fieldValue, $this->record->getId());
			}
			if ('password' === $fieldName) {
				$fieldValue = $displayValue = '**********';
			}
			$result[$fieldName] = ['value' => $fieldValue, 'display_value' => $displayValue];
		}

		$result['_recordLabel'] = $this->record->getName();
		$result['_recordId'] = $this->record->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
