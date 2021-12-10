<?php

/**
 * Export to spreadsheet model file.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Export to spreadsheet model class.
 */
class Vtiger_ExportToSpreadsheet_Model extends \App\Export\ExportRecords
{
	protected $headers;
	protected $workBook;
	protected $workSheet;
	protected $headerStyles;
	protected $colNo = 1;
	protected $rowNo = 1;
	protected $invNo = 0;

	/** {@inheritdoc} */
	public function initializeFromRequest(App\Request $request)
	{
		parent::initializeFromRequest($request);
		$this->fileExtension = $request->getByType('export_type', 'Alnum');
		$this->workBook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$this->workSheet = $this->workBook->setActiveSheetIndex(0);
		$this->headerStyles = [
			'fill' => ['type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'E1E0F7']],
			'font' => ['bold' => true],
		];
	}

	/** {@inheritdoc} */
	public function getHeaders(): array
	{
		$headers = parent::getHeaders();
		foreach ($headers as $header) {
			$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			++$this->colNo;
		}
		++$this->rowNo;
		return $headers;
	}

	/** {@inheritdoc} */
	public function output($headers, $entries)
	{
		//having written out all the data lets have a go at getting the columns to auto-size
		$row = $col = 1;
		foreach ($headers as $header) {
			$cell = $this->workSheet->getCellByColumnAndRow($col, $row);
			$this->workSheet->getStyleByColumnAndRow($col, $row)->applyFromArray($this->headerStyles);
			$this->workSheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			++$col;
		}
		$tempFileName = tempnam(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \App\Config::main('tmp_dir'), 'xls');
		$workbookWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->workBook, ucfirst($this->exportType));
		$workbookWriter->save($tempFileName);
		$fp = fopen($tempFileName, 'r');
		fpassthru($fp);
		fclose($fp);
		unlink($tempFileName);
	}

	/** {@inheritdoc} */
	public function exportData()
	{
		if (!$this->exportColumns && $this->quickExport && $this->queryOptions['viewname']) {
			$headers = $this->getHeaders();
			$listViewModel = Vtiger_ListView_Model::getInstance($this->moduleName, $this->queryOptions['viewname']);
			$pagingModel = (new \Vtiger_Paging_Model())->set('limit', Vtiger_Paging_Model::PAGE_MAX_LIMIT);
			$listViewModel->set('query_generator', $this->queryGeneratorForList);
			foreach ($listViewModel->getListViewEntries($pagingModel) as $record) {
				++$this->rowNo;
				$this->colNo = 1;
				foreach ($this->listViewHeaders as $fieldModel) {
					$value = $this->listValueForExport($fieldModel, true, $record);
					$value = strip_tags($value);
					$this->putDataIntoSpreadsheetForQuickExport($fieldModel, $value);
				}
			}
		} else {
			$query = $this->getExportQuery();
			$headers = $this->getHeaders();
			$isInventory = $this->moduleInstance->isInventory();
			if ($isInventory) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
				$inventoryFields = $inventoryModel->getFields();
				$inventoryTable = $inventoryModel->getDataTableName();
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				if ($isInventory && !$this->quickExport) {
					$invRows = (new \App\Db\Query())->from($inventoryTable)->where(['crmid' => $row['id']])->orderBy('seq')->all();
					if ($invRows) {
						foreach ($invRows as $invRow) {
							$this->sanitizeValues($row);
							$this->sanitizeInventoryValues($invRow, $inventoryFields);
						}
					}
				} else {
					$this->sanitizeValues($row);
				}
			}
			$dataReader->close();
		}
		$this->output($headers, []);
	}

	/**
	 * Put data into spread sheet for quick export.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 * @param mixed              $value
	 *
	 * @return void
	 */
	public function putDataIntoSpreadsheetForQuickExport(Vtiger_Field_Model $fieldModel, $value): void
	{
		switch ($fieldModel->getFieldDataType()) {
			case 'integer':
			case 'double':
			case 'currency':
				$type = is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $value, $type);
				break;
			case 'date':
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
					$this->workSheet->getStyleByColumnAndRow($this->colNo, $this->rowNo)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				break;
			case 'datetime':
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
					$this->workSheet->getStyleByColumnAndRow($this->colNo, $this->rowNo)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				break;
			default:
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
		}
		++$this->colNo;
	}

	/** {@inheritdoc} */
	public function sanitizeValues(array $row): array
	{
		++$this->rowNo;
		$this->colNo = 1;
		$id = $row['id'];
		foreach ($row as $fieldName => $value) {
			if (isset($this->moduleFieldInstances[$fieldName])) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
			} elseif (isset($this->relatedModuleFields[$fieldName])) {
				$fieldModel = $this->relatedModuleFields[$fieldName];
			} else {
				unset($row[$fieldName]);
				continue;
			}
			$this->putDataIntoSpreadsheet($fieldModel, $value, $id);
		}
		return [];
	}

	public function putDataIntoSpreadsheet(Vtiger_Field_Model $fieldModel, $value, int $id)
	{
		switch ($fieldModel->getFieldDataType()) {
			case 'integer':
			case 'double':
			case 'currency':
				$type = is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $value, $type);
				break;
			case 'date':
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
					$this->workSheet->getStyleByColumnAndRow($this->colNo, $this->rowNo)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				break;
			case 'datetime':
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
					$this->workSheet->getStyleByColumnAndRow($this->colNo, $this->rowNo)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS');
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				break;
			case 'text':
				$displayValue = \App\Purifier::decodeHtml($fieldModel->getDisplayValue($value, $id, false, true, null));
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $displayValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				break;
			default:
				$displayValue = $fieldModel->getDisplayValue($value, $id, false, true, null);
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $displayValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		}
		++$this->colNo;
	}

	/** {@inheritdoc} */
	public function sanitizeInventoryValues(array $inventoryRow, array $inventoryFields): array
	{
		++$this->invNo;
		$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $this->invNo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
		++$this->colNo;
		foreach ($inventoryFields as $columnName => $field) {
			$value = $inventoryRow[$columnName] ?? '';
			if (\in_array($field->getType(), ['Name', 'Reference', 'Currency', 'Value', 'Unit', 'Boolean', 'Comment', 'Picklist', 'PicklistField', 'DiscountMode', 'TaxMode'])) {
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $field->getDisplayValue($value, $inventoryRow, true), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			} elseif ('Date' === $field->getType()) {
				if ($value) {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
					$this->workSheet->getStyleByColumnAndRow($this->colNo, $this->rowNo)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
				} else {
					$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
			} else {
				$type = is_numeric($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $value, $type);
			}
			++$this->colNo;
			foreach ($field->getCustomColumn() as $customColumnName => $dbType) {
				$valueParam = $inventoryRow[$customColumnName] ?? '';
				if ('currencyparam' === $customColumnName) {
					$field = $inventoryFields['currency'];
					$valueData = $field->getCurrencyParam([], $valueParam);
					if (\is_array($valueData)) {
						$valueNewData = [];
						foreach ($valueData as $currencyId => $data) {
							$currencyName = \App\Fields\Currency::getById($currencyId)['currency_name'];
							$valueNewData[$currencyName] = $data;
						}
						$valueParam = \App\Json::encode($valueNewData);
					}
				}
				$this->workSheet->setCellValueExplicitByColumnAndRow($this->colNo, $this->rowNo, $valueParam, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				++$this->colNo;
			}
		}
		return [];
	}
}
