<?php
/**
 * Exception for 400 Bad Request responses.
 */

/**
 * Exception for 400 Bad Request responses.
 */
class Requests_Exception_HTTP_400 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 400;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Bad Request';
}
