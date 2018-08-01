<?php
/**
 * Exception for 415 Unsupported Media Type responses.
 */

/**
 * Exception for 415 Unsupported Media Type responses.
 */
class Requests_Exception_HTTP_415 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 415;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Unsupported Media Type';
}
