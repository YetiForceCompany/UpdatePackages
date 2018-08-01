<?php
/**
 * Exception for 409 Conflict responses.
 */

/**
 * Exception for 409 Conflict responses.
 */
class Requests_Exception_HTTP_409 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 409;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Conflict';
}
