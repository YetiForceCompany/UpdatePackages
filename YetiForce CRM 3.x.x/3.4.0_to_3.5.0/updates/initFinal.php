<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
chdir(__DIR__ . '/../../');
include_once 'include/main/WebUI.php';
\vtlib\Functions::recurseDelete('cache/updates');
\vtlib\Functions::recurseDelete('cache/templates_c');
//$menuRecordModel = new Settings_Menu_Record_Model();
//$menuRecordModel->refreshMenuFiles();
\vtlib\Deprecated::createModuleMetaFile();
\vtlib\Access::syncSharingAccess();
exit(header('Location: ' . AppConfig::main('site_URL')));
