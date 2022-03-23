<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSMail_Record_Model extends Vtiger_Record_Model {
	function getAccountsList($user = false, $onlyMy = false, $password = false) {
		$adb = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$param = array();
		$sql = "SELECT * FROM roundcube_users";
		if( $password ){
			$where .= " AND password <> ''";
		}
		if( $user ){
			$where .= " AND user_id = ?";
			$param[] = $user;
		}
		if( $onlyMy ){
			$where .= " AND crm_user_id = ?";
			$param[] = $currentUserModel->getId();
		}	
		if( $where ){
			$sql .= ' WHERE'.substr($where, 4);
		}		
		$result = $adb->pquery( $sql, $param, true);
		$Num = $adb->num_rows($result);
		if($Num == 0){
			return false;
		}else{
			return $result->GetArray();
		}
	}
	function ComposeEmail($params,$ModuleName) {
		$_SESSION['POST'] = $params;
		header('Location: '.self::GetSite_URL().'index.php?module=OSSMail&view=compose');
	}
	public static function getConfig($conf_type) {
		$adb = PearDatabase::getInstance();
        $queryParams = array();
		if($conf_type != '' || $conf_type != false){
			$sql = "WHERE conf_type = ?";
            $queryParams[] = $conf_type;
		}
		$result = $adb->pquery( "SELECT * FROM vtiger_ossmailscanner_config $sql ORDER BY parameter DESC" ,$queryParams, true);
		foreach($result->GetArray() as $row){
			if($conf_type != '' || $conf_type != false){
				$return[$row['parameter']] = $row['value'];
			}else{
				$return[$row['conf_type']][$row['parameter']] = $row['value'];
			}
		}
		return $return;
	}
	public static function load_roundcube_config() {
		global $no_include_config;
		$no_include_config = true;
		include 'modules/OSSMail/roundcube/config/config.inc.php';
		return $config;
	}
	public static function imap_connect($user , $password , $folder = 'INBOX') {
		global $log;
		$log->debug("Entering OSSMail_Record_Model::imap_connect($user , $password , $folder) method ...");
		$roundcube_config = self::load_roundcube_config();
		$a_host = parse_url($roundcube_config['default_host']);
		$validatecert = '';
		if($a_host['host']){
			$host = $a_host['host'];
			$ssl_mode = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? $a_host['scheme'] : null;
			if (!empty($a_host['port'])){
				$port = $a_host['port'];
			}else if ($ssl_mode && $ssl_mode != 'tls' && (!$roundcube_config['default_port'] || $roundcube_config['default_port'] == 143)){
				$port = 993;
			}
		}else{
			$host = $roundcube_config['default_host'];
			if($roundcube_config['default_port'] == 993){
				$ssl_mode = 'ssl';
			}else{
				$ssl_mode = 'tls';
			}
			$port = $roundcube_config['default_port'];
		}
		if (!$port) { $port = $roundcube_config['default_port'];}
		if (!$roundcube_config['validate_cert']) { $validatecert = '/novalidate-cert';}
		imap_timeout(IMAP_OPENTIMEOUT,5);
			$log->debug("imap_open({".$host.":".$port."/imap/".$ssl_mode.$validatecert."}$folder, $user , $password) method ...");
			$mbox = @imap_open("{".$host.":".$port."/imap/".$ssl_mode.$validatecert."}$folder", $user , $password) OR
			die( self::imap_open_error(imap_last_error()) );
		$log->debug("Exit OSSMail_Record_Model::imap_connect() method ...");
		return $mbox;
	}
	public static function imap_open_error($error) {
		global $log;
		$log->debug("Error OSSMail_Record_Model::imap_connect(): ".$error); 
		self::createdAlert(vtranslate('IMAP_ERROR', 'OSSMailScanner').': '.$error );
	}
	public static function getMailBoxmsgInfo($userid = false) {
		if($userid){
			$Accounts = self::get_account_detail($userid);
		}else{
			$Accounts = self::get_active_email_account();
		}
		if(!$Accounts){return false;}
		$mbox = self::imap_connect($Accounts[0]['username'] , $Accounts[0]['password']);
		$mailboxmsginfo = imap_mailboxmsginfo($mbox);
		return $mailboxmsginfo;
	}
	public static function get_mail_detail($mbox,$id,$msgno = false) {
		$return = array();
		if(!$msgno){
			$msgno = imap_msgno($mbox,$id);
		}
		if(!$id){
			$id = imap_uid($mbox,$msgno);
		}
		if(!$msgno){
			return false;
		}
		$header = imap_header($mbox,  $msgno);
		$structure = self::_get_body_attach($mbox, $id,$msgno);
		$return['id'] = $id;
		$return['Msgno'] = $header->Msgno;
		$return['message_id'] = $header->message_id;
		$return['toaddress'] = self::get_only_email($header->to);
		$return['fromaddress'] = self::get_only_email($header->from);
		$return['reply_toaddress'] = self::get_only_email($header->reply_to);
		$return['ccaddress'] = self::get_only_email($header->cc);
		$return['bccaddress'] = self::get_only_email($header->bcc);
		$return['senderaddress'] = self::get_only_email($header->sender);
		$return['subject'] = self::_decode_text($header->subject);
		$return['MailDate'] = $header->MailDate;
		$return['date'] = $header->date;
		$return['udate'] = $header->udate;
		$return['udate_formated'] = date("Y-m-d H:i:s", $header->udate );
		$return['Recent'] = $header->Recent;
		$return['Unseen'] = $header->Unseen;
		$return['Flagged'] = $header->Flagged;
		$return['Answered'] = $header->Answered;
		$return['Deleted'] = $header->Deleted;
		$return['Draft'] = $header->Draft;
		$return['Size'] = $header->Size;
		$return['body'] = $structure['body'];
		$return['attachments'] = $structure['attachment'];
        $return['clean'] = '';
		
                $msgs = imap_fetch_overview($mbox, $msgno);
                foreach ($msgs as $msg) {
                    $return['clean'] .= imap_fetchheader($mbox, $msg->msgno);
                }
		
                
		return $return;
	}
	public static function get_active_email_account() {
		return self::get_account_detail(Users_Record_Model::getCurrentUserModel()->get('id'));
	}
	public static function get_account_detail($userid) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery( "SELECT * FROM roundcube_users where crm_user_id = ?", array($userid) ,true);
		$Num = $adb->num_rows($result);
		if($Num > 0){
			return $result->GetArray();
		}else{
			return false;
		}
		
	}
	public static function get_account_detail_by_name($name) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery( "SELECT * FROM roundcube_users where username = ? ", array($name) ,true);
		return $result->GetArray();
	}
	public static function _decode_text($text) {
		$data = imap_mime_header_decode($text);
		$charset = ($data[0]->charset == 'default') ? 'ASCII' : $data[0]->charset;
		return iconv($charset, "UTF-8", $data[0]->text);
	}
	public static function get_full_name($text) {
		$return ='';
		foreach($text as $row){
			if($return !=''){
				$return.= ',';
			}
			if( $row->personal == ''){
				$return.= $row->mailbox.'@'.$row->host;
			}else{
				$return.= self::_decode_text($row->personal).' - '. $row->mailbox.'@'.$row->host;
			}
			
		}
		return $return;
	}
	public static function get_only_email($text) {
		$return ='';
		if(is_array($text)){
			foreach($text as $row){
				if($return !=''){
					$return.= ',';
				}
				$return.= $row->mailbox.'@'.$row->host;

			}
		}
		return $return;
	}
	public function _get_body_attach($mbox, $id,$msgno) {
		$struct = imap_fetchstructure($mbox, $id, FT_UID);
		$parts = $struct->parts;
		$i = 0;
		$mail = array('id' => $id);
		if(empty($struct->parts)) {
			$mail = self::initMailPart($mbox,$mail, $struct, 0);
		}else {
			foreach($struct->parts as $partNum => $partStructure) {
				$mail = self::initMailPart($mbox,$mail, $partStructure, $partNum + 1);
			}
		}
		$ret = array();
		$ret['body'] = $mail['textHtml'] ? $mail['textHtml']: $mail['textPlain'];
		$ret['attachment'] = $mail["attachments"];
		return $ret;
	}
	protected function initMailPart($mbox,$mail, $partStructure, $partNum) {
		$data = $partNum ? imap_fetchbody($mbox, $mail['id'], $partNum, FT_UID | FT_PEEK ) : imap_body($mbox, $mail['id'], FT_UID | FT_PEEK);
		if($partStructure->encoding == 1) {
			$data = imap_utf8($data);
		}
		elseif($partStructure->encoding == 2) {
			$data = imap_binary($data);
		}
		elseif($partStructure->encoding == 3) {
			$data = imap_base64($data);
		}
		elseif($partStructure->encoding == 4) {
			$data = imap_qprint($data);
		}
		$params = array();
		if(!empty($partStructure->parameters)) {
			foreach($partStructure->parameters as $param) {
				$params[strtolower($param->attribute)] = $param->value;
			}
		}
		if(!empty($partStructure->dparameters)) {
			foreach($partStructure->dparameters as $param) {
				$paramName = strtolower(preg_match('~^(.*?)\*~', $param->attribute, $matches) ? $matches[1] : $param->attribute);
				if(isset($params[$paramName])) {
					$params[$paramName] .= $param->value;
				}
				else {
					$params[$paramName] = $param->value;
				}
			}
		}
		if(!empty($params['charset'])) {
			$data = iconv(strtoupper($params['charset']), 'utf-8', $data);
		}
		$attachmentId = $partStructure->ifid
			? trim($partStructure->id, " <>")
			: (isset($params['filename']) || isset($params['name']) ? mt_rand() . mt_rand() : null);
		if($attachmentId) {
			if(empty($params['filename']) && empty($params['name'])) {
				$fileName = $attachmentId . '.' . strtolower($partStructure->subtype);
			}
			else {
				$fileName = !empty($params['filename']) ? $params['filename'] : $params['name'];
				$fileName = self::decodeMimeStr($fileName);
				$fileName = self::decodeRFC2231($fileName);
			}
			$mail['attachments'][$attachmentId]['filename'] = $fileName;
			$mail['attachments'][$attachmentId]['attachment'] = $data;
		}elseif($partStructure->type == 0 && $data) {
			if(strtolower($partStructure->subtype) == 'plain') {
				$mail['textPlain'] .= $data;
			} else {
				$mail['textHtml'] .= $data;
			}
		}elseif($partStructure->type == 2 && $data) {
			$mail['textPlain'] .= trim($data);
		}
		if(!empty($partStructure->parts)) {
			foreach($partStructure->parts as $subPartNum => $subPartStructure) {
				if($partStructure->type == 2 && $partStructure->subtype == 'RFC822') {
					$mail = self::initMailPart($mbox,$mail, $subPartStructure, $partNum);
				} else {
					$mail = self::initMailPart($mbox,$mail, $subPartStructure, $partNum . '.' . ($subPartNum + 1));
				}
			}
		}
		return $mail;
	}
	function decodeMimeStr($string, $charset = 'utf-8') {
		$newString = '';
		$elements = imap_mime_header_decode($string);
		for($i = 0; $i < count($elements); $i++) {
			if($elements[$i]->charset == 'default') {
				$elements[$i]->charset = 'iso-8859-1';
			}
			$newString .= iconv(strtoupper($elements[$i]->charset), $charset, $elements[$i]->text);
		}
		return $newString;
	}
	function isUrlEncoded($string) {
		$string = str_replace('%20', '+', $string);
		$decoded = urldecode($string);
		return $decoded != $string && urlencode($decoded) == $string;
	}

	protected function decodeRFC2231($string, $charset = 'utf-8') {
		if(preg_match("/^(.*?)'.*?'(.*?)$/", $string, $matches)) {
			$encoding = $matches[1];
			$data = $matches[2];
			if(self::isUrlEncoded($data)) {
				$string = iconv(strtoupper($encoding), $charset, urldecode($data));
			}
		}
		return $string;
	}
	function _SaveAttachements($attachments, $userid , $usetime, $relID = false) {
		$adb = PearDatabase::getInstance();
		$setype = "OSSMailView Attachment";
		$IDs = Array();
		if($attachments){
			foreach($attachments as $attachment) {
				$filename = $attachment['filename'];
				$filecontent = $attachment['attachment'];
				$attachid = $adb->getUniqueId('vtiger_crmentity');
				$description = $filename;
				$adb->pquery("INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid, 
					modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
					Array($attachid, $userid, $userid, $userid, $setype, $description, $usetime, $usetime, 1, 0));
				$issaved = self::_SaveAttachmentFile($attachid, $filename, $filecontent);
				if($issaved) {
					$document = Vtiger_Module::getInstance('Documents'); //new Documents();
					$document->column_fields['notes_title']      = $filename;
					$document->column_fields['filename']         = $filename;
					$document->column_fields['filestatus']       = 1;
					$document->column_fields['filelocationtype'] = 'I';
					$document->column_fields['folderid']         = 1; // Default Folder 
					$document->column_fields['assigned_user_id'] = $userid;
					$document->save('Documents');
					$IDs[] = $document->id;
					$adb->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)", Array($document->id, $attachid));
					$adb->pquery("UPDATE vtiger_crmentity SET createdtime = ?,smcreatorid = ?,modifiedby = ?  WHERE crmid = ? ", array($usetime,$userid,$userid,$document->id) );
					if($relID && $relID != 0 && $relID != ''){
						$dirname = Vtiger_Functions::initStorageFileDirectory('OSSMailView');
						$url_to_image = $dirname.$attachid.'_'.$filename;
						$adb->pquery("INSERT INTO vtiger_ossmailview_files(ossmailviewid, documentsid, attachmentsid) VALUES(?,?,?)", Array($relID,$document->id, $attachid));
						$db_content = $adb->pquery( "SELECT content FROM vtiger_ossmailview where ossmailviewid = ?", array($relID) ,true);
						$content = $adb->raw_query_result_rowdata($db_content, 0);
						$content = $content['content'];
						preg_match_all('/src="cid:(.*)"/Uims', $content, $matches);
						if(count($matches)) {
							$search = array();
							$replace = array();
							foreach($matches[1] as $match) {
								if (strpos($filename, $match) !== false || strpos($match, $filename) !== false) {
									$search[] = "src=\"cid:$match\"";
									$replace[] = "src=\"$url_to_image\"";
								}
							}
							$content = str_replace($search, $replace, $content);
						}
						$adb->pquery("UPDATE vtiger_ossmailview SET content = ? WHERE ossmailviewid = ? ",array($content,$relID) );
					}
				}
			}
		}
		return $IDs;
	}
	function _SaveAttachmentFile($attachid, $filename, $filecontent) {
		require_once 'modules/OSSMail/MailAttachmentMIME.php';
		$adb = PearDatabase::getInstance();
		$dirname = Vtiger_Functions::initStorageFileDirectory('OSSMailView');
		if(!is_dir($dirname)) mkdir($dirname);
		$filename = str_replace(' ', '-', $filename);
		$filename = str_replace(':', '-', $filename);
		$filename = str_replace('/', '-', $filename);
		$saveasfile = "$dirname$attachid" . "_$filename";
		if(!file_exists($saveasfile)) {
			$fh = fopen($saveasfile, 'wb');
			fwrite($fh, $filecontent);
			fclose($fh);
		}
		$mimetype = MailAttachmentMIME::detect($saveasfile);
		$adb->pquery("INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?",
			Array($attachid, $filename, $description, $mimetype, $dirname));
		return true;
	}
	public static function get_default_mailboxes() {
		$Accounts = self::getAccountsList(false,false,true);
		$mailboxs = Array();
		if($Accounts){
			$mbox = self::imap_connect($Accounts[0]['username'] , $Accounts[0]['password']);
			$ref = "{".$Accounts[0]['mail_host']."}";
			$list = imap_list($mbox, $ref , "*");
			foreach($list as $mailboxname) {
				$name = str_replace($ref, '', $mailboxname);
				$mailboxs[$name] = self::convertCharacterEncoding($name, 'UTF-8', 'UTF7-IMAP');
			}
			return $mailboxs;
		}else{
			return false;
		}
	}
	function convertCharacterEncoding($value, $toCharset, $fromCharset) {
		if (function_exists('mb_convert_encoding')) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($toCharset, $fromCharset, $value);
		}
		return $value;
	}
	function findCrmDetail($params,$metod){
		$OSSMailViewModel = Vtiger_Record_Model::getCleanInstance('OSSMailView');
		$Array = $OSSMailViewModel->findCrmRecordsByMessage_id($params,$metod);
		if( count($Array['Potentials']) ){
			$crmid = $Array['Potentials']['record']['crmid'];$module = $Array['Potentials']['record']['module'];
			$PotentialsRecord_Model = Vtiger_Record_Model::getInstanceById($crmid, $module);
			$related_to = $PotentialsRecord_Model->get('related_to');
			$contact_id = $PotentialsRecord_Model->get('contact_id');
			if($related_to != 0 && $related_to != '')
			$Array['Potentials']['Accounts'] = array('crmid'=>$related_to,'label'=>Vtiger_Functions::getCRMRecordLabel($related_to));
			if($contact_id != 0 && $contact_id != '')
			$Array['Potentials']['Contacts'] = array('crmid'=>$contact_id,'label'=>Vtiger_Functions::getCRMRecordLabel($contact_id));
		}
		if( count($Array['Project']) ){
			$crmid = $Array['Project']['record']['crmid'];$module = $Array['Project']['record']['module'];
			$ProjectRecord_Model = Vtiger_Record_Model::getInstanceById($crmid, $module);
			$acc_cont = $ProjectRecord_Model->get('linktoaccountscontacts');
			if($acc_cont != 0 && $acc_cont != '')
			$Array['Project']['RelRecord'] = array('crmid'=>$acc_cont,'label'=>Vtiger_Functions::getCRMRecordLabel($acc_cont),'module'=>Vtiger_Functions::getCRMRecordType($acc_cont));
		}
		if( count($Array['HelpDesk']) ){
			$crmid = $Array['HelpDesk']['record']['crmid'];$module = $Array['HelpDesk']['record']['module'];
			$HelpDeskRecord_Model = Vtiger_Record_Model::getInstanceById($crmid, $module);
			$parent_id = $HelpDeskRecord_Model->get('parent_id');
			$contact_id = $HelpDeskRecord_Model->get('contact_id');
			if($parent_id != 0 && $parent_id != '')
			$Array['HelpDesk']['Accounts'] = array('crmid'=>$parent_id,'label'=>Vtiger_Functions::getCRMRecordLabel($parent_id));
			if($contact_id != 0 && $contact_id != '')
			$Array['HelpDesk']['Contacts'] = array('crmid'=>$contact_id,'label'=>Vtiger_Functions::getCRMRecordLabel($contact_id));
		}
		return $Array;
	}
	function get_message_id_uid($params) {
		$password = $this->get_user_password($params['username']);
		$mbox = $this->imap_connect($params['username'] , $password, $params['folder']);
		$msgno = imap_msgno($mbox,$params['uid']);
		$header = imap_header($mbox,  $msgno);
		$message_id = $header->message_id;
		return $header->message_id;
	}
	public static function get_user_password($username) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery( "SELECT password FROM roundcube_users where username = ?", array($username) ,true);
		return $adb->query_result($result, 0, 'password');
	}
	public static function addRelated($params) {
		$adb = PearDatabase::getInstance();
		$crmid = $params['crmid'];
		if($params['rel_new_mod']){
			$dest = $params['rel_new_mod'];
			$crmid = $params['rel_new_id'];
		}else{
			$dest = 'OSSMailView';
		}
		if($params['new_module'] == 'Products'){
			$adb->pquery("INSERT INTO vtiger_seproductsrel SET crmid=?, productid=?, setype=?",
			Array($crmid, $params['new_crmid'], $dest));
		}else{
			$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?",
			Array($crmid, $dest, $params['new_crmid'], $params['new_module']));
		}
		if($params['rcrmid']){
			$mailID = $params['crmid'];
			$id = $params['rcrmid'];
			$module = $params['rmodule'];
			$adb->pquery("DELETE FROM vtiger_crmentityrel WHERE (crmid = ? AND module = ? AND relcrmid = ?) OR ( crmid = ? AND relcrmid = ? AND relmodule = ?)", array($id, $module, $mailID, $mailID, $id, $module) ,true);
		}
		return vtranslate('Add relationship','OSSMail');
	}
	public static function removeRelated($params) {
		$adb = PearDatabase::getInstance();
		$mailID = $params['crmid'];
		$id = $params['rcrmid'];
		$module = $params['rmodule'];
		$adb->pquery("DELETE FROM vtiger_crmentityrel WHERE (crmid = ? AND module = ? AND relcrmid = ?) OR ( crmid = ? AND relcrmid = ? AND relmodule = ?)", array($id, $module, $mailID, $mailID, $id, $module) ,true);
		return vtranslate('Removed relationship','OSSMail');
	}
	public static function getViewableData() {
		global $no_include_config;
		$no_include_config = true;
		$return = array();
		include 'modules/OSSMail/roundcube/config/config.inc.php';
		foreach ($config as $key => $value){
			if($key == 'skin_logo'){
				$return[$key] = $value['*'];
			}else{
				$return[$key] = $value;
			}
		}
		return $return;
	}
	public static function setConfigData($param, $dbupdate = true) {
		$fileName = 'modules/OSSMail/roundcube/config/config.inc.php';
		$fileContent = file_get_contents($fileName);
		$Fields = self::getEditableFields();
		foreach ($param as $fieldName => $fieldValue) {
			$type = $Fields[$fieldName]['fieldType'];
			$pattern = '/(\$config\[\''.$fieldName.'\'\])[\s]+=([^;]+);/';
			if($type == 'checkbox' || $type == 'int'){
				$patternString = "\$config['%s'] = %s;";
			}elseif($fieldName == 'skin_logo'){
				$patternString = "\$config['%s'] = array(\"*\" => \"%s\");";
			}else{
				$patternString = "\$config['%s'] = '%s';";
			}
			$replacement = sprintf($patternString, $fieldName, $fieldValue);
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		}
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		if($dbupdate){
			$adb = PearDatabase::getInstance();
			$adb->pquery("update roundcube_users set language=?", array( $param['language'] ) );
		}
		return vtranslate('JS_save_config_info', 'OSSMailScanner');
	}
	function getEditableFields() {
	return array(
		'product_name'				=> array('label' => 'LBL_RC_product_name',			'fieldType' => 'text'),
		'validate_cert'				=> array('label' => 'LBL_RC_validate_cert',			'fieldType' => 'checkbox'),
		'default_host'				=> array('label' => 'LBL_RC_default_host',			'fieldType' => 'text'),
		'default_port'				=> array('label' => 'LBL_RC_default_port',			'fieldType' => 'int'),
		'smtp_server'				=> array('label' => 'LBL_RC_smtp_server',			'fieldType' => 'text'),
		'smtp_user'					=> array('label' => 'LBL_RC_smtp_user',				'fieldType' => 'text'),
		'smtp_pass'					=> array('label' => 'LBL_RC_smtp_pass',				'fieldType' => 'text'),
		'smtp_port'					=> array('label' => 'LBL_RC_smtp_port',				'fieldType' => 'int'),
		'language'					=> array('label' => 'LBL_RC_language',				'fieldType' => 'picklist', 'value' => array('ar_SA','az_AZ','be_BE','bg_BG','bn_BD','bs_BA','ca_ES','cs_CZ','cy_GB','da_DK','de_CH','de_DE','el_GR','en_CA','en_GB','en_US','es_419','es_AR','es_ES','et_EE','eu_ES','fa_AF','fa_IR','fi_FI','fr_FR','fy_NL','ga_IE','gl_ES','he_IL','hi_IN','hr_HR','hu_HU','hy_AM','id_ID','is_IS','it_IT','ja_JP','ka_GE','km_KH','ko_KR','lb_LU','lt_LT','lv_LV','mk_MK','ml_IN','mr_IN','ms_MY','nb_NO','ne_NP','nl_BE','nl_NL','nn_NO','pl_PL','pt_BR','pt_PT','ro_RO','ru_RU','si_LK','sk_SK','sl_SI','sq_AL','sr_CS','sv_SE','ta_IN','th_TH','tr_TR','uk_UA','ur_PK','vi_VN','zh_CN','zh_TW') ),
		'username_domain'			=> array('label' => 'LBL_RC_username_domain',		'fieldType' => 'text'),
		'debug_level'				=> array('label' => 'LBL_RC_debug_level',			'fieldType' => 'int'),
		'skin_logo'					=> array('label' => 'LBL_RC_skin_logo',				'fieldType' => 'text'),
		'ip_check'					=> array('label' => 'LBL_RC_ip_check',				'fieldType' => 'checkbox'),
		'enable_spellcheck'			=> array('label' => 'LBL_RC_enable_spellcheck',		'fieldType' => 'checkbox'),
		'identities_level'			=> array('label' => 'LBL_RC_identities_level',		'fieldType' => 'picklist', 'value' => array(0,1,2,3,4) ),
		'auto_create_user'			=> array('label' => 'LBL_RC_auto_create_user',		'fieldType' => 'checkbox'),
		'smtp_log'					=> array('label' => 'LBL_RC_smtp_log',				'fieldType' => 'checkbox'),
		'session_lifetime'			=> array('label' => 'LBL_RC_session_lifetime',		'fieldType' => 'int'),
	);
	}
	function createdAlert($info) {
		$return = '<div class="alert alert-block alert-error fade in" style="margin-left: 10px;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">'.$info.'</h4></div>';
	return $return;
	}
	function GetSite_URL() {
		$site_URL = vglobal('site_URL');
		if(substr($site_URL, -1) != '/'){
			$site_URL = $site_URL.'/';
		}
		return $site_URL;
	}
	function getMailsFromIMAP($user = false) {
		$Accounts = self::getAccountsList($user,true);
		$mails = Array();
		$mailLimit = 5;
		if($Accounts){
			$imap = self::imap_connect($Accounts[0]['username'] , $Accounts[0]['password']);
			$numMessages = imap_num_msg($imap);
			if( $numMessages < $mailLimit){
				$mailLimit = $numMessages;
			}
			for ($i = $numMessages; $i > ($numMessages - $mailLimit); $i--) {
				$header = imap_headerinfo($imap, $i);
				$mail_detail = self::get_mail_detail($imap, false, $i);
				$mails[] = $mail_detail;
			}
			imap_close($imap);
		}
		return $mails;
	}
	
	function getUrlToCompose($module, $record) {
        if ( !isRecordExists($record) )
            return false;
		$recordModel_OSSMailView = Vtiger_Record_Model::getCleanInstance('OSSMailView');
		$email = $recordModel_OSSMailView->findEmail( $record, $module );
		$url = '&to='.$email;
		$InstanceModel = Vtiger_Record_Model::getInstanceById($record, $module);
		if($module == 'HelpDesk'){
			$urldata = '&subject='.$InstanceModel->get('ticket_no').' - '.$InstanceModel->get('ticket_title');
		}elseif($module == 'Potentials'){
			$urldata = '&subject='.$InstanceModel->get('potential_no').' - '.$InstanceModel->get('potentialname');
		}elseif($module == 'Project'){
			$urldata = '&subject='.$InstanceModel->get('project_no').' - '.$InstanceModel->get('projectname');
		}
		$url .= $urldata;
		return $url;
	}	
}
