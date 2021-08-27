<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var string
	 */
	public $logFile = 'cache/logs/updateLogsTrace.log';
	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;

	/**
	 * @var object
	 */
	public $moduleNode;

	/**
	 * Fields to delete.
	 *
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * Constructor.
	 *
	 * @param object $moduleNode
	 */
	public function __construct($moduleNode)
	{
		$this->moduleNode = $moduleNode;
	}

	/**
	 * Logs.
	 *
	 * @param string $message
	 */
	public function log($message)
	{
		$fp = fopen($this->logFile, 'a+');
		fwrite($fp, $message . PHP_EOL);
		fclose($fp);
	}

	/**
	 * Preupdate.
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$this->emailTemplates();
		$this->importerDb();
	}

	public function emailTemplates()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$oldContent = (new \App\Db\Query())->select(['content'])->from('u_yf_emailtemplates')->where(['sys_name' => 'UsersResetPassword'])->scalar();
		$usersResetPassword = <<<'STR'
		<table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" class="table_full editable-bg-color bg_color_ffffff editable-bg-image" style="max-width:1024px;min-width:320px;">
	<tbody>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr>
						<td bgcolor="#fcfcfc" style="border:1px solid #f2f2f2;border-radius:5px;padding:10px;">
						<table align="center" border="0" cellpadding="0" cellspacing="0" class="no_float">
							<tbody>
								<tr>
									<td align="center" class="editable-img">$(organization : logo)$</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="25">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr style="text-align:left;">
						<td bgcolor="#fcfcfc" style="padding:30px 20px 30px 20px;border:1px solid #f2f2f2;border-radius:5px;">
							<p>Dear user,<br />
								We received a request to change the password to your account.<br />
								In order to set a new password click the following link (valid until $(params : expirationDate)$):<br /><br />
								<a href="$(params : url)$" target="_blank">$(params : url)$</a><br />
								$(params : token)$
								<br /><br />
								If you didn't request the passwords change please report it to the administrator or use the password change option available on the login page.
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="40">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr>
						<td align="center" class="text_color_c6c6c6" style="line-height:1;font-size:14px;font-weight:400;font-family:'Open Sans', Helvetica, sans-serif;">
						<div class="editable-text"><span class="text_container">&copy; 2021 YetiForce Sp. z o.o. All Rights Reserved.</span></div>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
	</tbody>
</table>
STR;
		if ('1c5a01d67ab1fa3e3c4e6baa1f3f164f6af00665' === sha1($oldContent)) {
			$dbCommand->update('u_yf_emailtemplates', ['content' => $usersResetPassword], ['sys_name' => 'UsersResetPassword'])->execute();
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add tables.
	 */
	public function importerDb()
	{
		$db = \App\Db::getInstance();
		$importer = new \App\Db\Importers\Base();
		$tables = [
			'u_#__mail_quantities' => [
				'columns' => [
					'userid' => $importer->integer(10)->unsigned()->notNull(),
					'num' => $importer->smallInteger()->unsigned()->defaultValue(0),
					'date' => $importer->dateTime(),
				],
				'primaryKeys' => [
					['mail_quantities_pk', 'userid'],
				],
				'foreignKey' => [
					['u_#__mail_quantities_ibfk_1', 'u_#__mail_quantities', 'userid', 'roundcube_users', 'user_id', 'CASCADE', null],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'roundcube_users' => [
				'columns' => [
					'crm_status' => $importer->smallInteger(1)->defaultValue(0),
					'crm_error' => $importer->stringType(200),
				],
				'columns_mysql' => [
					'crm_status' => $importer->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['crm_status', 'crm_status'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
		];
		$base = new \App\Db\Importer();
		$base->dieOnError = App\Config::debug('SQL_DIE_ON_ERROR');
		foreach ($tables as $tableName => $data) {
			$importer->tables = [$tableName => $data];
			if ($db->isTableExists($tableName)) {
				$base->updateTables($importer);
			} else {
				$base->addTables($importer);
				if (isset($data['foreignKey'])) {
					$importer->foreignKey = $data['foreignKey'];
					$base->addForeignKey($importer);
				}
			}
		}
		if ($db->isTableExists('yetiforce_mail_quantities')) {
			$base->dropTable(['yetiforce_mail_quantities']);
		}
		$base->logs(true);
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\App\Cache::clear();
		\App\Cache::resetOpcache();
		return true;
	}
}
