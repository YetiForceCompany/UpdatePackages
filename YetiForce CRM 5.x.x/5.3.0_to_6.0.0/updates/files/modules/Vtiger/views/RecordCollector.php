<?php

/**
 * Record collector view file.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Record collector view class.
 */
class Vtiger_RecordCollector_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $showFooter = false;
	/**
	 * Record collector instance.
	 *
	 * @var \App\RecordCollectors\Base
	 */
	private $recordCollector;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->recordCollector = \App\RecordCollector::getInstance($request->getByType('collectorType', 'ClassName'), $request->getModule());
		$this->recordCollector->setRequest($request);
		$this->modalIcon = $this->recordCollector->icon;
		$this->pageTitle = $this->recordCollector->label;
		if (!$request->getMode()) {
			parent::preProcessAjax($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $request->getInteger('record'));
		$viewer->assign('RECORD_COLLECTOR', $this->recordCollector);
		$viewer->assign('COLLECTOR_NAME', $request->getByType('collectorType', 'ClassName'));
		if ('search' === $request->getMode()) {
			$viewer->assign('SEARCH_DATA', $this->recordCollector->search());
			$viewer->view('Modals/RecordCollectorSearch.tpl', $request->getModule());
		} else {
			$viewer->view('Modals/RecordCollector.tpl', $request->getModule());
		}
	}
}
