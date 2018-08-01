<?php
/**
 * Exception for 428 Precondition Required responses.
 *
 * @see https://tools.ietf.org/html/rfc6585
 */

/**
 * Exception for 428 Precondition Required responses.
 *
 * @see https://tools.ietf.org/html/rfc6585
 */
class Requests_Exception_HTTP_428 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 428;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Precondition Required';
}
