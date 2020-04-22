<?php
/**
 * Dashboard class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal;

use App\Db\Query;
use App\Language;
use App\Module;
use OpenApi\Annotations as OA;

/**
 * Model dashboard.
 *
 * @OA\Info(
 * 		title="YetiForce API for Webservice App. Type: Portal",
 * 		version="0.1",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License v3",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *   	),
 *   	termsOfService="https://yetiforce.com/"
 * )
 */
class Dashboard
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName;

	/**
	 * Type of dashboard.
	 *
	 * @var int
	 */
	public $dashboardType;

	/**
	 * Id application.
	 *
	 * @var int
	 */
	public $application;

	/**
	 * Function to get instance.
	 *
	 * @param string $moduleName
	 * @param int    $dashboardType
	 * @param int    $application
	 */
	public static function getInstance(string $moduleName, int $dashboardType, int $application): self
	{
		$instance = new static();
		$instance->moduleName = $moduleName;
		$instance->dashboardType = $dashboardType;
		$instance->application = $application;
		return $instance;
	}

	/**
	 * Gets tabs.
	 *
	 * @return array
	 */
	public function getTabs()
	{
		$tabs = [];
		$dataReader = (new \App\Db\Query())->select(['u_#__dashboard_type.*'])->from('u_#__dashboard_type')
			->innerJoin('vtiger_module_dashboard_blocks', 'u_#__dashboard_type.dashboard_id = vtiger_module_dashboard_blocks.dashboard_id')
			->where(['vtiger_module_dashboard_blocks.authorized' => $this->application])
			->distinct()->createCommand()->query();
		while ($dashboard = $dataReader->read()) {
			$tabs[] = [
				'name' => \App\Language::translate($dashboard['name'], $this->moduleName),
				'id' => $dashboard['dashboard_id'],
				'system' => $dashboard['system']
			];
		}
		return $tabs;
	}

	/**
	 * Return data about all added widgets in this dashboard.
	 *
	 * @return array
	 */
	public function getData()
	{
		$dataReader = (new Query())->select(['vtiger_module_dashboard.*', 'vtiger_links.linklabel'])
			->from('vtiger_module_dashboard')
			->innerJoin('vtiger_module_dashboard_blocks', 'vtiger_module_dashboard_blocks.id = vtiger_module_dashboard.blockid')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard.linkid')
			->where([
				'vtiger_module_dashboard_blocks.dashboard_id' => $this->dashboardType,
				'vtiger_module_dashboard_blocks.tabid' => Module::getModuleId($this->moduleName),
				'vtiger_module_dashboard_blocks.authorized' => $this->application,
			])
			->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$row['linkid'] = $row['id'];
			if ('Mini List' === $row['linklabel']) {
				$minilistWidgetModel = new \Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel(\Vtiger_Widget_Model::getInstanceFromValues($row));
				$headers = $records = [];
				$headerFields = $minilistWidgetModel->getHeaders();
				foreach ($headerFields as $fieldName => $fieldModel) {
					$headers[$fieldName] = Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
				}
				foreach ($minilistWidgetModel->getRecords('all') as $recordModel) {
					foreach ($headerFields as $fieldName => $fieldModel) {
						$records[$recordModel->getId()][$fieldName] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
					}
				}
				$widgets[] = [
					'type' => $row['linklabel'],
					'data' => [
						'title' => \App\Language::translate($minilistWidgetModel->getTitle(), $minilistWidgetModel->getTargetModule()),
						'modulename' => $minilistWidgetModel->getTargetModule(),
						'headers' => $headers,
						'records' => $records
					]
				];
			} elseif ('ChartFilter' == $row['linklabel']) {
				$chartFilterWidgetModel = new \Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel(\Vtiger_Widget_Model::getInstanceFromValues($row));
				$widgets[] = [
					'type' => $row['linklabel'],
					'data' => [
						'title' => $chartFilterWidgetModel->getTitle(),
						'modulename' => $chartFilterWidgetModel->getTargetModule(),
						'stacked' => $chartFilterWidgetModel->isStacked() ? 1 : 0,
						'colorsFromDividingField' => $chartFilterWidgetModel->areColorsFromDividingField() ? 1 : 0,
						'filterIds' => $chartFilterWidgetModel->getFilterIds(),
						'typeChart' => $chartFilterWidgetModel->getType(),
						'widgetData' => $chartFilterWidgetModel->getChartData(),
					]
				];
			}
		}
		return $widgets;
	}
}
