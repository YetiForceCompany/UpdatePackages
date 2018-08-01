<?php
/**
 * Exception for 418 I'm A Teapot responses.
 *
 * @see https://tools.ietf.org/html/rfc2324
 */

/**
 * Exception for 418 I'm A Teapot responses.
 *
 * @see https://tools.ietf.org/html/rfc2324
 */
class Requests_Exception_HTTP_418 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 418;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = "I'm A Teapot";
}
