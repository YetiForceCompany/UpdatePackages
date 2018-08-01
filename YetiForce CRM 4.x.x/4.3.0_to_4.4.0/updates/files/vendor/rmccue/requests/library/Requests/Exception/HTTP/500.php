<?php
/**
 * Exception for 500 Internal Server Error responses.
 */

/**
 * Exception for 500 Internal Server Error responses.
 */
class Requests_Exception_HTTP_500 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 500;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Internal Server Error';
}
