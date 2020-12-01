<?php

namespace App\Session;

/**
 * Base Session Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base extends \SessionHandler
{
	/**
	 * Is driver available.
	 *
	 * @return bool
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Construct.
	 *
	 * @param string $name
	 */
	public function __construct(string $name = 'YTSID')
	{
		if (PHP_SESSION_ACTIVE === session_status()) {
			return;
		}
		$cookie = session_get_cookie_params();
		$cookie['lifetime'] = \Config\Security::$maxLifetimeSessionCookie ?? 0;
		$cookie['secure'] = \App\RequestUtil::isHttps();
		$cookie['domain'] = $_SERVER['HTTP_HOST'] ?? '';
		if (isset(\Config\Security::$cookieForceHttpOnly)) {
			$cookie['httponly'] = \Config\Security::$cookieForceHttpOnly;
		}
		if ($cookie['secure']) {
			$cookie['samesite'] = \Config\Security::$cookieSameSite;
		}
		session_name($name);
		if (\PHP_VERSION_ID < 70300) {
			if ($cookie['secure']) {
				$cookie['path'] .= '; samesite=' . $cookie['samesite'];
			}
			session_set_cookie_params(
				$cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']
			);
		} else {
			session_set_cookie_params($cookie);
		}
	}

	/**
	 * Function to get the value for a given key.
	 *
	 * @param string $key
	 *
	 * @return mixed Value for the given key
	 */
	public function get($key)
	{
		return $_SESSION[$key] ?? null;
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	/**
	 * Function to check if the key exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * Function to remove the value.
	 *
	 * @param string $key
	 */
	public function delete($key)
	{
		unset($_SESSION[$key]);
	}

	/**
	 * Update the current session id with a newly generated one.
	 *
	 * @see http://php.net/manual/en/function.session-regenerate-id.php
	 *
	 * @param bool $deleteOldSession
	 */
	public function regenerateId($deleteOldSession = false)
	{
		return session_regenerate_id($deleteOldSession);
	}

	/**
	 * Function to get session data by id.
	 *
	 * @param string $sessionId
	 *
	 * @return array
	 */
	public function getById(string $sessionId): array
	{
		return [];
	}

	/**
	 * Function to clean session. Removed old session.
	 *
	 * @return string[]
	 */
	public static function clean()
	{
	}
}
