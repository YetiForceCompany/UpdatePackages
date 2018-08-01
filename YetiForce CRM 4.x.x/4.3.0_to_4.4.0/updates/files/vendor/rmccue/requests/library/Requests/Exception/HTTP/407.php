<?php
/**
 * Exception for 407 Proxy Authentication Required responses.
 */

/**
 * Exception for 407 Proxy Authentication Required responses.
 */
class Requests_Exception_HTTP_407 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 407;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Proxy Authentication Required';
}
