<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class
 */
class YetiForceUpdate
{

	/**
	 * @var \vtlib\PackageImport 
	 */
	public $package;

	/**
	 * @var object 
	 */
	public $modulenode;

	/**
	 * Fields to delete
	 * @var string[] 
	 */
	public $filesToDelete = [];

	/**
	 * Constructor
	 * @param object $modulenode,
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	/**
	 * Preupdate
	 */
	public function preupdate()
	{
		
	}

	/**
	 * Update
	 */
	public function update()
	{
		
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		
	}
}
