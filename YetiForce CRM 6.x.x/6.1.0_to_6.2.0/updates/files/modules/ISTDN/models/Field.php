<?php

/**
 * Field Class for ISTDN.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ISTDN_Field_Model extends Vtiger_Field_Model
{
	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && 'istdn_status' === $this->getName()) {
			$edit = false;
		}
		return $edit;
	}
}
