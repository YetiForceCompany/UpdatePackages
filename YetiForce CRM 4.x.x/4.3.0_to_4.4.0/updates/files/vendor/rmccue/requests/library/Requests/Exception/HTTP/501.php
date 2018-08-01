<?php
/**
 * Exception for 501 Not Implemented responses.
 */

/**
 * Exception for 501 Not Implemented responses.
 */
class Requests_Exception_HTTP_501 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 501;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Not Implemented';
}
