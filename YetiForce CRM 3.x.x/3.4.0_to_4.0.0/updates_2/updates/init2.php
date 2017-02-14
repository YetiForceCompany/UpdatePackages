<?php
chdir(__DIR__ . '/../../');
include_once 'include/main/WebUI.php';
$instance = new YetiForceUpdate2();
$instance->preupdate();
$instance->update();
$instance->postupdate();

/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class YetiForceUpdate2
{

	public $package;
	public $modulenode;
	public $return = true;
	public $userEntity = [];
	private $cronAction = [];
	public $filesToDelete = [
		'api/',
		'include/autoload.php',
		'include/composer/',
		'include/ListView/RelatedListViewSession.php',
		'include/recaptcha/recaptchalib.php',
		'include/RelatedListView.php',
		'include/simplehtmldom/simple_html_dom.php',
		'include/SystemWarnings.php',
		'include/SystemWarnings/',
		'include/Webservices/Custom/VtigerCompanyDetails.php',
		'languages/de_de/Emails.php',
		'languages/de_de/OSSDocumentControl.php',
		'languages/de_de/OSSMailTemplates.php',
		'languages/de_de/OSSProjectTemplates.php',
		'languages/de_de/Settings/CustomerPortal.php',
		'languages/de_de/Settings/OSSDocumentControl.php',
		'languages/de_de/Settings/OSSProjectTemplates.php',
		'languages/de_de/Settings/POS.php',
		'languages/de_de/Settings/Webforms.php',
		'languages/en_us/Emails.php',
		'languages/en_us/OSSDocumentControl.php',
		'languages/en_us/OSSMailTemplates.php',
		'languages/en_us/OSSProjectTemplates.php',
		'languages/en_us/Settings/CustomerPortal.php',
		'languages/en_us/Settings/OSSDocumentControl.php',
		'languages/en_us/Settings/OSSProjectTemplates.php',
		'languages/en_us/Settings/POS.php',
		'languages/en_us/Settings/Webforms.php',
		'languages/fr_fr/Emails.php',
		'languages/fr_fr/OSSDocumentControl.php',
		'languages/fr_fr/OSSMailTemplates.php',
		'languages/fr_fr/OSSProjectTemplates.php',
		'languages/fr_fr/Settings/CustomerPortal.php',
		'languages/fr_fr/Settings/OSSDocumentControl.php',
		'languages/fr_fr/Settings/OSSProjectTemplates.php',
		'languages/fr_fr/Settings/POS.php',
		'languages/fr_fr/Settings/Webforms.php',
		'languages/pl_pl/Emails.php',
		'languages/pl_pl/OSSDocumentControl.php',
		'languages/pl_pl/OSSMailTemplates.php',
		'languages/pl_pl/OSSProjectTemplates.php',
		'languages/pl_pl/Settings/CustomerPortal.php',
		'languages/pl_pl/Settings/OSSDocumentControl.php',
		'languages/pl_pl/Settings/OSSProjectTemplates.php',
		'languages/pl_pl/Settings/POS.php',
		'languages/pl_pl/Settings/Webforms.php',
		'languages/ru_ru/Emails.php',
		'languages/ru_ru/OSSDocumentControl.php',
		'languages/ru_ru/OSSMailTemplates.php',
		'languages/ru_ru/OSSProjectTemplates.php',
		'languages/ru_ru/Settings/CustomerPortal.php',
		'languages/ru_ru/Settings/OSSDocumentControl.php',
		'languages/ru_ru/Settings/OSSProjectTemplates.php',
		'languages/ru_ru/Settings/POS.php',
		'languages/ru_ru/Settings/Webforms.php',
		'layouts/basic/modules/Calendar/ListViewContents.tpl',
		'layouts/basic/modules/Calendar/ListViewLeftSide.tpl',
		'layouts/basic/modules/Calendar/QuickCreateFollowup.tpl',
		'layouts/basic/modules/Emails/',
		'layouts/basic/modules/Notification/ListViewLeftSide.tpl',
		'layouts/basic/modules/OSSDocumentControl',
		'layouts/basic/modules/OSSMail/GetMails.tpl',
		'layouts/basic/modules/OSSMailTemplates/',
		'layouts/basic/modules/OSSPasswords/ListViewLeftSide.tpl',
		'layouts/basic/modules/OSSProjectTemplates/',
		'layouts/basic/modules/Settings/CustomerPortal/',
		'layouts/basic/modules/Settings/OSSDocumentControl/',
		'layouts/basic/modules/Settings/OSSProjectTemplates/',
		'layouts/basic/modules/Settings/PDF/FieldBlock.tpl',
		'layouts/basic/modules/Settings/POS/',
		'layouts/basic/modules/Settings/Vtiger/CompanyDetails.tpl',
		'layouts/basic/modules/Settings/Vtiger/CompanyDetailsEdit.tpl',
		'layouts/basic/modules/Settings/Vtiger/OutgoingServerDetail.tpl',
		'layouts/basic/modules/Settings/Vtiger/OutgoingServerEdit.tpl',
		'layouts/basic/modules/Settings/Vtiger/resources/CompanyDetails.js',
		'layouts/basic/modules/Settings/Vtiger/resources/CompanyDetails.min.js',
		'layouts/basic/modules/Settings/Vtiger/resources/OutgoingServer.js',
		'layouts/basic/modules/Settings/Vtiger/resources/OutgoingServer.min.js',
		'layouts/basic/modules/Settings/Webforms/',
		'layouts/basic/modules/Vtiger/ComposeEmailForm.tpl',
		'layouts/basic/modules/Vtiger/SelectEmailFields.tpl',
		'layouts/basic/skins/images/CustomerPortal.png',
		'layouts/basic/skins/images/Emails.png',
		'layouts/basic/skins/images/Emails128.png',
		'layouts/basic/skins/images/Emails48.png',
		'layouts/basic/skins/images/Emails64.png',
		'layouts/basic/skins/images/OSSDocumentControl.png',
		'layouts/basic/skins/images/OSSDocumentControl128.png',
		'layouts/basic/skins/images/OSSDocumentControl48.png',
		'layouts/basic/skins/images/OSSDocumentControl64.png',
		'layouts/basic/skins/images/OSSMailTemplates.png',
		'layouts/basic/skins/images/OSSMailTemplates128.png',
		'layouts/basic/skins/images/OSSMailTemplates48.png',
		'layouts/basic/skins/images/OSSMailTemplates64.png',
		'layouts/basic/skins/images/OSSProjectTemplates.png',
		'layouts/basic/skins/images/OSSProjectTemplates128.png',
		'layouts/basic/skins/images/OSSProjectTemplates48.png',
		'layouts/basic/skins/images/OSSProjectTemplates64.png',
		'layouts/basic/skins/images/Webforms.png',
		'libraries/htmlpurifier',
		'libraries/jquery/ckeditor',
		'libraries/nusoap',
		'libraries/nusoap/nusoapmime.php',
		'libraries/restler/restler.php',
		'libraries/Smarty',
		'modules/Calendar/actions/SaveFollowupAjax.php',
		'modules/Calendar/RenderRelatedListUI.php',
		'modules/Calendar/views/Export.php',
		'modules/Calendar/views/QuickCreateFollowupAjax.php',
		'modules/Contacts/handlers/ContactsHandler.php',
		'modules/CustomerPortal/CustomerPortal.php',
		'modules/Emails/Emails.php',
		'modules/Emails/models/Record.php',
		'modules/OSSDocumentControl',
		'modules/OSSMail/actions/GetMail.php',
		'modules/OSSMail/MailAttachmentMIME.php',
		'modules/OSSMailTemplates/',
		'modules/OSSProjectTemplates/',
		'modules/Project/models/Field.php',
		'modules/Settings/CustomerPortal/',
		'modules/Settings/OSSDocumentControl/',
		'modules/Settings/OSSProjectTemplates/',
		'modules/Settings/PDF/actions/GetMainFields.php',
		'modules/Settings/POS/',
		'modules/Settings/Vtiger/actions/CompanyDetailsFieldSave.php',
		'modules/Settings/Vtiger/actions/CompanyDetailsSave.php',
		'modules/Settings/Vtiger/actions/OutgoingServerSaveAjax.php',
		'modules/Settings/Vtiger/actions/UpdateCompanyLogo.php',
		'modules/Settings/Vtiger/models/CompanyDetails.php',
		'modules/Settings/Vtiger/models/OutgoingServer.php',
		'modules/Settings/Vtiger/views/CompanyDetails.php',
		'modules/Settings/Vtiger/views/CompanyDetailsEdit.php',
		'modules/Settings/Vtiger/views/OutgoingServerDetail.php',
		'modules/Settings/Vtiger/views/OutgoingServerEdit.php',
		'modules/Settings/Webforms/',
		'modules/Settings/Workflows/models/EditTaskRecordStructure.php',
		'modules/Vtiger/models/CompanyDetails.php',
		'modules/Vtiger/models/ModulesHierarchy.php',
		'modules/Vtiger/pdfs/special_functions',
		'modules/Vtiger/pdfs/SpecialFunction.php',
		'modules/Vtiger/uitypes/PosList.php',
		'modules/Vtiger/views/ComposeEmail.php',
		'modules/Vtiger/views/EmailsRelatedModulePopup.php',
		'modules/Vtiger/views/EmailsRelatedModulePopupAjax.php',
		'modules/Webforms/',
		'vendor/',
		'vtlib/thirdparty/network',
		'vtlib/Vtiger/Net/Client.php',
		'include/utils/RecurringType.php',
		'languages/de_de/Settings/MobileApps.php',
		'languages/en_us/Settings/MobileApps.php',
		'languages/pl_pl/Settings/MobileApps.php',
		'languages/pl_pl/Install.php',
		'languages/ru_ru/Settings/MobileApps.php',
		'layouts/basic/modules/Settings/MobileApps',
		'layouts/basic/modules/Vtiger/resources/Mobile.js',
		'layouts/basic/modules/Vtiger/uitypes/StreetAddress.tpl',
		'layouts/basic/modules/Vtiger/uitypes/StreetAddressDetailView.tpl',
		'modules/Calendar/Appointment.php',
		'modules/Calendar/RepeatEvents.php',
		'modules/Settings/MobileApps',
		'modules/Vtiger/actions/Mobile.php',
		'modules/Vtiger/models/Mobile.php',
		'modules/Vtiger/uitypes/StreetAddress.php',
		'vendor/yetiforce/QueryField/StreetAddressField.php'
	];

	public function __construct()
	{
		$this->from_version = \AppRequest::get('from_version');
		$this->to_version = \AppRequest::get('to_version');
	}

	public function preupdate()
	{
		$db = \PearDatabase::getInstance();
		$db->query('SET FOREIGN_KEY_CHECKS = 0;');
		\App\Db::getInstance()->createCommand()->checkIntegrity(false)->execute();
	}

	public function update()
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_entityname', ['tablename' => 'vtiger_emaildetails', 'entityidfield' => 'emailid', 'entityidcolumn' => 'emailid'], ['modulename' => 'Emails'])->execute();
		$this->removeModules(['OSSMailTemplates', 'Emails', 'Webforms']);
		$this->removeFields($this->getFieldsToRemove(1));
		$this->actionMapp($this->getActionMapp(1));
		$this->setFields($this->getFields(1));
		$this->setTablesScheme($this->getTablesAction(1));
		$this->addModules(['CFixedAssets', 'CInternalTickets', 'FInvoiceCost', 'CMileageLogbook', 'SVendorEnquiries']);
		$this->addRecords('EmailTemplates');
		$this->updateData();
		$this->setTablesScheme($this->getTablesAction(5));
		$this->setAlterTables($this->getAlterTables(1));
		$this->updateConfigurationFiles();
		$this->updateMenu();
		$this->updateSettingMenu();
	}

	public function updateMenu()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$columns = ['id', 'parentid', 'type', 'sequence', 'module', 'label', 'icon'];
		$menu = [
				[44, 0, 2, 0, NULL, 'MEN_VIRTUAL_DESK', 'userIcon-VirtualDesk'],
				[45, 44, 0, 0, 'Home', 'Home page', 'userIcon-Home'],
				[46, 44, 0, 1, 'Calendar', NULL, ''],
				[47, 0, 2, 1, NULL, 'MEN_COMPANIES_CONTACTS', 'userIcon-CompaniesAndContact'],
				[48, 47, 0, 0, 'Leads', NULL, ''],
				[49, 47, 0, 5, 'Contacts', NULL, ''],
				[50, 47, 0, 3, 'Vendors', NULL, ''],
				[51, 47, 0, 1, 'Accounts', NULL, ''],
				[52, 0, 2, 2, NULL, 'MEN_MARKETING', 'userIcon-Campaigns'],
				[54, 52, 0, 0, 'Campaigns', NULL, ''],
				[62, 118, 0, 8, 'PriceBooks', NULL, ''],
				[63, 0, 2, 5, NULL, 'MEN_SUPPORT', 'userIcon-Support'],
				[64, 63, 0, 0, 'HelpDesk', NULL, ''],
				[65, 63, 0, 1, 'ServiceContracts', NULL, ''],
				[66, 63, 0, 2, 'Faq', NULL, ''],
				[67, 0, 2, 4, NULL, 'MEN_PROJECTS', 'userIcon-Project'],
				[68, 67, 0, 0, 'Project', NULL, ''],
				[69, 67, 0, 1, 'ProjectMilestone', NULL, ''],
				[70, 67, 0, 2, 'ProjectTask', NULL, ''],
				[71, 0, 2, 6, NULL, 'MEN_ACCOUNTING', 'userIcon-Bookkeeping'],
				[72, 71, 0, 5, 'PaymentsIn', NULL, ''],
				[73, 71, 0, 4, 'PaymentsOut', NULL, ''],
				[76, 0, 2, 8, NULL, 'MEN_COMPANY', 'userIcon-HumanResources'],
				[77, 76, 0, 0, 'OSSEmployees', NULL, ''],
				[78, 76, 0, 1, 'OSSTimeControl', NULL, ''],
				[79, 76, 0, 2, 'HolidaysEntitlement', NULL, ''],
				[80, 0, 2, 9, NULL, 'MEN_SECRETARY', 'userIcon-Secretary'],
				[81, 80, 0, 0, 'LettersIn', NULL, ''],
				[82, 80, 0, 1, 'LettersOut', NULL, ''],
				[83, 80, 0, 2, 'Reservations', NULL, ''],
				[84, 0, 2, 10, NULL, 'MEN_DATABESES', 'userIcon-Database'],
				[85, 84, 2, 0, NULL, 'MEN_PRODUCTBASE', NULL],
				[86, 84, 0, 1, 'Products', NULL, ''],
				[87, 84, 0, 2, 'OutsourcedProducts', NULL, ''],
				[88, 84, 0, 3, 'Assets', NULL, ''],
				[89, 84, 3, 4, NULL, NULL, NULL],
				[90, 84, 2, 5, NULL, 'MEN_SERVICESBASE', NULL],
				[91, 84, 0, 6, 'Services', NULL, ''],
				[92, 84, 0, 7, 'OSSOutsourcedServices', NULL, ''],
				[93, 84, 0, 8, 'OSSSoldServices', NULL, ''],
				[94, 84, 3, 9, NULL, NULL, NULL],
				[95, 84, 2, 10, NULL, 'MEN_LISTS', NULL],
				[96, 84, 0, 11, 'OSSMailView', NULL, ''],
				[97, 84, 0, 12, 'SMSNotifier', NULL, ''],
				[98, 84, 0, 13, 'PBXManager', NULL, ''],
				[100, 84, 0, 15, 'Documents', NULL, ''],
				[106, 84, 0, 17, 'CallHistory', NULL, ''],
				[107, 84, 3, 18, NULL, NULL, NULL],
				[108, 84, 0, 23, 'Announcements', NULL, ''],
				[109, 84, 0, 16, 'OSSPasswords', NULL, ''],
				[111, 44, 0, 3, 'Ideas', NULL, ''],
				[113, 44, 0, 2, 'OSSMail', NULL, ''],
				[114, 84, 0, 22, 'Reports', NULL, ''],
				[115, 84, 0, 19, 'Rss', NULL, ''],
				[116, 84, 0, 20, 'Portal', NULL, ''],
				[117, 84, 3, 21, NULL, NULL, NULL],
				[118, 0, 2, 3, NULL, 'MEN_SALES', 'userIcon-Sales'],
				[119, 118, 0, 0, 'SSalesProcesses', NULL, ''],
				[120, 118, 0, 1, 'SQuoteEnquiries', NULL, ''],
				[121, 118, 0, 2, 'SRequirementsCards', NULL, ''],
				[122, 118, 0, 3, 'SCalculations', NULL, ''],
				[123, 118, 0, 4, 'SQuotes', NULL, ''],
				[124, 118, 0, 5, 'SSingleOrders', NULL, ''],
				[125, 118, 0, 6, 'SRecurringOrders', NULL, ''],
				[126, 47, 0, 2, 'Partners', NULL, ''],
				[127, 47, 0, 4, 'Competition', NULL, ''],
				[128, 71, 0, 0, 'FBookkeeping', NULL, ''],
				[129, 71, 0, 1, 'FInvoice', NULL, ''],
				[130, 63, 0, 3, 'KnowledgeBase', NULL, ''],
				[131, 0, 2, 7, NULL, 'MEN_LOGISTICS', 'userIcon-VendorsAccounts'],
				[132, 131, 0, 10, 'IStorages', NULL, ''],
				[133, 131, 0, 1, 'IGRN', NULL, ''],
				[134, 71, 0, 2, 'FInvoiceProforma', NULL, ''],
				[135, 131, 0, 4, 'IGDN', NULL, ''],
				[136, 131, 0, 5, 'IIDN', NULL, ''],
				[137, 131, 0, 6, 'IGIN', NULL, ''],
				[138, 131, 0, 7, 'IPreOrder', NULL, ''],
				[139, 131, 0, 9, 'ISTDN', NULL, ''],
				[140, 131, 0, 0, 'ISTN', NULL, ''],
				[141, 131, 0, 8, 'ISTRN', NULL, ''],
				[142, 71, 0, 3, 'FCorectingInvoice', NULL, ''],
				[143, 131, 0, 2, 'IGRNC', 'IGRNC', ''],
				[144, 131, 0, 3, 'IGDNC', 'IGDNC', ''],
				[145, 84, 0, 24, 'RecycleBin', '', ''],
				[146, 84, 0, 14, 'EmailTemplates', '', ''],
				[147, 76, 0, 3, 'CFixedAssets', '', ''],
				[148, 76, 0, 4, 'CInternalTickets', '', ''],
				[149, 76, 0, 5, 'CMileageLogbook', '', ''],
				[150, 71, 0, 6, 'FInvoiceCost', '', ''],
				[151, 118, 0, 7, 'SVendorEnquiries', '', '']
		];
		$db->createCommand()->delete('yetiforce_menu', ['role' => 0])->execute();
		$parents = [];
		foreach ($menu as $row) {
			$parent = $row[1] !== 0 ? $parents[$row[1]] : 0;
			$module = $row[2] === 0 ? \vtlib\Functions::getModuleId($row[4]) : $row[4];
			if ($row[2] === 0 && !\App\Module::isModuleActive($row[4])) {
				continue;
			}
			$result = $db->createCommand()->insert('yetiforce_menu', ['role' => 0, 'parentid' => $parent, 'type' => $row[2], 'sequence' => $row[3], 'module' => $module, 'label' => $row[5], 'icon' => $row[6]])->execute();
			if ($result && $row[1] === 0) {
				$parents[$row[0]] = $db->getLastInsertID('yetiforce_menu_id_seq');
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function removeModules($modules)
	{
		$db = App\Db::getInstance();
		foreach ($modules as $moduleName) {
			$tabId = \vtlib\Functions::getModuleId($moduleName);
			if (!$tabId) {
				continue;
			}
			\App\Cache::delete('ModuleEntityById', $tabId);
			\App\Cache::delete('ModuleEntityByName', $moduleName);
			$moduleBaseInstance = vtlib\Module::getInstance($moduleName);
			if ($moduleBaseInstance) {
				$moduleInstance = \Vtiger_Module_Model::getInstance($moduleBaseInstance->name);
				$focus = CRMEntity::getInstance($moduleBaseInstance->name);
				$moduleBaseInstance->tableName = $focus->table_name;
				if ($moduleBaseInstance->name === 'Emails') {
					$moduleBaseInstance->tableName = 'vtiger_emaildetails';
				}
				if ($moduleBaseInstance->isentitytype) {
					$moduleBaseInstance->deleteFromCRMEntity();
					\vtlib\Access::deleteTools($moduleBaseInstance);
					\vtlib\Filter::deleteForModule($moduleBaseInstance);
					$this->deletePickLists($moduleBaseInstance);
					\vtlib\Field::deleteUiType10Fields($moduleBaseInstance);
					$db->createCommand()->delete('vtiger_field', ['tabid' => $moduleBaseInstance->id])->execute();
					$db->createCommand()->delete('vtiger_fieldmodulerel', ['or', "module = '$moduleBaseInstance->name'", "relmodule = '$moduleBaseInstance->name'"])->execute();
					\vtlib\Block::deleteForModule($moduleBaseInstance, false);
					if (method_exists($moduleBaseInstance, 'deinitWebservice')) {
						$moduleBaseInstance->deinitWebservice();
					}
				}
				$moduleBaseInstance->deleteIcons();
				$moduleBaseInstance->unsetAllRelatedList($moduleInstance);
				\ModComments_Module_Model::deleteForModule($moduleInstance);
				\vtlib\Language::deleteForModule($moduleInstance);
				\vtlib\Access::deleteSharing($moduleInstance);
				$moduleBaseInstance->deleteFromModentityNum();
				\vtlib\Cron::deleteForModule($moduleInstance);
				\vtlib\Profile::deleteForModule($moduleInstance);
				\Settings_Workflows_Module_Model::deleteForModule($moduleInstance);
				\vtlib\Menu::deleteForModule($moduleInstance);
				$moduleBaseInstance->deleteGroup2Modules();
				$moduleBaseInstance->deleteModuleTables();
				$moduleBaseInstance->deleteCRMEntityRel();
				\vtlib\Profile::deleteForModule($moduleBaseInstance);
				\vtlib\Link::deleteAll($moduleBaseInstance->id);
				$db->createCommand()->delete('vtiger_settings_field', ['like', 'linkto', "module={$moduleBaseInstance->name}&"])->execute();
				$moduleBaseInstance->deleteDir($moduleInstance);
				$moduleBaseInstance->__delete();
				\vtlib\Deprecated::createModuleMetaFile();
			}
		}
	}

	/**
	 * Function to remove picklist-type or multiple choice picklist-type table
	 * @param Module Instance of module
	 */
	private function deletePickLists($moduleInstance)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query)->select(['fieldname'])->from('vtiger_field')->where(['tabid' => $moduleInstance->getId(), 'uitype' => [15, 16, 33]]);
		$modulePicklists = $query->column();
		if (!empty($modulePicklists)) {
			$query = (new \App\Db\Query)->select('fieldname')->from('vtiger_field')->where(['fieldname' => $modulePicklists, 'uitype' => [15, 16, 33]])
				->andWhere(['<>', 'tabid', $moduleInstance->getId()]);
			$picklists = $query->column();
			$modulePicklists = array_diff($modulePicklists, $picklists);
		}
		foreach ($modulePicklists as &$picklistName) {
			if ($db->isTableExists("vtiger_$picklistName")) {
				$db->createCommand()->dropTable("vtiger_$picklistName")->execute();
			}
			if ($db->isTableExists("vtiger_{$picklistName}_seq")) {
				$db->createCommand()->dropTable("vtiger_{$picklistName}_seq")->execute();
			}
			$picklistId = (new \App\Db\Query)->select('picklistid')->from('vtiger_picklist')->where(['name' => $picklistName])->scalar();
			$db->createCommand()->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
			$db->createCommand()->delete('vtiger_picklist', ['name' => $picklistName])->execute();
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function getFieldsToRemove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'vtiger_campaignrelstatus' => ['campaignrelstatus']
				];
				break;
			case 2:
				$fields = [
					'u_yf_istorages' => ['pos'],
					'u_yf_ssingleorders' => ['pos', 'istoragesid', 'table', 'seat'],
					'vtiger_products' => ['pos']
				];
				break;
			case 3:
				$fields = [
					'vtiger_activity' => ['recurringtype']
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function removeFields($fields)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		foreach ($fields as $tableNameVal => $columnsName) {
			if (empty($columnsName) || !$db->isTableExists($tableNameVal)) {
				continue;
			}
			foreach ($columnsName as $columnSingleName) {
				$ids = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['columnname' => $columnSingleName, 'tablename' => $tableNameVal])->column();
				foreach ($ids as $id) {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					try {
						$fieldInstance->__delete();
						$fldModule = $fieldInstance->getModuleName();
						$uitype = $fieldInstance->get('uitype');
						$typeofdata = $fieldInstance->get('typeofdata');
						$fieldname = $fieldInstance->getName();
						$oldfieldlabel = $fieldInstance->get('label');
						$tablename = $fieldInstance->get('table');
						$columnName = $fieldInstance->get('column');
						$fieldtype = explode("~", $typeofdata);
						$tabId = $fieldInstance->getModuleId();

						$focus = CRMEntity::getInstance($fldModule);

						$deleteColumnName = $tablename . ":" . $columnName . ":" . $fieldname . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldtype[0];
						$columnCvstdfilter = $tablename . ":" . $columnName . ":" . $fieldname . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel);
						$selectColumnname = $tablename . ":" . $columnName . ":" . $fldModule . "_" . str_replace(" ", "_", $oldfieldlabel) . ":" . $fieldname . ":" . $fieldtype[0];
						$reportsummaryColumn = $tablename . ":" . $columnName . ":" . str_replace(" ", "_", $oldfieldlabel);
						$tableSchema = $db->getSchema()->getTableSchema($tablename);
						if ($tablename != 'vtiger_crmentity' && $tableSchema && $tableSchema->getColumn($columnName)) {
							$db->createCommand()->dropColumn($tablename, $columnName)->execute();
						}
						//we have to remove the entries in customview and report related tables which have this field ($colName)
						$db->createCommand()->delete('vtiger_cvcolumnlist', ['columnname' => $deleteColumnName])->execute();
						$db->createCommand()->delete('vtiger_cvstdfilter', ['columnname' => $columnCvstdfilter])->execute();
						$db->createCommand()->delete('vtiger_cvadvfilter', ['columnname' => $deleteColumnName])->execute();
						$db->createCommand()->delete('vtiger_selectcolumn', ['columnname' => $selectColumnname])->execute();
						$db->createCommand()->delete('vtiger_relcriteria', ['columnname' => $selectColumnname])->execute();
						$db->createCommand()->delete('vtiger_reportsortcol', ['columnname' => $selectColumnname])->execute();
						$db->createCommand()->delete('vtiger_reportdatefilter', ['datecolumnname' => $columnCvstdfilter])->execute();
						$db->createCommand()->delete('vtiger_reportsummary', ['like', 'columnname', $reportsummaryColumn])->execute();
						//Deleting from convert lead mapping vtiger_table- Jaguar
						if ($fldModule == 'Leads') {
							$db->createCommand()->delete('vtiger_convertleadmapping', ['leadfid' => $id])->execute();
						} elseif ($fldModule == 'Accounts') {
							$mapDelId = ['Accounts' => 'accountfid'];
							$db->createCommand()->update('vtiger_convertleadmapping', [$mapDelId[$fldModule] => 0], [$mapDelId[$fldModule] => $id])->execute();
						}

						//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
						if ($fieldInstance->getFieldDataType() == 'picklist' || $fieldInstance->getFieldDataType() == 'multipicklist') {
							$query = (new \App\Db\Query())->from('vtiger_field')
								->where(['columnname' => $columnName])
								->andWhere(['in', 'uitype', [15, 16, 33]]);
							$dataReader = $query->createCommand()->query();
							if (!$dataReader->count()) {
								$db->createCommand()->dropTable('vtiger_' . $columnName)->execute();
								//To Delete Sequence Table 
								if ($db->isTableExists('vtiger_' . $columnName . '_seq')) {
									$db->createCommand()->dropTable('vtiger_' . $columnName . '_seq')->execute();
								}
								$db->createCommand()->delete('vtiger_picklist', ['name' => $columnName]);
							}
							$db->createCommand()->delete('vtiger_picklist_dependency', ['and', "tabid = $tabId", ['or', "sourcefield = '$columnname'", "targetfield = '$columnname'"]])->execute();
						}
					} catch (Exception $e) {
						\App\Log::error('ERROR' . __METHOD__ . ': code ' . $e->getCode() . " message " . $e->getMessage());
					}
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$className = $this->getClassName(['Vtiger_ModulesHierarchy_Model', '\App\ModuleHierarchy']);
				$modules = $className::getModulesByLevel(0);
				$modules1 = $className::getModulesByLevel(1);
				$modules2 = $className::getModulesByLevel(2);
				$modulesAll = array_merge(array_keys($modules), array_keys($modules1), array_keys($modules2));
				$actions = [
						['type' => 'remove', 'name' => 'NotificationCreateMessage'],
						['type' => 'add', 'name' => 'TimeLineList', 'tabsData' => array_map('\vtlib\Functions::getModuleId', $modulesAll)]
				];
				break;
			case 2:
				$modulesAll = ['EmailTemplates', 'CFixedAssets', 'CInternalTickets', 'CMileageLogbook', 'FInvoiceCost', 'SVendorEnquiries'];
				$tabsData = array_map('\vtlib\Functions::getModuleId', $modulesAll);
				$actions = [
						['type' => 'add', 'name' => 'Import', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'Export', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'CreateCustomFilter', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'DuplicateRecord', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'MassEdit', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'MassDelete', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'MassAddComment', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'MassComposeEmail', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'MassTransferOwnership', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'Dashboard', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'CreateDashboardFilter', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'ExportPdf', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'RecordMapping', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'RecordMappingList', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'FavoriteRecords', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'WatchingRecords', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'WatchingModule', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'RemoveRelation', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'ReviewingUpdates', 'tabsData' => $tabsData],
						['type' => 'add', 'name' => 'CreateDashboardChartFilter', 'tabsData' => $tabsData]
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	private function actionMapp($actions)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		foreach ($actions as $action) {
			$key = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action['name']])->limit(1)->scalar();
			if ($action['type'] === 'remove') {
				if ($key) {
					$db->createCommand()->delete('vtiger_actionmapping', ['actionid' => $key])->execute();
					$db->createCommand()->delete('vtiger_profile2utility', ['activityid' => $key])->execute();
				}
				continue;
			}
			if (empty($key)) {
				$securitycheck = 0;
				$key = $db->getUniqueID('vtiger_actionmapping', 'actionid', false);
				$db->createCommand()->insert('vtiger_actionmapping', ['actionid' => $key, 'actionname' => $action['name'], 'securitycheck' => $securitycheck])->execute();
			}
			$permission = 1;
			if (isset($action['permission'])) {
				$permission = $action['permission'];
			}

			$tabsData = $action['tabsData'];
			$dataReader = (new \App\Db\Query())->select(['profileid'])->from('vtiger_profile')->createCommand()->query();
			while (($profileId = $dataReader->readColumn(0)) !== false) {
				foreach ($tabsData as $tabId) {
					$isExists = (new \App\Db\Query())->from('vtiger_profile2utility')->where(['profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key])->exists();
					if (!$isExists) {
						$db->createCommand()->insert('vtiger_profile2utility', [
							'profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key, 'permission' => $permission
						])->execute();
					}
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	public function getClassName($name)
	{
		$log = vglobal('log');
		if (is_array($name)) {
			foreach ($name as $className) {
				if (class_exists($className)) {
					return $className;
				}
			}
		}
		\App\Log::error('ERROR' . __METHOD__ . ': | Class not found for ' . print_r($name, true));
		return false;
	}

	private function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields[] = ['42', '623', 'startdate', 'u_yf_ssalesprocesses', '1', '5', 'startdate', 'FL_START_DATE', '1', '2', '', '100', '5', '105', '1', 'D~O', '2', '5', 'BAS', '1', '', '1', '', NULL, "date", 'LBL_SSALESPROCESSES_INFORMATION', [], [], 'SSalesProcesses'];
				break;
			case 2:
				$fields[] = ['95', '2572', 'description', 'vtiger_crmentity', '1', '300', 'description', 'Description', '1', '2', '', '100', '0', '398', '1', 'V~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "varchar(100)", 'LBL_DESCRIPTION_BLOCK', [], [], 'FInvoice'];
				$fields[] = ['95', '2571', 'attention', 'vtiger_crmentity', '1', '300', 'attention', 'Attention', '1', '2', '', '100', '0', '399', '1', 'V~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "varchar(100)", 'LBL_ATTENTION_BLOCK', [], [], 'FInvoice'];
				$fields[] = ['95', '2570', 'shownerid', 'vtiger_crmentity', '1', '120', 'shownerid', 'Share with users', '1', '2', '', '100', '7', '311', '1', 'V~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "varchar(100)", 'LBL_CUSTOM_INFORMATION', [], [], 'FInvoice'];
				$fields[] = ['95', '2573', 'pscategory', 'u_yf_finvoice', '1', '302', 'pscategory', 'FL_CATEGORY', '1', '2', '', '100', '16', '310', '1', 'V~O', '1', '0', 'BAS', '1', '', '0', '21', NULL, "varchar(100)", 'LBL_BASIC_DETAILS', [], [], 'FInvoice'];
				break;
			case 3:
				$fields[] = ['113', '2574', 'current_odometer_reading', 'u_yf_cfixedassets', '1', '7', 'current_odometer_reading', 'FL_CURRENT_ODOMETER_READING', '1', '2', '', '100', '0', '380', '1', 'I~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "int(11)", 'LBL_VEHICLE', [], [], 'CFixedAssets'];
				$fields[] = ['113', '2575', 'number_repair', 'u_yf_cfixedassets', '1', '7', 'number_repair', 'FL_NUMBER_REPAIR', '1', '2', '', '100', '0', '380', '1', 'I~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "smallint(6)", 'LBL_VEHICLE', [], [], 'CFixedAssets'];
				$fields[] = ['113', '2576', 'date_last_repair', 'u_yf_cfixedassets', '1', '5', 'date_last_repair', 'FL_DATE_OF_LAST_REPAIR', '1', '2', '', '100', '0', '380', '1', 'D~O', '1', '0', 'BAS', '1', '', '0', '', NULL, "date", 'LBL_VEHICLE', [], [], 'CFixedAssets'];
				break;
			case 4:
				$fields[] = [9, 2602, 'reapeat', 'vtiger_activity', 1, 56, 'reapeat', 'FL_REAPEAT', 1, 2, '', 100, 0, 19, 3, 'I~O', 1, 0, 'BAS', 1, '', 0, '', NULL, "smallint(1)", 'LBL_TASK_INFORMATION', [], [], 'Calendar'];
				$fields[] = [9, 2603, 'recurrence', 'vtiger_activity', 1, 342, 'recurrence', 'FL_RECURRENCE', 1, 2, '', 100, 0, 19, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, "text", 'LBL_TASK_INFORMATION', [], [], 'Calendar'];
				$fields[] = [16, 2601, 'recurrence', 'vtiger_activity', 1, 342, 'recurrence', 'FL_RECURRENCE', 1, 2, '', 100, 8, 117, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, "text", 'LBL_RECURRENCE_INFORMATION', [], [], 'Events'];
				$fields[] = [16, 2600, 'reapeat', 'vtiger_activity', 1, 56, 'reapeat', 'FL_REAPEAT', 1, 2, '', 100, 7, 117, 1, 'I~O', 1, 0, 'BAS', 1, '', 0, '', NULL, "smallint(1)", 'LBL_RECURRENCE_INFORMATION', [], [], 'Events'];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setFields($fields)
	{
//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (!\vtlib\Functions::getModuleId($field[28]) || $this->checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$moduleInstance = \vtlib\Module::getInstance($field[28]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $moduleInstance);
			$fieldInstance = new \vtlib\Field();
			$fieldInstance->column = $field[2];
			$fieldInstance->name = $field[6];
			$fieldInstance->label = $field[7];
			$fieldInstance->table = $field[3];
			$fieldInstance->uitype = $field[5];
			$fieldInstance->typeofdata = $field[15];
			$fieldInstance->readonly = $field[8];
			$fieldInstance->displaytype = $field[14];
			$fieldInstance->masseditable = $field[19];
			$fieldInstance->quickcreate = $field[16];
			$fieldInstance->columntype = $field[24];
			$fieldInstance->presence = $field[9];
			$fieldInstance->maximumlength = $field[11];
			$fieldInstance->quicksequence = $field[17];
			$fieldInstance->info_type = $field[18];
			$fieldInstance->helpinfo = $field[20];
			$fieldInstance->summaryfield = $field[21];
			$fieldInstance->generatedtype = $field[4];
			$fieldInstance->defaultvalue = $field[10];
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 ))
				$fieldInstance->setPicklistValues($field[26]);
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
	}

	private function checkFieldExists($moduleName, $column, $table)
	{
		$query = new \App\Db\Query();
		if ($moduleName == 'Settings') {
			$query->from('vtiger_settings_field')->where(['name' => $column, 'linkto' => $table]);
		} else {
			if (is_numeric($moduleName)) {
				$tabId = $moduleName;
			} else {
				$tabId = \vtlib\Functions::getModuleId($moduleName);
			}
			$query->from('vtiger_field')->where(['columnname' => $column, 'tablename' => $table, 'tabid' => $tabId]);
		}
		if (!$query->exists()) {
			return false;
		}
		return true;
	}

	private function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
						['type' => 'add', 'name' => 'u_yf_timeline', 'sql' => "`u_yf_timeline` (
							`crmid` int(11) NOT NULL,
							`type` varchar(50) DEFAULT NULL,
							`userid` int(11) NOT NULL,
							KEY `timeline_crmid_idx` (`crmid`),
							CONSTRAINT `fk_1_u_yf_timeline` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
						  )"],
						['type' => 'remove', 'name' => 'vtiger_salesmanactivityrel'],
						['type' => 'remove', 'name' => 'vtiger_emaildetails'],
						['type' => 'remove', 'name' => 'vtiger_webforms_field'],
						['type' => 'remove', 'name' => 'vtiger_webforms'],
				];
				break;
			case 2:
				$tables = [
						['type' => 'add', 'name' => 's_yf_companies', 'sql' => "`s_yf_companies` (
							`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
							`name` varchar(100) NOT NULL,
							`short_name` varchar(100) DEFAULT NULL,
							`default` tinyint(1) unsigned NOT NULL DEFAULT '0',
							`industry` varchar(50) DEFAULT NULL,
							`street` varchar(150) DEFAULT NULL,
							`city` varchar(100) DEFAULT NULL,
							`code` varchar(30) DEFAULT NULL,
							`state` varchar(100) DEFAULT NULL,
							`country` varchar(100) DEFAULT NULL,
							`phone` varchar(30) DEFAULT NULL,
							`fax` varchar(30) DEFAULT NULL,
							`website` varchar(100) DEFAULT NULL,
							`vatid` varchar(50) DEFAULT NULL,
							`id1` varchar(50) DEFAULT NULL,
							`id2` varchar(50) DEFAULT NULL,
							`email` varchar(100) DEFAULT NULL,
							`logo_login` varchar(50) DEFAULT NULL,
							`logo_login_height` tinyint(3) unsigned DEFAULT NULL,
							`logo_main` varchar(50) DEFAULT NULL,
							`logo_main_height` tinyint(3) unsigned DEFAULT NULL,
							`logo_mail` varchar(50) DEFAULT NULL,
							`logo_mail_height` tinyint(3) unsigned DEFAULT NULL,
							PRIMARY KEY (`id`)
						  )"],
				];
				break;
			case 3:
				$tables = [
						['type' => 'add', 'name' => 'w_yf_portal_session', 'sql' => "`w_yf_portal_session` (
							`id` varchar(32) NOT NULL,
							`user_id` int(19) DEFAULT NULL,
							`language` varchar(10) DEFAULT NULL,
							`created` datetime DEFAULT NULL,
							`changed` datetime DEFAULT NULL,
							`params` text,
							PRIMARY KEY (`id`)
						  )"],
						['type' => 'add', 'name' => 'w_yf_portal_user', 'sql' => "`w_yf_portal_user` (
							`id` int(19) NOT NULL AUTO_INCREMENT,
							`server_id` int(10) DEFAULT NULL,
							`status` tinyint(1) DEFAULT '0',
							`user_name` varchar(50) NOT NULL,
							`password_h` varchar(200) DEFAULT NULL,
							`password_t` varchar(200) DEFAULT NULL,
							`type` tinyint(1) unsigned DEFAULT '1',
							`login_time` datetime DEFAULT NULL,
							`logout_time` datetime DEFAULT NULL,
							`language` varchar(10) DEFAULT NULL,
							`crmid` int(19) DEFAULT NULL,
							`user_id` int(19) DEFAULT NULL,
							PRIMARY KEY (`id`),
							UNIQUE KEY `user_name` (`user_name`),
							KEY `user_name_2` (`user_name`,`status`)
						  )"],
						['type' => 'remove', 'name' => 'w_yf_portal_users'],
						['type' => 'remove', 'name' => 'w_yf_sessions'],
				];
				break;
			case 4:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_ossdocumentcontrol_cnd'],
						['type' => 'remove', 'name' => 'vtiger_taxclass_seq'],
						['type' => 'remove', 'name' => 'vtiger_taxclass'],
						['type' => 'remove', 'name' => 'vtiger_customerportal_tabs'],
						['type' => 'remove', 'name' => 'vtiger_customerportal_prefs'],
						['type' => 'remove', 'name' => 'vtiger_customerportal_fields'],
				];
				break;
			case 5:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_portalinfo'],
						['type' => 'remove', 'name' => 'vtiger_organizationdetails_seq'],
						['type' => 'remove', 'name' => 'vtiger_organizationdetails'],
						['type' => 'remove', 'name' => 'vtiger_oss_project_templates'],
						['type' => 'remove', 'name' => 'vtiger_campaignrelstatus_seq'],
						['type' => 'remove', 'name' => 'vtiger_campaignrelstatus'],
						['type' => 'remove', 'name' => 'yetiforce_mobile_keys'],
						['type' => 'remove', 'name' => 'yetiforce_mobile_pushcall'],
						['type' => 'remove', 'name' => 'w_yf_pos_actions'],
						['type' => 'remove', 'name' => 'w_yf_pos_users'],
						['type' => 'remove', 'name' => 'vtiger_recurringtype_seq'],
						['type' => 'remove', 'name' => 'vtiger_recurringtype'],
						['type' => 'remove', 'name' => 'vtiger_recurringevents'],
						['type' => 'remove', 'name' => 'vtiger_blocks_seq'],
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	private function setTablesScheme($tables)
	{
		$db = \PearDatabase::getInstance();
		foreach ($tables as $table) {
			if (empty($table)) {
				continue;
			}
			switch ($table['type']) {
				case 'add':
					$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE = InnoDB DEFAULT CHARSET = "utf8";
');
					break;
				case 'remove':
					$db->query('DROP TABLE IF EXISTS ' . $table['name'] . ';
');
					break;
				case 'rename':
					$result = $db->query("SHOW TABLES LIKE '" . $table['name'] . "';");
					if ($result->rowCount()) {
						$db->query($table['sql']);
					}
					break;
				case 'exception':
					$db->query($table['sql']);
					break;
				default:
					break;
			}
		}
	}

	private function addModules($modules)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = PearDatabase::getInstance();
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		$dirName = 'cache/updates/files/';
		foreach ($modules as $module) {
			if (file_exists('cache/updates/' . $module . '.xml') && !\vtlib\Module::getInstance($module)) {
				$locations = ['modules/' . $module];
				foreach ($locations as $loc) {
					if (is_dir($dirName . $loc) && !file_exists($rootDir . $loc)) {
						mkdir($rootDir . $loc);
					}
					\vtlib\Functions::recurseCopy($dirName . $loc, $loc, true);
					\vtlib\Functions::recurseDelete($dirName . $loc);
				}

				$importInstance = new \vtlib\PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/' . $module . '.xml');
				$importInstance->import_Module();
				unlink('cache/updates/' . $module . '.xml');
				$this->postInstalModule($module);
			} else {
				\App\Log::warning('Exiting ' . __METHOD__ . ' | Module exists: ' . $module);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function postInstalModule($moduleName)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		$prefix = $this->getPrefix($moduleName);
		$moduleData = \vtlib\Functions::getModuleData($moduleName);
		\App\Fields\RecordNumber::setNumber($moduleData['tabid'], $prefix, '1');
		if ('CMileageLogbook' !== $moduleName) {
			$modcommentsModuleInstance = \vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo([$moduleName]);
			}
		}
		CRMEntity::getInstance('ModTracker')->enableTrackingForModule($moduleData['tabid']);
		$this->setWidgetToSummary($this->getWidgetToSummary($moduleName));
	}

	/**
	 * Get prefix
	 * @param string $moduleName
	 * @return string
	 */
	private function getPrefix($moduleName)
	{
		$prefixes = [
			'CFixedAssets' => 'FA',
			'CInternalTickets' => 'IT',
			'FInvoiceCost' => 'FC',
			'CMileageLogbook' => 'ML',
			'SVendorEnquiries' => 'S-VE'
		];
		return $prefixes[$moduleName];
	}

	private function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'ip', 'table' => 'o_yf_access_for_user', 'sql' => "ALTER TABLE `o_yf_access_for_user` 
							CHANGE `ip` `ip` varchar(100) NULL after `date` , 
							CHANGE `agent` `agent` varchar(255) NULL after `url` ;"],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setAlterTables($data)
	{
		$db = PearDatabase::getInstance();
		$db->query('SET FOREIGN_KEY_CHECKS = 0;');
		if (!empty($data)) {
			foreach ($data as $alter) {
				switch ($alter['type'][1]) {
					case 'Key_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Key_name="' . $alter['name'] . '";';
						break;
					case 'Column_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Column_name="' . $alter['name'] . '";';
						break;
					case 'exception':
						$db->query($alter['sql']);
						continue;
						break;
					default:
						if ($alter['type'][0] == 'changeTable') {
							$checkSql = 'SHOW TABLE STATUS WHERE NAME LIKE "' . $alter['table'] . '";';
						} else {
							$checkSql = 'SHOW COLUMNS FROM `' . $alter['table'] . '` LIKE "' . $alter['name'] . '";';
						}
						break;
				}
				if (!$checkSql) {
					continue;
				}
				$result = $db->query($checkSql);
				$num = $result->rowCount();
				if (( $num === 0 && $alter['type'][0] === 'add') || ($num > 0 && $alter['type'][0] === 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && in_array($alter['type'][0], ['change', 'changeTable'])) {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
	}

	private function addRecords($moduleName)
	{
		$db = \App\Db::getInstance();
		$location = "cache/updates/$moduleName";
		$zipfile = "cache/updates/$moduleName.zip";
		$filesName = [];
		if (file_exists($location)) {
			\vtlib\Functions::recurseDelete($location);
			rmdir($location);
		}
		if (file_exists($zipfile)) {
			mkdir($location);
			$unzip = new vtlib\Unzip($zipfile);
			$unzip->unzipAllEx($location);
			foreach ($unzip->getList() as $name => $data) {
				$filesName[] = $name;
			}
			$unzip->__destroy();
			unlink($zipfile);
		}
		foreach ($filesName as $name) {
			$recordData = [];
			$filePatch = $location . DIRECTORY_SEPARATOR . $name;
			if (file_exists($filePatch)) {
				$xmlToImport = new XMLReader();
				$xmlToImport->open($filePatch);
				while ($xmlToImport->read()) {
					if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
						if ($xmlToImport->localName !== 'MODULE_FIELDS') {
							$recordData[$xmlToImport->localName] = $xmlToImport->readString();
						}
					}
				}
				$this->addRec($recordData);
			}
		}
		$number = (new \App\Db\Query())->select(['number'])->from('u_yf_emailtemplates')->orderBy(['number' => SORT_DESC])->limit(1)->scalar();
		$number = (int) str_replace('N', '', $number);
		if ($number && $number !== 1) {
			$num = $number + 1;
			$db->createCommand()->update('vtiger_modentity_num', ['cur_id' => $num], ['tabid' => \vtlib\Functions::getModuleId('EmailTemplates')])->execute();
		}
	}

	private function addRec($recordData)
	{
		$db = App\Db::getInstance();
		if (empty($recordData)) {
			\App\Log::warning('Exiting ' . __METHOD__ . ' | No data');
			return;
		}
		$isSys = false;
		$query = (new \App\Db\Query())->from('u_yf_emailtemplates');
		if ($recordData['sys_name']) {
			$query->where(['sys_name' => $recordData['sys_name']]);
			$isSys = true;
		} else {
			$query->where(['name' => $recordData['name']]);
		}
		if (!$query->exists()) {
			$recordModel = Vtiger_Record_Model::getCleanInstance('EmailTemplates');
			$recordData['assigned_user_id'] = \App\User::getCurrentUserRealId();
			$recordData['created_user_id'] = \App\User::getCurrentUserRealId();
			foreach ($recordData as $key => $value) {
				$recordModel->set($key, $value);
			}
			$recordModel->save();
			$db->createCommand()->update('vtiger_crmentity', ['smownerid' => $recordData['assigned_user_id'], 'smcreatorid' => $recordData['created_user_id'], 'users' => null], ['crmid' => $recordModel->getId()])->execute();
			if ($isSys) {
				$db->createCommand()->update('u_yf_emailtemplates', ['sys_name' => $recordData['sys_name']], ['emailtemplatesid' => $recordModel->getId()])->execute();
			}
		}
	}

	private function updateData()
	{
		$db = App\Db::getInstance();
		$db->createCommand()->delete('vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail'])->execute();
		if (!$db->isTableExists('s_yf_companies')) {
			$data = (new \App\Db\Query())->select(['name' => 'organizationname', 'street' => 'address', 'city', 'state', 'country', 'code', 'phone', 'fax', 'website', 'logo_login' => 'panellogoname', 'logo_login_height' => 'height_panellogo', 'logo_main' => 'logoname', 'vatid', 'id1', 'id2', 'email'])->from('vtiger_organizationdetails')->limit(1)->one();
			$data['default'] = 1;
			$this->setTablesScheme($this->getTablesAction(2));
			$db->createCommand()->insert('s_yf_companies', $data)->execute();
		}
		$fieldModel = Vtiger_Field_Model::getInstance('reservations_status', Vtiger_Module_Model::getInstance('Reservations'));
		$picklist = \App\Fields\Picklist::getPickListValues('reservations_status');
		if ($fieldModel && !in_array('PLL_DRAFT', $picklist)) {
			$fieldModel->setPicklistValues(['PLL_DRAFT', 'PLL_CANCELLED']);
		}
		$this->addBlocks();
		$this->setFields($this->getFields(2));
		$fieldModel = Vtiger_Field_Model::getInstance('finvoice_status', Vtiger_Module_Model::getInstance('FInvoice'));
		if ($fieldModel) {
			$valueId = (new \App\Db\Query())->select(['picklist_valueid'])->from('vtiger_finvoice_status')->where([$fieldModel->getName() => 'None'])->limit(1)->scalar();
			if ($valueId) {
				$db->createCommand()->delete('vtiger_role2picklist', ['picklistvalueid' => $valueId])->execute();
				$db->createCommand()->delete('vtiger_finvoice_status', [$fieldModel->getName() => 'None'])->execute();
			}
			$picklist = \App\Fields\Picklist::getPickListValues('finvoice_status');
			if (!in_array('PLL_UNASSIGNED', $picklist)) {
				$fieldModel->setPicklistValues(['PLL_UNASSIGNED', 'PLL_AWAITING_REALIZATION', 'PLL_FOR_PROCESSING', 'PLL_IN_PROGRESSING', 'PLL_SUBMITTED_COMMENTS', 'PLL_FOR_APPROVAL', 'PLL_CANCELLED', 'PLL_ACCEPTED']);
			}
		}
		$this->setTrees($this->getTrees(1));
		$this->setFields($this->getFields(3));
		$db->createCommand()->update('vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Companies&view=List'], ['linkto' => 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails'])->execute();
		$db->createCommand()->delete('vtiger_ws_entity_fieldtype', ['table_name' => ['vtiger_organizationdetails', 'vtiger_inventoryproductrel']])->execute();
		$db->createCommand()->delete('vtiger_ws_entity_name', ['table_name' => 'vtiger_organizationdetails'])->execute();
		$db->createCommand()->delete('vtiger_ws_entity_tables', ['table_name' => 'vtiger_organizationdetails'])->execute();
		$db->createCommand()->delete('vtiger_ws_fieldinfo', ['id' => 'vtiger_organizationdetails.organization_id'])->execute();
		$db->createCommand()->delete('vtiger_ws_entity_referencetype', ['type' => ['Users', 'Products']])->execute();

		$keys = ['tabid', 'related_tabid', 'name', 'sequence', 'label', 'presence', 'actions', 'favorites', 'creator_detail', 'relation_comment'];
		$relData = [\vtlib\Functions::getModuleId('Accounts'), \vtlib\Functions::getModuleId('Notification'), 'getDependentsList', 29, 'Notification', 0, 'ADD', 0, 0, 0];
		$data = array_combine($keys, $relData);
		$isExists = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $data['tabid'], 'related_tabid' => $data['related_tabid'], 'name' => $data['name']])->exists();
		if (!$isExists) {
			$db->createCommand()->insert('vtiger_relatedlists', $data)->execute();
		}
		$relData = [\vtlib\Functions::getModuleId('HelpDesk'), \vtlib\Functions::getModuleId('Notification'), 'getDependentsList', 22, 'Notification', 0, 'ADD', 0, 0, 0];
		$data = array_combine($keys, $relData);
		$isExists = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $data['tabid'], 'related_tabid' => $data['related_tabid'], 'name' => $data['name']])->exists();
		if (!$isExists) {
			$db->createCommand()->insert('vtiger_relatedlists', $data)->execute();
		}
		$this->actionMapp($this->getActionMapp(2));
		$this->setTablesScheme($this->getTablesAction(3));
		$db->createCommand()->delete('vtiger_ws_entity', ['name' => 'CompanyDetails'])->execute();
		$isExists = (new \App\Db\Query())->from('vtiger_ws_fieldtype')->where(['fieldtype' => 'companySelect'])->exists();
		if (!$isExists) {
			$db->createCommand()->insert('vtiger_ws_fieldtype', ['uitype' => 310, 'fieldtype' => 'companySelect'])->execute();
		}
		$this->removeFields($this->getFieldsToRemove(2));
		$this->removeModules(['OSSProjectTemplates']);
		$db->createCommand()->delete('vtiger_links', ['linkurl' => 'index.php?module=OSSProjectTemplates&view=GenerateProject'])->execute();
		$tableSchema = $db->getSchema()->getTableSchema('vtiger_activity_reminder_popup');
		$columnSchema = $tableSchema->getColumn('semodule');
		if ($columnSchema) {
			$db->createCommand()->dropColumn('vtiger_activity_reminder_popup', 'semodule')->execute();
		}
		$isExists = (new \App\Db\Query())->from('a_yf_taxes_global')->where(['value' => 23.00])->exists();
		if (!$isExists) {
			$db->createCommand()->insert('a_yf_taxes_global', ['name' => VAT, 'value' => 23.00, 'status' => 0])->execute();
		}
		$this->removeModules(['OSSDocumentControl']);
		$this->setTablesScheme($this->getTablesAction(4));
		$this->removeModules(['CustomerPortal']);
		$workflowId = (new \App\Db\Query())->from('com_vtiger_workflows')->where(['summary' => 'Generate Customer Login Details', 'module_name' => 'Contacts'])->scalar();
		if ($workflowId) {
			$db->createCommand()->delete('com_vtiger_workflows', ['workflow_id' => $workflowId])->execute();
			$db->createCommand()->delete('com_vtiger_workflowtasks', ['workflow_id' => $workflowId])->execute();
		}
		$db->createCommand()->delete('com_vtiger_workflowtasks', ['summary' => 'Mark portal users password as sent.'])->execute();
		$db->createCommand()->delete('com_vtiger_workflowtasks_entitymethod', ['method_name' => 'CreatePortalLoginDetails', 'module_name' => 'Contacts'])->execute();
		$db->createCommand()->delete('com_vtiger_workflowtasks_entitymethod', ['method_name' => 'MarkPasswordSent', 'module_name' => 'Contacts'])->execute();
		$db->createCommand()->delete('vtiger_ws_fieldtype', ['fieldtype' => 'posList'])->execute();
		$this->setFields($this->getFields(4));
		$isExists = (new \App\Db\Query())->from('vtiger_ws_fieldtype')->where(['uitype' => 342])->exists();
		if (!$isExists) {
			$db->createCommand()->insert('vtiger_ws_fieldtype', ['uitype' => 342, 'fieldtype' => 'recurrence'])->execute();
		}
		$isExists = (new \App\Db\Query())->from('vtiger_blocks')
				->innerJoin('vtiger_field', 'vtiger_field.block = vtiger_blocks.blockid')
				->where(['vtiger_blocks.blocklabel' => 'LBL_SYNCHRONIZE_POS', 'vtiger_blocks.tabid' => \vtlib\Functions::getModuleId('SSingleOrders')])->exists();
		if (!$isExists) {
			$db->createCommand()->delete('vtiger_blocks', ['blocklabel' => 'LBL_SYNCHRONIZE_POS', 'tabid' => \vtlib\Functions::getModuleId('SSingleOrders')])->execute();
		}
		$this->cron($this->getCronData(1));
		$this->removeFields($this->getFieldsToRemove(3));
		$tableSchema = $db->getSchema()->getTableSchema('vtiger_activity_reminder');
		$columnSchema = $tableSchema->getColumn('recurringid');
		if ($columnSchema) {
			$db->createCommand()->dropColumn('vtiger_activity_reminder', 'recurringid')->execute();
		}
		$res = $db->createCommand()->update('vtiger_subindustry', ['subindustry' => 'District'], ['subindustry' => 'Poviat'])->execute();
		if ($res) {
			$db->createCommand()->update('vtiger_leaddetails', ['subindustry' => 'District'], ['subindustry' => 'Poviat'])->execute();
		}
		$res = $db->createCommand()->update('vtiger_subindustry', ['subindustry' => 'Developers'], ['subindustry' => 'Deweloperzy'])->execute();
		if ($res) {
			$db->createCommand()->update('vtiger_leaddetails', ['subindustry' => 'Developers'], ['subindustry' => 'Deweloperzy'])->execute();
		}
		$res = $db->createCommand()->update('vtiger_subindustry', ['subindustry' => 'District Job Center'], ['subindustry' => 'Poviat Job Centre'])->execute();
		if ($res) {
			$db->createCommand()->update('vtiger_leaddetails', ['subindustry' => 'District Job Center'], ['subindustry' => 'Poviat Job Centre'])->execute();
		}
		$dataReader = (new \App\Db\Query())->from('vtiger_picklist_dependency')->where(['or like', 'targetvalues', ['"Poviat"', '"Deweloperzy"', '"Poviat Job Centre"']])->createCommand()->query();
		while ($raw = $dataReader->read()) {
			$raw['targetvalues'] = str_replace("Poviat Job Centre", "District Job Center", $raw['targetvalues']);
			$raw['targetvalues'] = str_replace("Poviat", "District", $raw['targetvalues']);
			$raw['targetvalues'] = str_replace("Deweloperzy", "Developers", $raw['targetvalues']);
			$db->createCommand()->update('vtiger_picklist_dependency', ['targetvalues' => $raw['targetvalues']], ['id' => $raw['id']])->execute();
		}
		$res = $db->createCommand()->delete('vtiger_ws_fieldtype', ['fieldtype' => 'streetAddress'])->execute();
		if ($res) {
			$db->createCommand()->update('vtiger_field', ['uitype' => 1, 'displaytype' => 1], ['uitype' => 306])->execute();
		}
		$tableSchema = $db->getSchema()->getTableSchema('s_yf_companies');
		$columnSchema = $tableSchema->getColumn('industry');
		if (!$columnSchema) {
			\vtlib\Utils::AddColumn('s_yf_companies', 'industry', ['string', 50]);
		}
		$db->createCommand()->update('vtiger_field', ['typeofdata' => 'V~O'], ['typeofdata' => 'V~O~LE~100', 'columnname' => ['buildingnumbera', 'localnumbera', 'buildingnumberb', 'localnumberb', 'buildingnumberc', 'localnumberc']])->execute();
		$db->createCommand()->update('vtiger_field', ['uitype' => 1], ['uitype' => 307])->execute();
		$db->createCommand()->update('vtiger_field', ['typeofdata' => 'V~M'], ['typeofdata' => 'V~O', 'columnname' => 'finvoice_formpayment'])->execute();
		$this->cleanDB();
	}

	private function cleanDB()
	{
		$db = App\Db::getInstance();
		$query = (new \App\Db\Query())->select('vtiger_field.fieldid')->from('vtiger_field');
		$db->createCommand()->delete('vtiger_fieldmodulerel', ['not', ['vtiger_fieldmodulerel.fieldid' => $query]])->execute();
	}

	private function updateSettingMenu()
	{
		$db = App\Db::getInstance();

		$fieldsToDelete = [
				['LBL_MAIL_TOOLS', 'LBL_MAIL_SERVER_SETTINGS'],
				['LBL_INTEGRATION', 'LBL_CUSTOMER_PORTAL'],
				['LBL_INTEGRATION', 'Webforms'],
				['LBL_AUTOMATION', 'Document Control'],
				['LBL_AUTOMATION', 'Project Templates'],
				['LBL_INTEGRATION', 'LBL_POS'],
				['LBL_INTEGRATION', 'LBL_MOBILE_KEYS'],
		];
		foreach ($fieldsToDelete as $row) {
			$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_settings_field')
					->innerJoin('vtiger_settings_blocks', 'vtiger_settings_field.blockid = vtiger_settings_blocks.blockid')
					->where(['vtiger_settings_field.name' => $row[1], 'vtiger_settings_blocks.label' => $row[0]])->scalar();
			if ($fieldId) {
				$db->createCommand()->delete('vtiger_settings_field', ['fieldid' => $fieldId])->execute();
			}
		}
		$menu = [
				['LBL_USER_MANAGEMENT', 'LBL_USERS', 'adminIcon-user', 'LBL_USER_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=List', '1', '0', '1'],
				['LBL_USER_MANAGEMENT', 'LBL_ROLES', 'adminIcon-roles', 'LBL_ROLE_DESCRIPTION', 'index.php?module=Roles&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_PROFILES', 'adminIcon-profiles', 'LBL_PROFILE_DESCRIPTION', 'index.php?module=Profiles&parent=Settings&view=List', '3', '0', '0'],
				['LBL_USER_MANAGEMENT', 'USERGROUPLIST', 'adminIcon-groups', 'LBL_GROUP_DESCRIPTION', 'index.php?module=Groups&parent=Settings&view=List', '4', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_SHARING_ACCESS', 'adminIcon-module-access', 'LBL_SHARING_ACCESS_DESCRIPTION', 'index.php?module=SharingAccess&parent=Settings&view=Index', '5', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_FIELDS_ACCESS', 'adminIcon-special-access', 'LBL_SHARING_FIELDS_DESCRIPTION', 'index.php?module=FieldAccess&parent=Settings&view=Index', '6', '0', '0'],
				['LBL_LOGS', 'LBL_LOGIN_HISTORY_DETAILS', 'adminIcon-users-login', 'LBL_LOGIN_HISTORY_DESCRIPTION', 'index.php?module=LoginHistory&parent=Settings&view=List', '3', '0', '0'],
				['LBL_STUDIO', 'VTLIB_LBL_MODULE_MANAGER', 'adminIcon-modules-installation', 'VTLIB_LBL_MODULE_MANAGER_DESCRIPTION', 'index.php?module=ModuleManager&parent=Settings&view=List', '1', '0', '1'],
				['LBL_STUDIO', 'LBL_PICKLIST_EDITOR', 'adminIcon-fields-picklists', 'LBL_PICKLIST_DESCRIPTION', 'index.php?parent=Settings&module=Picklist&view=Index', '9', '0', '1'],
				['LBL_STUDIO', 'LBL_PICKLIST_DEPENDENCY_SETUP', 'adminIcon-fields-picklists-relations', 'LBL_PICKLIST_DEPENDENCY_DESCRIPTION', 'index.php?parent=Settings&module=PickListDependency&view=List', '10', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_ADVANCED_PERMISSION', 'glyphicon glyphicon-ice-lolly', NULL, 'index.php?module=AdvancedPermission&parent=Settings&view=List', '10', '0', '0'],
				['LBL_COMPANY', 'NOTIFICATIONSCHEDULERS', '', 'LBL_NOTIF_SCHED_DESCRIPTION', 'index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings', '4', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'INVENTORYNOTIFICATION', '', 'LBL_INV_NOTIF_DESCRIPTION', 'index.php?module=Settings&view=listinventorynotifications&parenttab=Settings', '1', '0', '0'],
				['LBL_COMPANY', 'LBL_COMPANY_DETAILS', 'adminIcon-company-detlis', 'LBL_COMPANY_DESCRIPTION', 'index.php?parent=Settings&module=Companies&view=List', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_CURRENCY_SETTINGS', 'adminIcon-currencies', 'LBL_CURRENCY_DESCRIPTION', 'index.php?parent=Settings&module=Currency&view=List', '4', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_SWITCH_USERS', 'adminIcon-users', 'LBL_SWITCH_USERS_DESCRIPTION', 'index.php?module=Users&view=SwitchUsers&parent=Settings', '11', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_SYSTEM_INFO', 'adminIcon-server-configuration', 'LBL_SYSTEM_DESCRIPTION', 'index.php?module=Settings&submodule=Server&view=ProxyConfig', '6', '1', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_DEFAULT_MODULE_VIEW', 'adminIcon-standard-modules', 'LBL_DEFAULT_MODULE_VIEW_DESC', 'index.php?module=Settings&action=DefModuleView&parenttab=Settings', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_TERMS_AND_CONDITIONS', 'adminIcon-terms-and-conditions', 'LBL_INV_TANDC_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit', '3', '0', '0'],
				['LBL_STUDIO', 'LBL_CUSTOMIZE_RECORD_NUMBERING', 'adminIcon-recording-control', 'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering', '6', '0', '0'],
				['LBL_AUTOMATION', 'LBL_LIST_WORKFLOWS', 'adminIcon-triggers', 'LBL_LIST_WORKFLOWS_DESCRIPTION', 'index.php?module=Workflows&parent=Settings&view=List', '1', '0', '1'],
				['LBL_SYSTEM_TOOLS', 'LBL_CONFIG_EDITOR', 'adminIcon-system-tools', 'LBL_CONFIG_EDITOR_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail', '7', '0', '0'],
				['LBL_AUTOMATION', 'Scheduler', 'adminIcon-cron', 'LBL_SCHEDULER_DESCRIPTION', 'index.php?module=CronTasks&parent=Settings&view=List', '3', '0', '0'],
				['LBL_AUTOMATION', 'LBL_WORKFLOW_LIST', 'adminIcon-workflow', 'LBL_AVAILABLE_WORKLIST_LIST', 'index.php?module=com_vtiger_workflow&action=workflowlist', '1', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'ModTracker', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_DESCRIPTION', 'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker', '9', '0', '0'],
				['LBL_INTEGRATION', 'LBL_PBXMANAGER', 'adminIcon-pbx-manager', 'LBL_PBXMANAGER_DESCRIPTION', 'index.php?module=PBXManager&parent=Settings&view=Index', '22', '0', '0'],
				['LBL_STUDIO', 'LBL_EDIT_FIELDS', 'adminIcon-modules-fields', 'LBL_LAYOUT_EDITOR_DESCRIPTION', 'index.php?module=LayoutEditor&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_PDF', 'adminIcon-modules-pdf-templates', 'LBL_PDF_DESCRIPTION', 'index.php?module=PDF&parent=Settings&view=List', '10', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'LBL_PASSWORD_CONF', 'adminIcon-passwords-configuration', 'LBL_PASSWORD_DESCRIPTION', 'index.php?module=Password&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_STUDIO', 'LBL_MENU_BUILDER', 'adminIcon-menu-configuration', 'LBL_MENU_BUILDER_DESCRIPTION', 'index.php?module=Menu&view=Index&parent=Settings', '14', '0', '1'],
				['LBL_STUDIO', 'LBL_ARRANGE_RELATED_TABS', 'adminIcon-modules-relations', 'LBL_ARRANGE_RELATED_TABS', 'index.php?module=LayoutEditor&parent=Settings&view=Index&mode=showRelatedListLayout', '4', '0', '1'],
				['LBL_MAIL_TOOLS', 'Mail Scanner', 'adminIcon-mail-scanner', 'LBL_MAIL_SCANNER_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=Index', '3', '0', '0'],
				['LBL_LOGS', 'Mail Logs', 'adminIcon-mail-download-history', 'LBL_MAIL_LOGS_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=logs', '4', '0', '0'],
				['LBL_MAIL_TOOLS', 'Mail View', 'adminIcon-oss_mailview', 'LBL_MAIL_VIEW_DESCRIPTION', 'index.php?module=OSSMailView&parent=Settings&view=index', '21', '0', '0'],
				['LBL_About_YetiForce', 'License', 'adminIcon-license', 'LBL_LICENSE_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=License', '4', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'OSSPassword Configuration', 'adminIcon-passwords-encryption', 'LBL_OSSPASSWORD_CONFIGURATION_DESCRIPTION', 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings', '3', '0', '0'],
				['LBL_AUTOMATION', 'LBL_DATAACCESS', 'adminIcon-recording-control', 'LBL_DATAACCESS_DESCRIPTION', 'index.php?module=DataAccess&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LangManagement', 'adminIcon-languages-and-translations', 'LBL_LANGMANAGEMENT_DESCRIPTION', 'index.php?module=LangManagement&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_USER_MANAGEMENT', 'GlobalPermission', 'adminIcon-special-access', 'LBL_GLOBALPERMISSION_DESCRIPTION', 'index.php?module=GlobalPermission&parent=Settings&view=Index', '7', '0', '0'],
				['LBL_SEARCH_AND_FILTERS', 'Search Setup', 'adminIcon-search-configuration', 'LBL_SEARCH_SETUP_DESCRIPTION', 'index.php?module=Search&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_SEARCH_AND_FILTERS', 'CustomView', 'adminIcon-filters-configuration', 'LBL_CUSTOMVIEW_DESCRIPTION', 'index.php?module=CustomView&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_STUDIO', 'Widgets', 'adminIcon-modules-widgets', 'LBL_WIDGETS_DESCRIPTION', 'index.php?module=Widgets&parent=Settings&view=Index', '3', '0', '1'],
				['LBL_About_YetiForce', 'Credits', 'adminIcon-contributors', 'LBL_CREDITS_DESCRIPTION', 'index.php?module=Vtiger&view=Credits&parent=Settings', '3', '0', '0'],
				['LBL_STUDIO', 'LBL_QUICK_CREATE_EDITOR', 'adminIcon-fields-quick-create', 'LBL_QUICK_CREATE_EDITOR_DESCRIPTION', 'index.php?module=QuickCreateEditor&parent=Settings&view=Index', '8', '0', '0'],
				['LBL_INTEGRATION', 'LBL_API_ADDRESS', 'adminIcon-address', 'LBL_API_ADDRESS_DESCRIPTION', 'index.php?module=ApiAddress&parent=Settings&view=Configuration', '5', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'LBL_BRUTEFORCE', 'adminIcon-brute-force', 'LBL_BRUTEFORCE_DESCRIPTION', 'index.php?module=BruteForce&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_LOGS', 'LBL_UPDATES_HISTORY', 'adminIcon-server-updates', 'LBL_UPDATES_HISTORY_DESCRIPTION', 'index.php?parent=Settings&module=Updates&view=Index', '2', '0', '0'],
				['LBL_LOGS', 'LBL_CONFREPORT', 'adminIcon-server-configuration', 'LBL_CONFREPORT_DESCRIPTION', 'index.php?parent=Settings&module=ConfReport&view=Index', '1', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_ACTIVITY_TYPES', 'adminIcon-calendar-types', 'LBL_ACTIVITY_TYPES_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=ActivityTypes', '1', '0', '0'],
				['LBL_STUDIO', 'LBL_WIDGETS_MANAGEMENT', 'adminIcon-widgets-configuration', 'LBL_WIDGETS_MANAGEMENT_DESCRIPTION', 'index.php?module=WidgetsManagement&parent=Settings&view=Configuration', '15', '0', '0'],
				['LBL_STUDIO', 'LBL_TREES_MANAGER', 'adminIcon-field-folders', 'LBL_TREES_MANAGER_DESCRIPTION', 'index.php?module=TreesManager&parent=Settings&view=List', '11', '0', '0'],
				['LBL_STUDIO', 'LBL_MODTRACKER_SETTINGS', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_SETTINGS_DESCRIPTION', 'index.php?module=ModTracker&parent=Settings&view=List', '5', '0', '0'],
				['LBL_STUDIO', 'LBL_HIDEBLOCKS', 'adminIcon-filed-hide-bloks', 'LBL_HIDEBLOCKS_DESCRIPTION', 'index.php?module=HideBlocks&parent=Settings&view=List', '12', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_PUBLIC_HOLIDAY', 'adminIcon-calendar-holidys', 'LBL_PUBLIC_HOLIDAY_DESCRIPTION', 'index.php?module=PublicHoliday&view=Configuration&parent=Settings', '3', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_CALENDAR_CONFIG', 'adminIcon-calendar-configuration', 'LBL_CALENDAR_CONFIG_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=UserColors', '2', '0', '0'],
				['LBL_PROCESSES', 'LBL_SALES_PROCESSES', 'adminIcon-sales', 'LBL_SALES_PROCESSES_DESCRIPTION', 'index.php?module=SalesProcesses&view=Index&parent=Settings', '2', '0', '0'],
				['LBL_INTEGRATION', 'LBL_DAV_KEYS', 'adminIcon-dav-applications', 'LBL_DAV_KEYS_DESCRIPTION', 'index.php?parent=Settings&module=Dav&view=Keys', '7', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_AUTOLOGIN', 'adminIcon-mail-auto-login', 'LBL_AUTOLOGIN_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Autologin', '4', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_MAIL_GENERAL_CONFIGURATION', 'adminIcon-mail-smtp-server', 'LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Config', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_SUPPORT_PROCESSES', 'adminIcon-support ', 'LBL_SUPPORT_PROCESSES_DESCRIPTION', 'index.php?module=SupportProcesses&view=Index&parent=Settings', '6', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_COLORS', 'adminIcon-colors', 'LBL_COLORS_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=Colors', '4', '0', '0'],
				['LBL_PROCESSES', 'LBL_REALIZATION_PROCESSES', 'adminIcon-realization', 'LBL_REALIZATION_PROCESSES_DESCRIPTION', 'index.php?module=RealizationProcesses&view=Index&parent=Settings', '3', '0', '0'],
				['LBL_PROCESSES', 'LBL_MARKETING_PROCESSES', 'adminIcon-marketing', 'LBL_MARKETING_PROCESSES_DESCRIPTION', 'index.php?module=MarketingProcesses&view=Index&parent=Settings', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_FINANCIAL_PROCESSES', 'adminIcon-finances', 'LBL_FINANCIAL_PROCESSES_DESCRIPTION', 'index.php?module=FinancialProcesses&view=Index&parent=Settings', '5', '0', '0'],
				['LBL_INTEGRATION', 'LBL_AUTHORIZATION', 'adminIcon-automation', 'LBL_AUTHORIZATION_DESCRIPTION', 'index.php?module=Users&view=Auth&parent=Settings', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_TIMECONTROL_PROCESSES', 'adminIcon-logistics', 'LBL_TIMECONTROL_PROCESSES_DESCRIPTION', 'index.php?module=TimeControlProcesses&parent=Settings&view=Index', '7', '0', '0'],
				['LBL_STUDIO', 'LBL_CUSTOM_FIELD_MAPPING', 'adminIcon-filed-mapping', 'LBL_CUSTOM_FIELD_MAPPING_DESCRIPTION', 'index.php?parent=Settings&module=Leads&view=MappingDetail', '13', '0', '0'],
				['LBL_INTEGRATION', 'LBL_CURRENCY_UPDATE', 'adminIcon-currencies', 'LBL_CURRENCY_UPDATE_DESCRIPTION', 'index.php?module=CurrencyUpdate&view=Index&parent=Settings', '2', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_CREDITLIMITS', 'adminIcon-credit-limit-base_2', 'LBL_CREDITLIMITS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=CreditLimits', '5', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_TAXES', 'adminIcon-taxes-rates', 'LBL_TAXES_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Taxes', '1', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTS', 'adminIcon-discount-base', 'LBL_DISCOUNTS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Discounts', '3', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_TAXCONFIGURATION', 'adminIcon-taxes-caonfiguration', 'LBL_TAXCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=TaxConfiguration', '4', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTCONFIGURATION', 'adminIcon-discount-configuration', 'LBL_DISCOUNTCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=DiscountConfiguration', '2', '0', '0'],
				['LBL_MAIL_TOOLS', 'Mail', 'adminIcon-mail-download-history', 'LBL_OSSMAIL_DESCRIPTION', 'index.php?module=OSSMail&parent=Settings&view=index', '2', '0', '0'],
				['LBL_STUDIO', 'LBL_MAPPEDFIELDS', 'adminIcon-mapped-fields', 'LBL_MAPPEDFIELDS_DESCRIPTION', 'index.php?module=MappedFields&parent=Settings&view=List', '16', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_LOCKS', 'adminIcon-locks', 'LBL_LOCKS_DESCRIPTION', 'index.php?module=Users&view=Locks&parent=Settings', '8', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_NOTIFICATIONS_CONFIGURATION', 'adminIcon-NotificationConfiguration', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=Configuration&parent=Settings', '13', '0', '0'],
				['LBL_INTEGRATION', 'LBL_WEBSERVICE_APPS', 'adminIcon-webservice-apps', NULL, 'index.php?module=WebserviceApps&view=Index&parent=Settings', '11', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_OWNER_ALLOCATION', 'adminIcon-owner', 'LBL_OWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=owner', '9', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_MULTIOWNER_ALLOCATION', 'adminIcon-shared-owner', 'LBL_MULTIOWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=sharedOwner', '10', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_AUTOMATIC_ASSIGNMENT', 'adminIcon-automatic-assignment', 'LBL_AUTOMATICASSIGNMENT_DESCRIPTION', 'index.php?module=AutomaticAssignment&view=List&parent=Settings', '11', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_EMAILS_TO_SEND', 'adminIcon-mail-queue', 'LBL_EMAILS_TO_SEND_DESCRIPTION', 'index.php?module=Mail&parent=Settings&view=List', '22', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_MAIL_SMTP', 'adminIcon-mail-configuration', 'LBL_MAILSMTP_TO_SEND_DESCRIPTION', 'index.php?module=MailSmtp&parent=Settings&view=List', '23', '0', '0'],
				['LBL_INTEGRATION', 'LBL_WEBSERVICE_USERS', 'adminIcon-webservice-users', 'LBL_WEBSERVICE_USERS_DESCRIPTION', 'index.php?module=WebserviceUsers&view=List&parent=Settings', '11', '0', '0']
		];
		$blocks = [];
		foreach ($menu as $row) {
			if (!array_key_exists($row[0], $blocks)) {
				$blockInstance = Settings_Vtiger_Menu_Model::getInstance($row[0]);
				$blocks[$row[0]] = $blockInstance;
			}
			$isExists = (new \App\Db\Query())->from('vtiger_settings_field')->where(['name' => $row[1]])->exists();
			if ($isExists && !empty($blocks[$row[0]])) {
				$db->createCommand()->update('vtiger_settings_field', ['blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => (int) $row[5], 'active' => (int) $row[6], 'pinned' => (int) $row[7]], ['name' => $row[1]])->execute();
			} elseif (!empty($blocks[$row[0]])) {
				$db->createCommand()->insert('vtiger_settings_field', ['blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => (int) $row[5], 'active' => (int) $row[6], 'pinned' => (int) $row[7]])->execute();
			}
		}
	}

	private function getCronData($index)
	{
		$crons = [];
		switch ($index) {
			case 1:
				$crons = [
						['type' => 'add', 'data' => ['LBL_NEVER_ENDING_RECURRING_EVENTS', 'modules/Events/cron/RecurringEvents.php', 86400, NULL, NULL, 1, 'Events', 26, NULL]]
				];
				break;
			default:
				break;
		}
		return $crons;
	}

	private function cron($crons = [])
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = App\Db::getInstance();
		if ($crons) {
			foreach ($crons as $cron) {
				if (empty($cron)) {
					continue;
				}
				$cronData = $cron['data'];
				$isExists = (new \App\Db\Query())->from('vtiger_cron_task')->where(['name' => $cronData[0], 'handler_file' => $cronData[1]])->exists();
				if (!$isExists && $cron['type'] === 'add') {
					\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
					$this->cronAction[] = $cronData[0];
				} elseif ($isExists && $cron['type'] === 'remove') {
					\vtlib\Cron::deregister($cronData[0]);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function getTrees($index)
	{
		$trees = [];
		switch ($index) {
			case 1:
				$trees = [
						[
						'column' => 'pscategory',
						'base' => ['17', 'Category', \vtlib\Functions::getModuleId('FInvoice'), 1],
						'data' => [['17', 'none', 'T1', 'T1', 0, 'none', '', '']]
					],
						[
						'column' => 'pscategory',
						'base' => ['17', 'Category', \vtlib\Functions::getModuleId('CFixedAssets'), 1],
						'data' => [['17', 'none', 'T1', 'T1', 0, 'none', '', '']]
					],
						[
						'column' => 'pscategory',
						'base' => ['17', 'Category', \vtlib\Functions::getModuleId('FInvoiceCost'), 1],
						'data' => [['17', 'none', 'T1', 'T1', 0, 'none', '', '']]
					],
						[
						'column' => 'pscategory',
						'base' => ['17', 'Category', \vtlib\Functions::getModuleId('SVendorEnquiries'), 0],
						'data' => [['17', 'LBL_NONE', 'T1', 'T1', 0, 'LBL_NONE', '', '']]
					]
				];
				break;
			default:
				break;
		}
		return $trees;
	}

	private function setTrees($trees)
	{
		$db = App\Db::getInstance();
		foreach ($trees as $tree) {
			$skipCheckData = false;
			$templateId = (new \App\Db\Query())->select(['templateid'])->from('vtiger_trees_templates')->where(['module' => $tree['base'][2]])->scalar();
			if (!$templateId) {
				$db->createCommand()->insert('vtiger_trees_templates', [
					'name' => $tree['base'][1],
					'module' => $tree['base'][2],
					'access' => $tree['base'][3]
				])->execute();
				$templateId = $db->getLastInsertID('vtiger_trees_templates_templateid_seq');
				$db->createCommand()->update('vtiger_field', ['fieldparams' => $templateId], ['tabid' => $tree['base'][2], 'columnname' => $tree['column']])->execute();
				$skipCheckData = true;
			}
			foreach ($tree['data'] as $data) {
				if (!$skipCheckData) {
					$isExists = (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $templateId, 'name' => $data[1]])->exists();
					if ($isExists) {
						continue;
					}
				}
				$db->createCommand()->insert('vtiger_trees_templates_data', [
					'templateid' => $templateId,
					'name' => $data[1],
					'tree' => $data[2],
					'parenttrre' => $data[3],
					'depth' => $data[4],
					'label' => $data[5],
					'state' => $data[6],
					'icon' => $data[7]
				])->execute();
			}
		}
	}

	function addBlocks()
	{
		$blocks = [
				['398', 'FInvoice', 'LBL_DESCRIPTION_BLOCK', '5', '0', '0', '0', '0', '0', '1', '1',],
				['399', 'FInvoice', 'LBL_ATTENTION_BLOCK', '6', '0', '0', '0', '0', '0', '1', '1']
		];
		$keys = ['blockid', 'tabid', 'blocklabel', 'sequence', 'show_title', 'visible', 'create_view', 'edit_view', 'detail_view', 'display_status', 'iscustom'];
		foreach ($blocks as $block) {
			$block = array_combine($keys, $block);
			$query = (new \App\Db\Query())->from('vtiger_blocks')->where(['blocklabel' => $block['blocklabel'], 'tabid' => \vtlib\Functions::getModuleId($block['tabid'])]);
			if ($query->exists()) {
				continue;
			}
			try {
				$moduleInstance = \vtlib\Module::getInstance($block['tabid']);
				$blockInstance = new \vtlib\Block();
				$blockInstance->label = $block['blocklabel'];
				$blockInstance->sequence = $block['sequence'];
				$blockInstance->showtitle = $block['show_title'];
				$blockInstance->visible = $block['visible'];
				$blockInstance->increateview = $block['create_view'];
				$blockInstance->ineditview = $block['edit_view'];
				$blockInstance->indetailview = $block['detail_view'];
				$blockInstance->display_status = $block['display_status'];
				$blockInstance->iscustom = $block['iscustom'];
				$moduleInstance->addBlock($blockInstance);
			} catch (Exception $e) {
				\App\Log::error('ERROR' . __METHOD__ . ': code ' . $e->getCode() . " message " . $e->getMessage());
			}
		}
	}

	private function getConfigurations()
	{
		return [
				['name' => 'config/config.inc.php', 'conditions' => [
						['type' => 'remove', 'search' => 'Logo is visible in footer'],
						['type' => 'remove', 'search' => 'isVisibleLogoInFooter'],
				],
			],
				['name' => 'config/modules/ModTracker.php', 'conditions' => [
						['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_TIMELINE_IN_LISTVIEW', 'addingType' => 'before', 'value' => "	// Show timeline in listview [module name, ...]
	'SHOW_TIMELINE_IN_LISTVIEW' => [],
	// Limit of records displayed in timeline popup
	'TIMELINE_IN_LISTVIEW_LIMIT' => 5,
"],
				]
			],
				['name' => 'config/modules/OSSMail.php', 'conditions' => [
						['type' => 'update', 'search' => "config['plugins']", 'checkInLine' => 'authres_status', 'replace' => [');', ", 'authres_status');"]],
						['type' => 'update', 'search' => "config['mail_pagesize']", 'checkInLine' => '25', 'replace' => ['25', "30"]],
						['type' => 'update', 'search' => "config['session_lifetime']", 'checkInLine' => '10', 'replace' => ['10', "30"]],
						['type' => 'add', 'search' => "config['debug_level']", 'checkInContents' => 'reply_mode', 'addingType' => 'before', 'value' => "\$config['reply_mode'] = 1;
// Debug
"],
						['type' => 'add', 'search' => "config['skin'", 'checkInContents' => "config['list_cols", 'addingType' => 'after', 'value' => "\$config['list_cols'] = array('flag', 'status', 'subject', 'fromto', 'date', 'size', 'attachment', 'authres_status', 'threads');
"],
						['type' => 'add', 'search' => "config['skin'", 'checkInContents' => "enable_authres_status_column", 'addingType' => 'after', 'value' => "// plugin authres_status
\$config['enable_authres_status_column'] = true;
"],
						['type' => 'add', 'search' => "config['skin'", 'checkInContents' => "show_statuses", 'addingType' => 'after', 'value' => "\$config['show_statuses'] = 127;
"],
				]
			],
				['name' => 'config/search.php', 'conditions' => [
						['type' => 'add', 'search' => "];", 'checkInContents' => "GLOBAL_SEARCH_OPERATOR", 'addingType' => 'before', 'value' => "	// Global search - Show operator
	'GLOBAL_SEARCH_OPERATOR' => true,
"],
				]
			],
				['name' => 'config/api.php', 'conditions' => [
						['type' => 'remove', 'search' => 'yetiportal'],
						['type' => 'remove', 'search' => 'mobile'],
				]
			],
				['name' => '.htaccess', 'conditions' => [
						['type' => 'update', 'search' => "RewriteRule ^api/webservice/(.*)$ api/webservice.php?module=$1 [QSA,NC,L]", 'replace' => ["?module=", "?action="]],
						['type' => 'add', 'search' => "IfModule mod_php5.c", 'checkInContents' => 'error_reporting', 'addingType' => 'after', 'value' => "	#php_flag	error_reporting				337
"],
						['type' => 'add', 'search' => "IfModule mod_php5.c", 'checkInContents' => 'Error + Warning', 'addingType' => 'after', 'value' => "	#Error: 337 ,Error + Warning: 5111
"],
						['type' => 'add', 'search' => "file_uploads", 'checkInContents' => 'post_max_size', 'addingType' => 'after', 'value' => "	php_value   post_max_size				50M
"],
						['type' => 'add', 'search' => "file_uploads", 'checkInContents' => 'upload_max_filesize', 'addingType' => 'after', 'value' => "	php_value   upload_max_filesize			100M
"],
						['type' => 'remove', 'search' => 'magic_quotes_gpc'],
						['type' => 'remove', 'search' => 'magic_quotes_runtime'],
						['type' => 'remove', 'search' => 'IfModule fcgid_module'],
						['type' => 'remove', 'search' => 'FcgidIOTimeout'],
						['type' => 'remove', 'search' => 'FcgidConnectTimeout'],
						['type' => 'remove', 'search' => 'FcgidBusyTimeout'],
						['type' => 'remove', 'search' => 'FcgidIdleTimeout'],
						['type' => 'remove', 'search' => '</IfModule>', 'before' => 'FcgidIdleTimeout'],
						['type' => 'remove', 'search' => 'IfModule mod_fcgid'],
						['type' => 'remove', 'search' => 'IdleTimeout'],
						['type' => 'remove', 'search' => 'ProcessLifeTime'],
						['type' => 'remove', 'search' => 'IPCConnectTimeout'],
						['type' => 'remove', 'search' => 'IPCCommTimeout'],
						['type' => 'remove', 'search' => 'BusyTimeout'],
						['type' => 'remove', 'search' => '</IfModule>', 'before' => 'BusyTimeout'],
				]],
		];
	}

	private function updateConfigurationFiles()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = $configContentClone = file($fileName);
				$emptyLine = false;
				$addContent = [];
				$indexes = [];
				foreach ($configContent as $key => $line) {
					if ($emptyLine && strlen($line) == 1) {
						unset($configContent[$key]);
						$emptyLine = false;
						continue;
					}
					$emptyLine = false;
					foreach ($conditions as $index => $condition) {
						if (empty($condition)) {
							continue;
						}
						if ($condition['type'] === 'add' && !in_array($index, $indexes)) {
							$addContent[$index] = $condition['value'];
							$indexes[] = $index;
						}
						if (strpos($line, $condition['search']) !== false) {
							switch ($condition['type']) {
								case 'add':
									if ($condition['checkInContents'] && strpos($baseContent, $condition['checkInContents']) === false) {
										if ($condition['trim']) {
											$configContent[$key] = $this->getTrimValue($condition['trim'], $configContent[$key]);
										}
										$configContent[$key] = $condition['addingType'] === 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									if (!empty($condition['before'])) {
										if (strpos($configContentClone[$key - 1], $condition['before']) !== false) {
											unset($configContent[$key]);
											$emptyLine = true;
										}
									} else {
										unset($configContent[$key]);
										$emptyLine = true;
									}
									break;
								case 'update':
									if ($condition['checkInLine'] && (strpos($condition['checkInLine'], $configContent[$key]) !== false)) {
										break;
									}
									if ($condition['replace']) {
										$configContent[$key] = str_replace($condition['replace'][0], $condition['replace'][1], $configContent[$key]);
									} else {
										$configContent[$key] = $condition['value'];
									}
									break;
								default:
									break;
							}
						}
					}
				}
				$content = implode("", $configContent);
				if ($addContent) {
					$addContentString = implode("", $addContent);
					$content .= $addContentString;
				}
				$file = fopen($fileName, "w+");
				fwrite($file, $content);
				fclose($file);
			} else {
				$dirName = 'cache/updates/' . $config['name'];
				$sourceFile = $rootDirectory . $dirName;
				if (file_exists($sourceFile)) {
					copy($sourceFile, $fileName);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	public function postupdate()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$dirName = 'cache/updates';
		$adb = \PearDatabase::getInstance();
		$adb->query('SET FOREIGN_KEY_CHECKS = 1;');
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(true)->execute();

		foreach ($this->filesToDelete as $path) {
			\vtlib\Functions::recurseDelete($path);
		}
		\vtlib\Functions::recurseCopy($dirName . '/files', '', true);

		$db->createCommand()->insert('yetiforce_updates', ['user' => Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => 'Update package',
			'from_version' => $this->from_version,
			'to_version' => $this->to_version,
			'result' => 1])->execute();
		$db->createCommand()->update('vtiger_version', ['current_version' => $this->to_version])->execute();
		$menuRecordModel = new Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		\vtlib\Deprecated::createModuleMetaFile();
		\vtlib\Access::syncSharingAccess();
		foreach ($this->cronAction as $cronName) {
			$cron = \vtlib\Cron::getInstance($cronName);
			if (!empty($cron)) {
				$cron->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
			}
		}
		\vtlib\Functions::recurseDelete($dirName);
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\App\Log::trace('Exiting ' . __METHOD__);
		header('Location: ' . AppConfig::main('site_URL'));
	}

	function getWidgetToSummary($moduleName)
	{
		$widgets = [];
		switch ($moduleName) {
			case 'CFixedAssets':
				$widgets = [
						['164', 'CFixedAssets', 'Summary', NULL, '1', '0', '0', '[]'],
						['165', 'CFixedAssets', 'Comments', '', '2', '2', '0', '{"relatedmodule":"ModComments","limit":"5"}'],
						['166', 'CFixedAssets', 'RelatedModule', '', '1', '1', '0', '{"relatedmodule":"8","limit":"5","columns":"1","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}'],
				];
				break;
			case 'CInternalTickets':
				$widgets = [
						['167', 'CInternalTickets', 'Summary', NULL, '1', '0', '0', '[]'],
						['168', 'CInternalTickets', 'RelatedModule', '', '1', '1', '0', '{"relatedmodule":"8","limit":"5","columns":"1","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}'],
						['169', 'CInternalTickets', 'Comments', '', '2', '2', '0', '{"relatedmodule":"ModComments","limit":"5"}'],
				];
				break;
			case 'FInvoiceCost':
				$widgets = [
						['170', 'FInvoiceCost', 'Summary', NULL, '1', '0', '0', '[]'],
						['171', 'FInvoiceCost', 'Comments', '', '2', '2', '0', '{"relatedmodule":"ModComments","limit":"5"}'],
						['172', 'FInvoiceCost', 'RelatedModule', '', '1', '1', '0', '{"relatedmodule":"8","limit":"5","columns":"1","action":"1","switchHeader":"-","filter":"-","checkbox":"-"}'],
				];
				break;
			case 'SVendorEnquiries':
				$widgets = [
						['173', 'SVendorEnquiries', 'Activities', '', '2', '2', '0', '{"limit":"5"}'],
						['174', 'SVendorEnquiries', 'EmailList', 'Emails', '2', '3', '0', '{"limit":"5"}'],
						['175', 'SVendorEnquiries', 'Comments', '', '1', '1', '0', '{"relatedmodule":"ModComments","limit":"5"}'],
						['176', 'SVendorEnquiries', 'Summary', NULL, '1', '0', '0', '[]']
				];
				break;
			default:
				break;
		}
		return $widgets;
	}

	public function setWidgetToSummary($widgets)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = PearDatabase::getInstance();
		foreach ($widgets as $widget) {
			if (empty($widget)) {
				continue;
			}
			list($ID, $moduleName, $type, $label, $wcol, $sequence, $nomargin, $data) = $widget;
			$tabid = \vtlib\Functions::getModuleId($moduleName);
			if ($type == 'RelatedModule') {
				$arrayData = \App\Json::decode($data);
				$ralModule = $arrayData['relatedmodule'];
				$result = $db->pquery('SELECT 1 FROM vtiger_widgets WHERE tabid = ? AND type = ? AND `data` LIKE ?;', [$tabid, $type, '%"relatedmodule":"' . $ralModule . '"%']);
			} else {
				$result = $db->pquery('SELECT 1 FROM vtiger_widgets WHERE tabid = ? AND type = ?;', [$tabid, $type]);
			}
			if (!$db->getRowCount($result)) {
				$db->insert('vtiger_widgets', [
					'tabid' => $tabid,
					'type' => $type,
					'label' => $label,
					'wcol' => $wcol,
					'sequence' => $sequence,
					'nomargin' => $nomargin,
					'data' => $data
				]);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}
}
