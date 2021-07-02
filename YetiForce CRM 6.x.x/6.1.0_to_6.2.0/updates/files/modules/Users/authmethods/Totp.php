<?php

/**
 * TOTP authentication method class.
 * TOTP - Time-based One-time Password.
 *
 * @package AuthMethod
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class Users_Totp_Authmethod
{
	/**
	 * User authentication mode possible values.
	 * TOTP_OFF - 2FA TOTP is checking off
	 * TOTP_OPTIONAL - It is defined by the user
	 * TOTP_OBLIGATORY - It is obligatory.
	 */
	const ALLOWED_USER_AUTHY_MODE = ['TOTP_OFF', 'TOTP_OPTIONAL', 'TOTP_OBLIGATORY'];
	/**
	 * @var int - User id
	 */
	private $userId;
	/**
	 * @var string - Secret code
	 */
	private $secret;

	/**
	 * Users_Totp_Authmethod constructor.
	 *
	 * @param int $userId - Id of user
	 */
	public function __construct($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * Generate otaauth url for QR codes.
	 *
	 * @see https://github.com/google/google-authenticator/wiki/Key-Uri-Format
	 *
	 * @param string      $secret - REQUIRED: The secret parameter is an arbitrary key value encoded in Base32 according to RFC 3548. The padding specified in RFC 3548 section 2.2 is not required and should be omitted.
	 * @param string      $name   - The name is used to identify which account a key is associated with.
	 * @param string|null $issuer - STRONGLY RECOMMENDED: The issuer parameter is a string value indicating the provider or service this account is associated with, URL-encoded according to RFC 3986.
	 *
	 * @return string - otpauth url
	 */
	public function getOtpAuthUrl($secret, $name, $issuer = null)
	{
		if (null === $issuer) {
			$arr = parse_url(App\Config::main('site_URL'));
			$issuer = $arr['host'] ?? '';
		}
		$url = "otpauth://totp/{$issuer}:{$name}?secret={$secret}";
		if (!empty($issuer)) {
			$url .= "&issuer={$issuer}";
		}
		return $url;
	}

	/**
	 * Creating a secret code for TOTP.
	 *
	 * @return string
	 */
	public function createSecret()
	{
		return $this->secret = (new GoogleAuthenticator())->generateSecret();
	}

	/**
	 * Create QR code.
	 *
	 * @param string $otpAuthUrl
	 * @param string $type       - acceptable types [HTML, SVG, PNG]
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \Milon\Barcode\path|string - HTML code
	 */
	private function createQrCode($otpAuthUrl, $type = 'HTML')
	{
		$qrCodeGenerator = new \Milon\Barcode\DNS2D();
		$qrCodeGenerator->setStorPath(__DIR__ . App\Config::main('tmp_dir'));
		switch ($type) {
			case 'HTML':
				return $qrCodeGenerator->getBarcodeHTML($otpAuthUrl, 'QRCODE');
			case 'SVG':
				return $qrCodeGenerator->getBarcodeSVG($otpAuthUrl, 'QRCODE');
			case 'PNG':
				return '<img src="data:image/png;base64,' . $qrCodeGenerator->getBarcodePNG($otpAuthUrl, 'QRCODE', 10, 10) . '" alt="QR code" class="col-auto p-0" />';
			default:
				break;
		}
		throw new \App\Exceptions\NotAllowedMethod('LBL_NOT_EXIST: ' . $type);
	}

	/**
	 * Create QR code for user.
	 *
	 * @param string $type - acceptable types [HTML, SVG, PNG]
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \Milon\Barcode\path|string
	 */
	public function createQrCodeForUser($type = 'PNG')
	{
		return $this->createQrCode($this->getOtpAuthUrl($this->secret, \App\User::getUserModel($this->userId)->getDetail('user_name')), $type);
	}

	/**
	 * 2FA - verification of the code from the user.
	 *
	 * @param string $secret
	 * @param string $userCode
	 *
	 * @return bool
	 */
	public static function verifyCode($secret, $userCode)
	{
		return (new GoogleAuthenticator())->checkCode($secret, (string) $userCode);
	}

	/**
	 * Determine whether 2FA is required.
	 *
	 * @param int|null $userId - if null then getCurrentUserRealId
	 *
	 * @return bool
	 */
	public static function isActive($userId = null)
	{
		$isActive = false;
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserRealId();
		}
		$userModel = \App\User::getUserModel($userId);
		if ('PLL_PASSWORD_2FA' === $userModel->getDetail('login_method') || 'PLL_LDAP_2FA' === $userModel->getDetail('login_method')) {
			switch (App\Config::security('USER_AUTHY_MODE')) {
				case 'TOTP_OPTIONAL':
					$isActive = 'PLL_AUTHY_TOTP' === $userModel->getDetail('authy_methods');
					break;
				case 'TOTP_OBLIGATORY':
					$isActive = true;
					break;
				default:
					break;
			}
		}
		return $isActive;
	}

	/**
	 * Check if 2FA initiation is necessary.
	 *
	 * @param int|null $userId - if null then getCurrentUserRealId
	 *
	 * @return bool
	 */
	public static function mustInit($userId = null)
	{
		if (empty($userId)) {
			$userId = \App\User::getCurrentUserRealId();
		}
		return empty(\App\User::getUserModel($userId)->getDetail('authy_secret_totp'));
	}
}
