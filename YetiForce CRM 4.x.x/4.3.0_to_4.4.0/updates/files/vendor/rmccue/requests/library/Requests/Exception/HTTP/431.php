<?php
/**
 * Exception for 431 Request Header Fields Too Large responses.
 *
 * @see https://tools.ietf.org/html/rfc6585
 */

/**
 * Exception for 431 Request Header Fields Too Large responses.
 *
 * @see https://tools.ietf.org/html/rfc6585
 */
class Requests_Exception_HTTP_431 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 431;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Request Header Fields Too Large';
}
