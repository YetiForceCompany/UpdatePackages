<?php
/**
 * Exception for 505 HTTP Version Not Supported responses.
 */

/**
 * Exception for 505 HTTP Version Not Supported responses.
 */
class Requests_Exception_HTTP_505 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 505;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'HTTP Version Not Supported';
}
