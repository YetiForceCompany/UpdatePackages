<?php
/**
 * Exception for 412 Precondition Failed responses.
 */

/**
 * Exception for 412 Precondition Failed responses.
 */
class Requests_Exception_HTTP_412 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 412;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Precondition Failed';
}
