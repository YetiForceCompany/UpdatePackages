<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o
 * *********************************************************************************** */

class Vtiger_DocumentsFileUpload_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/DocumentsFileUpload.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$truncateValue = \App\TextParser::textTruncate($value, $this->getFieldModel()->get('maxlengthtext'));

		$truncateValue = \App\Purifier::encodeHtml($truncateValue);
		if ($recordModel && !empty($value) && $recordModel->getValueByField('filestatus')) {
			if ('I' === $recordModel->getValueByField('filelocationtype')) {
				$fileId = (new App\Db\Query())->select(['attachmentsid'])
					->from('vtiger_seattachmentsrel')->where(['crmid' => $record])->scalar();
				if ($fileId) {
					$value = '<a href="file.php?module=Documents&action=DownloadFile&record=' . $record . '&fileid=' . $fileId . '"' .
							' title="' . \App\Language::translate('LBL_DOWNLOAD_FILE', 'Documents') . '" >' . $truncateValue . '</a>';
				}
			} else {
				$value = \App\Purifier::encodeHtml($value);
				$value = '<a href="' . $value . '" target="_blank" title="' . \App\Language::translate('LBL_DOWNLOAD_FILE', 'Documents') . '" rel="noreferrer noopener">' . $truncateValue . '</a>';
			}
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (null === $value) {
			$fileName = (new App\Db\Query())->select(['filename'])->from('vtiger_notes')->where(['notesid' => $this->id])->one();
			if ($fileName) {
				return App\Purifier::decodeHtml($fileName);
			}

			return '';
		}
		return App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
