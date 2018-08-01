<?php
/**
 * Exception for 401 Unauthorized responses.
 */

/**
 * Exception for 401 Unauthorized responses.
 */
class Requests_Exception_HTTP_401 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 401;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Unauthorized';
}
