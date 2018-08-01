<?php
/**
 * Exception for 304 Not Modified responses.
 */

/**
 * Exception for 304 Not Modified responses.
 */
class Requests_Exception_HTTP_304 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 304;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Not Modified';
}
