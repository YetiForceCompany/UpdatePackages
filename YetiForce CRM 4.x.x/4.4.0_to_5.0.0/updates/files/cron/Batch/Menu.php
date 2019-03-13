<?php

namespace Cron\Batch;

/**
 * Batch proccess to recalculate menu.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Menu
{
	public function preProcess()
	{
		return false;
	}

	public function postProcess()
	{
		(new \Settings_Menu_Record_Model())->refreshMenuFiles();
		return true;
	}
}
