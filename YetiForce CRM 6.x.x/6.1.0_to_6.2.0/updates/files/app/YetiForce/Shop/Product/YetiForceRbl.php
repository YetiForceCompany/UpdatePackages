<?php
/**
 * YetiForce shop YetiForce RBL file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\YetiForce\Shop\Product;

/**
 * YetiForce shop YetiForce RBL class.
 */
class YetiForceRbl extends \App\YetiForce\Shop\AbstractBaseProduct
{
	/** {@inheritdoc} */
	public $label = 'YetiForce RBL';

	/** {@inheritdoc} */
	public $category = 'Integrations';

	/** {@inheritdoc} */
	public $website = 'https://yetiforce.com/en/yetiforce-rbl';

	/** {@inheritdoc} */
	public $prices = [
		'Micro' => 5,
		'Small' => 12,
		'Medium' => 25,
		'Large' => 50,
		'Corporation' => 100,
	];

	/** {@inheritdoc} */
	public $featured = true;

	/** {@inheritdoc} */
	public function verify(): array
	{
		$message = $status = true;
		if (\App\YetiForce\Register::getProducts('YetiForceRbl')) {
			[$status, $message] = \App\YetiForce\Shop::checkWithMessage('YetiForceRbl');
		}
		return ['status' => $status, 'message' => $message];
	}

	/** {@inheritdoc} */
	public function getAdditionalButtons(): array
	{
		$links = [
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
		];
		if (\App\Security\AdminAccess::isPermitted('MailRbl')) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_PUBLIC_RBL',
				'relatedModuleName' => 'Settings:MailRbl',
				'linkicon' => 'yfi yfi-rbl mr-2',
				'linkhref' => true,
				'linkurl' => 'index.php?parent=Settings&module=MailRbl&view=Index&tab=publicRbl',
				'linkclass' => 'btn-primary',
				'showLabel' => 1,
			]);
		}
		return $links;
	}
}
