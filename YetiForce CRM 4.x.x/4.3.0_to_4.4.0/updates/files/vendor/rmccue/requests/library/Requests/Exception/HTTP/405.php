<?php
/**
 * Exception for 405 Method Not Allowed responses.
 */

/**
 * Exception for 405 Method Not Allowed responses.
 */
class Requests_Exception_HTTP_405 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 405;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Method Not Allowed';
}
