<?php
/**
 * Exception for 503 Service Unavailable responses.
 */

/**
 * Exception for 503 Service Unavailable responses.
 */
class Requests_Exception_HTTP_503 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 503;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Service Unavailable';
}
