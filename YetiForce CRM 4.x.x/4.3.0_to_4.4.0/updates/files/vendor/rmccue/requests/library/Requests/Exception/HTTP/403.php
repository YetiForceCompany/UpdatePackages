<?php
/**
 * Exception for 403 Forbidden responses.
 */

/**
 * Exception for 403 Forbidden responses.
 */
class Requests_Exception_HTTP_403 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 403;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Forbidden';
}
