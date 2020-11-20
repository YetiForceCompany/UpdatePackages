<?php
/**
 * The file contains: Description class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\Products;

/**
 * Class Description.
 */
class RecordsTree extends \Api\Portal\BaseModule\RecordsList
{
	/**
	 * Permission type.
	 *
	 * @var int
	 */
	private $permissionType;

	/**
	 * Is user permissions.
	 *
	 * @var bool
	 */
	private $isUserPermissions;

	/**
	 * Parent record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $parentRecordModel;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->permissionType = (int) \App\User::getCurrentUserModel()->get('permission_type');
		$this->isUserPermissions = \Api\Portal\Privilege::USER_PERMISSIONS === $this->permissionType;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createQuery(): void
	{
		if ($this->isUserPermissions) {
			parent::createQuery();
		} else {
			if ($parent = $this->getParentCrmId()) {
				$this->parentRecordModel = \Vtiger_Record_Model::getInstanceById($parent, 'Accounts');
				$pricebookId = $this->parentRecordModel->get('pricebook_id');
				if (empty($pricebookId)) {
					parent::createQuery();
				} else {
					parent::createQuery();
					$this->queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
					$this->queryGenerator->addJoin([
						'LEFT JOIN',
						'vtiger_pricebookproductrel',
						"vtiger_pricebookproductrel.pricebookid={$pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid"]
					);
				}
			} else {
				parent::createQuery();
			}
		}
		$storage = $this->getUserStorageId();
		if ($storage) {
			$this->queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock as storage_qtyinstock');
			$this->queryGenerator->addJoin([
				'LEFT JOIN',
				'u_#__istorages_products',
				"u_#__istorages_products.crmid={$storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid"]
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function isRawData(): bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRecordFromRow(array $row): array
	{
		$record = parent::getRecordFromRow($row);
		$unitPrice = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
		$regionalTaxes = $availableTaxes = '';
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP';
		} else {
			if (isset($this->parentRecordModel)) {
				$availableTaxes = $this->parentRecordModel->get('accounts_available_taxes');
				$regionalTaxes = $this->parentRecordModel->get('taxes');
			}
			if (!empty($row['listprice'])) {
				$unitPrice = $row['listprice'];
			}
		}
		$record['unit_price'] = \CurrencyField::convertToUserFormatSymbol($unitPrice);
		$taxParam = \Api\Portal\Record::getTaxParam($availableTaxes, $row['taxes'], $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$record['unit_gross'] = \CurrencyField::convertToUserFormatSymbol($unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']));
		return $record;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRawDataFromRow(array $row): array
	{
		$row = parent::getRawDataFromRow($row);
		$unitPrice = $row['unit_price'] = (new \Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
		$regionalTaxes = $availableTaxes = '';
		if ($this->isUserPermissions) {
			$availableTaxes = 'LBL_GROUP';
		} else {
			if (isset($this->parentRecordModel)) {
				$availableTaxes = $this->parentRecordModel->get('accounts_available_taxes');
				$regionalTaxes = $this->parentRecordModel->get('taxes');
			}
			if (!empty($row['listprice'])) {
				$unitPrice = $row['unit_price'] = $row['listprice'];
			}
		}
		$taxParam = \Api\Portal\Record::getTaxParam($availableTaxes, $row['taxes'], $regionalTaxes);
		$taxConfig = \Vtiger_Inventory_Model::getTaxesConfig();
		$row['unit_gross'] = $unitPrice + (new \Vtiger_Tax_InventoryField())->getTaxValue($taxParam, $unitPrice, (int) $taxConfig['aggregation']);
		$row['qtyinstock'] = $row['storage_qtyinstock'] ?? 0;
		return $row;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getColumnNames(): array
	{
		$headers = parent::getColumnNames();
		$headers['unit_gross'] = \App\Language::translate('LBL_GRAND_TOTAL');
		return $headers;
	}
}
