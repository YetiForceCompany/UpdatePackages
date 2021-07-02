<?php
/**
 * Encryption basic class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class to encrypt and decrypt data.
 */
class Encryption extends Base
{
	/** @var array Passwords to encrypt */
	private static $mapPasswords = [
		'roundcube_users' => ['columnName' => ['password'], 'index' => 'user_id', 'db' => 'base'],
		's_#__mail_smtp' => ['columnName' => ['password', 'smtp_password'], 'index' => 'id', 'db' => 'admin'],
		'a_#__smsnotifier_servers' => ['columnName' => ['api_key'], 'index' => 'id', 'db' => 'admin'],
		'w_#__api_user' => ['columnName' => ['auth'], 'index' => 'id', 'db' => 'webservice'],
		'w_#__portal_user' => ['columnName' => ['auth'], 'index' => 'id', 'db' => 'webservice'],
		'w_#__servers' => ['columnName' => ['pass', 'api_key'], 'index' => 'id', 'db' => 'webservice'],
		'dav_users' => ['columnName' => ['key'], 'index' => 'id', 'db' => 'base'],
		\App\MeetingService::TABLE_NAME => ['columnName' => ['secret'], 'index' => 'id', 'db' => 'admin'],
		'i_#__magento_servers' => ['columnName' => ['password'], 'index' => 'id', 'db' => 'admin'],
	];
	/**
	 * @var array Recommended encryption methods
	 */
	public static $recommendedMethods = [
		'aes-256-cbc', 'aes-256-ctr', 'aes-192-cbc', 'aes-192-ctr',
	];

	/**
	 * Function to get instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (Cache::has('Encryption', 'Instance')) {
			return Cache::get('Encryption', 'Instance');
		}
		$row = (new \App\Db\Query())->from('a_#__encryption')->one(\App\Db::getInstance('admin'));
		$instance = new static();
		if ($row) {
			$instance->set('method', $row['method']);
			$instance->set('vector', $row['pass']);
		}
		$instance->set('pass', \App\Config::securityKeys('encryptionPass'));
		Cache::save('Encryption', 'Instance', $instance, Cache::LONG);
		return $instance;
	}

	/**
	 * Function to change password for encryption.
	 *
	 * @param string $method
	 * @param string $password
	 * @param string $vector
	 *
	 * @throws \Exception
	 * @throws Exceptions\AppException
	 */
	public static function recalculatePasswords(string $method, string $password, string $vector)
	{
		$decryptInstance = static::getInstance();
		if ($decryptInstance->get('method') === $method && $decryptInstance->get('vector') === $vector && $decryptInstance->get('pass') === $password) {
			return;
		}
		$oldMethod = $decryptInstance->get('method');
		$dbAdmin = Db::getInstance('admin');
		$transactionAdmin = $dbAdmin->beginTransaction();
		$transactionBase = Db::getInstance()->beginTransaction();
		$transactionWebservice = Db::getInstance('webservice')->beginTransaction();
		try {
			$passwords = [];
			foreach (self::$mapPasswords as $tableName => $info) {
				$values = (new Db\Query())->select(array_merge([$info['index']], $info['columnName']))
					->from($tableName)
					->createCommand(Db::getInstance($info['db']))
					->queryAllByGroup(1);
				if (!$values) {
					continue;
				}
				if ($decryptInstance->isActive()) {
					foreach ($values as &$columns) {
						foreach ($columns as &$value) {
							if (!empty($value)) {
								$value = $decryptInstance->decrypt($value);
								if (empty($value)) {
									throw new Exceptions\AppException('ERR_IMPOSSIBLE_DECRYPT');
								}
							}
						}
					}
				}
				$passwords[$tableName] = $values;
			}
			$dbAdmin->createCommand()->delete('a_#__encryption')->execute();
			if (!$decryptInstance->isActive() || !empty($method)) {
				$dbAdmin->createCommand()->insert('a_#__encryption', ['method' => $method, 'pass' => $vector])->execute();
			}
			$configFile = new ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $method);
			$configFile->set('encryptionPass', $password);
			$configFile->create();
			Cache::clear();
			\App\Config::set('securityKeys', 'encryptionMethod', $method);
			\App\Config::set('securityKeys', 'encryptionPass', $password);
			$encryptInstance = static::getInstance();
			foreach ($passwords as $tableName => $pass) {
				$dbCommand = Db::getInstance(self::$mapPasswords[$tableName]['db'])->createCommand();
				foreach ($pass as $index => $values) {
					foreach ($values as &$value) {
						if (!empty($value)) {
							$value = $encryptInstance->encrypt($value);
							if (empty($value)) {
								throw new Exceptions\AppException('ERR_IMPOSSIBLE_ENCRYPT');
							}
						}
					}
					$dbCommand->update($tableName, $values, [self::$mapPasswords[$tableName]['index'] => $index])->execute();
				}
			}
			$transactionWebservice->commit();
			$transactionBase->commit();
			$transactionAdmin->commit();
		} catch (\Throwable $e) {
			$transactionWebservice->rollBack();
			$transactionBase->rollBack();
			$transactionAdmin->rollBack();
			$configFile = new ConfigFile('securityKeys');
			$configFile->set('encryptionMethod', $oldMethod);
			$configFile->create();
			throw $e;
		}
	}

	/**
	 * Specifies the length of the vector.
	 *
	 * @param string $method
	 *
	 * @return int
	 */
	public static function getLengthVector($method)
	{
		return openssl_cipher_iv_length($method);
	}

	/**
	 * Function to encrypt data.
	 *
	 * @param string $decrypted
	 *
	 * @return string
	 */
	public function encrypt($decrypted)
	{
		if (!$this->isActive()) {
			return $decrypted;
		}
		$encrypted = openssl_encrypt($decrypted, $this->get('method'), $this->get('pass'), $this->get('options'), $this->get('vector'));
		return base64_encode($encrypted);
	}

	/**
	 * Function to decrypt data.
	 *
	 * @param string $encrypted
	 *
	 * @return string
	 */
	public function decrypt($encrypted)
	{
		if (!$this->isActive()) {
			return $encrypted;
		}
		return openssl_decrypt(base64_decode($encrypted), $this->get('method'), $this->get('pass'), $this->get('options'), $this->get('vector'));
	}

	/**
	 * Returns list method of encryption.
	 *
	 * @return string[]
	 */
	public static function getMethods()
	{
		return array_filter(openssl_get_cipher_methods(), function ($methodName) {
			return false === stripos($methodName, 'gcm') && false === stripos($methodName, 'ccm');
		});
	}

	/**
	 * Checks if encrypt or decrypt is possible.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		if (!\function_exists('openssl_encrypt') || $this->isEmpty('method') || $this->get('method') !== \App\Config::securityKeys('encryptionMethod') || !\in_array($this->get('method'), static::getMethods())) {
			return false;
		}
		return true;
	}

	/**
	 * Generate random password.
	 *
	 * @param int   $length
	 * @param mixed $type
	 *
	 * @return string
	 */
	public static function generatePassword($length = 10, $type = 'lbd')
	{
		$chars = [];
		if (false !== strpos($type, 'l')) {
			$chars[] = 'abcdefghjkmnpqrstuvwxyz';
		}
		if (false !== strpos($type, 'b')) {
			$chars[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		}
		if (false !== strpos($type, 'd')) {
			$chars[] = '0123456789';
		}
		if (false !== strpos($type, 's')) {
			$chars[] = '!"#$%&\'()*+,-./:;<=>?@[\]^_{|}';
		}
		$password = $allChars = '';
		foreach ($chars as $char) {
			$allChars .= $char;
			$password .= $char[array_rand(str_split($char))];
		}
		$allChars = str_split($allChars);
		$missing = $length - \count($chars);
		for ($i = 0; $i < $missing; ++$i) {
			$password .= $allChars[array_rand($allChars)];
		}
		return str_shuffle($password);
	}

	/**
	 * Generate user password.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function generateUserPassword($length = 10)
	{
		$passDetail = \Settings_Password_Record_Model::getUserPassConfig();
		if ($length > $passDetail['max_length']) {
			$length = $passDetail['max_length'];
		}
		if ($length < $passDetail['min_length']) {
			$length = $passDetail['min_length'];
		}
		$type = 'l';
		if ('true' === $passDetail['numbers']) {
			$type .= 'd';
		}
		if ('true' === $passDetail['big_letters']) {
			$type .= 'b';
		}
		if ('true' === $passDetail['special']) {
			$type .= 's';
		}
		return static::generatePassword($length, $type);
	}

	/**
	 * Function to create a hash.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function createHash($text)
	{
		return crypt($text, '$1$' . \App\Config::main('application_unique_key'));
	}

	/**
	 * Function to create a password hash.
	 *
	 * @param string $text
	 * @param string $pepper
	 *
	 * @return string
	 */
	public static function createPasswordHash(string $text, string $pepper): string
	{
		return password_hash(hash_hmac('sha256', $text, $pepper . \App\Config::main('application_unique_key')), PASSWORD_ARGON2ID);
	}

	/**
	 * Check password hash.
	 *
	 * @param string $password
	 * @param string $hash
	 * @param string $pepper
	 *
	 * @return bool
	 */
	public static function verifyPasswordHash(string $password, string $hash, string $pepper): bool
	{
		return password_verify(hash_hmac('sha256', $password, $pepper . \App\Config::main('application_unique_key')), $hash);
	}
}
