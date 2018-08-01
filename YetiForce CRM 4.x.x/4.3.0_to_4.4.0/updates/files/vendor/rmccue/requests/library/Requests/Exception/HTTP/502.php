<?php
/**
 * Exception for 502 Bad Gateway responses.
 */

/**
 * Exception for 502 Bad Gateway responses.
 */
class Requests_Exception_HTTP_502 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 502;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Bad Gateway';
}
