<?php
/**
 * Exception for 406 Not Acceptable responses.
 */

/**
 * Exception for 406 Not Acceptable responses.
 */
class Requests_Exception_HTTP_406 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 406;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Not Acceptable';
}
