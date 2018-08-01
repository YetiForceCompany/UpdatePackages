<?php
/**
 * Exception for 408 Request Timeout responses.
 */

/**
 * Exception for 408 Request Timeout responses.
 */
class Requests_Exception_HTTP_408 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 408;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Request Timeout';
}
