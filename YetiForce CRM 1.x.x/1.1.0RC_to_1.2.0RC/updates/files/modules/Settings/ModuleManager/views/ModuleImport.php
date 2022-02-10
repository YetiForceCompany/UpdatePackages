<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_ModuleImport_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('importUserModuleStep1');
		$this->exposeMethod('importUserModuleStep2');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}


    /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.ModuleImport"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function importUserModuleStep1(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ImportUserModuleStep1.tpl', $qualifiedModuleName);
	}
	
	public function importUserModuleStep2(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$qualifiedModuleName = $request->getModule(false);

		$uploadFile = 'usermodule_'. time() . '.zip';
		$uploadFileName = "$uploadDir/$uploadFile";
		$error = '';
		checkFileAccess($uploadDir);
		if(!move_uploaded_file($_FILES['moduleZip']['tmp_name'], $uploadFileName)) {
			$error = 'LBL_ERROR_MOVE_UPLOADED_FILE';
		}else{
			$package = new Vtiger_Package();
			$importModuleName = $package->getModuleNameFromZip($uploadFileName);
			$importModuleDepVtVersion = $package->getDependentVtigerVersion();

			if($importModuleName == null ) {
				$error = $package->_errorText;
				checkFileAccessForDeletion($uploadFileName);
				unlink($uploadFileName);
			} else {
				// We need these information to push for Update if module is detected to be present.
				$moduleLicence = vtlib_purify($package->getLicense());
				
				$viewer->assign("MODULEIMPORT_FILE", $uploadFile);
				$viewer->assign("MODULEIMPORT_TYPE", $package->type());
				$viewer->assign("MODULEIMPORT_NAME", $importModuleName);
				$viewer->assign("MODULEIMPORT_DEP_VTVERSION", $importModuleDepVtVersion);
				$viewer->assign("MODULEIMPORT_LICENSE", $moduleLicence);
				
				if(!$package->isLanguageType() && !$package->isUpdateType() && !$package->isModuleBundle()) {
					$moduleInstance = Vtiger_Module::getInstance($importModuleName);
					$moduleimport_exists = ($moduleInstance)? "true" : "false";
					$moduleimport_dir_name = "modules/$importModuleName";
					$moduleimport_dir_exists = (is_dir($moduleimport_dir_name)? "true" : "false");
					$viewer->assign("MODULEIMPORT_EXISTS", $moduleimport_exists);
					$viewer->assign("MODULEIMPORT_DIR", $moduleimport_dir_name);
					$viewer->assign("MODULEIMPORT_DIR_EXISTS", $moduleimport_dir_exists);
				}
			}
		}
		$viewer->assign('MODULEIMPORT_ERROR', $error);
		$viewer->view('ImportUserModuleStep2.tpl', $qualifiedModuleName);
	}
	
	public function importUserModuleStep3(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);
		
		$importType = $request->get('module_import_type');
		if(strtolower($importType) == 'language') {
			$package = new Vtiger_Language();
		} else {
			$package = new Vtiger_Package();
		}

		$package->import($uploadFileName);
		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);
		
		$viewer->assign("IMPORT_MODULE_NAME", $importModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ImportUserModuleStep3.tpl', $qualifiedModuleName);
	}
	
	public function updateUserModuleStep3(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Module_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);
		
		$importType = $request->get('module_import_type');
		if(strtolower($importType) == 'language') {
			$package = new Vtiger_Language();
		} else {
			$package = new Vtiger_Package();
		}

		if (strtolower($importType) == 'language') {
			$package->import($uploadFileName);
		} else {
			$package->update(Vtiger_Module::getInstance($importModuleName), $uploadFileName);
		}
		
		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);
		
		$viewer->assign("UPDATE_MODULE_NAME", $importModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('UpdateUserModuleStep3.tpl', $qualifiedModuleName);
	}
	public function validateRequest(Vtiger_Request $request) { 
		$request->validateReadAccess(); 
	}
}
