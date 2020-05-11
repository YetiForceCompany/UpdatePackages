<?php
/**
 * UIType meeting url field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType MeetingUrl Field Class.
 */
class Vtiger_MeetingUrl_UIType extends Vtiger_Url_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$rawValue = $value;
		$value = \App\Purifier::encodeHtml($value);
		preg_match('^[\\w]+:\\/\\/^', $value, $matches);
		if (empty($matches[0])) {
			$value = 'http://' . $value;
		}
		if ($rawText) {
			return $value;
		}
		$moduleName = $this->getFieldModel()->getModuleName();
		$class = $meetingModalUrl = '';
		if ($record && \App\Privilege::isPermitted($moduleName, 'DetailView', $record)) {
			$meetingModalUrl = "index.php?module={$moduleName}&view=MeetingModal&record={$record}&field={$this->getFieldModel()->getName()}";
			$class = 'js-show-modal';
		}
		$rawValue = \App\TextParser::textTruncate($rawValue, \is_int($length) ? $length : 0);
		return '<a class="noLinkBtn btnNoFastEdit ' . $class . ' u-cursor-pointer" title="' . $value . '" href="' . $value . '" target="_blank" rel="noreferrer noopener" data-url="' . $meetingModalUrl . '">' . \App\Purifier::encodeHtml($rawValue) . '</a>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, false, \App\Config::main('listview_max_textlength'));
	}

	/**
	 * Gets URL.
	 *
	 * @param int|null $recordId
	 *
	 * @return string
	 */
	public function getUrl($recordId = 0): string
	{
		$fieldModel = $this->getFieldModel();
		$params = $fieldModel->getFieldParams();
		return "index.php?module={$fieldModel->getModuleName()}&action=Meeting&fieldName={$fieldModel->getName()}&record=" . ($recordId ?: '') . '&expField=' . ($params['exp'] ?? '');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MeetingUrl.tpl';
	}
}
