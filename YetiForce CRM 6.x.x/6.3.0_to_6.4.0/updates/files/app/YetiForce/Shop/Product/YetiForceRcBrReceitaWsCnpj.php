<?php
/**
 * YetiForce shop YetiForceRcBrReceitaWsCnpj file.
 *
 * @see App\RecordCollectors\BrReceitaWsCnpj
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForceRcBrReceitaWsCnpj class.
 */
class YetiForceRcBrReceitaWsCnpj extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce Receita CNPJ BR';

	/** {@inheritdoc} */
	public $category = 'RecordCollectors';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-receita-cnpj-br';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 1,
		'Small' => 2,
		'Medium' => 4,
		'Large' => 8,
		'Corporation' => 15,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceRcBrReceitaWsCnpj')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceRcBrReceitaWsCnpj');
		} else {
			if (
				(new \App\Db\Query())->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => 'BrReceitaWsCnpj'])->exists()
				 || (new \App\Db\Query())->from('com_vtiger_workflowtasks')->where(['like', 'task', '%\BrReceitaWsCnpj";%', false])->exists()
			) {
				$message = 'LBL_PAID_FUNCTIONALITY_ACTIVATED';
				$status = false;
			}
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$return = [];
		if (\App\Security\AdminAccess::isPermitted('RecordCollector')) {
			$return[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'RecordCollector',
				'relatedModuleName' => 'Settings:RecordCollector',
				'linkicon' => 'yfi-record-collectors mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=RecordCollector&view=List',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return array_merge([
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'Website',
				'relatedModuleName' => '_Base',
				'linkicon' => 'fas fa-globe mr-2',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => $this->website,
				'linkclass' => 'btn-info',
				'showLabel' => 1,
			]),
			\Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'receitaws.com.br',
				'relatedModuleName' => 'Settings:_Base',
				'linkicon' => 'fa-solid fa-link',
				'linkhref' => true,
				'linkExternal' => true,
				'linktarget' => '_blank',
				'linkurl' => 'https://developers.receitaws.com.br/',
				'linkclass' => 'btn-secondary',
				'showLabel' => 1,
			]),
		], $return);
	}
}
