<?php
/**
 * Exception for 429 Too Many Requests responses.
 *
 * @see https://tools.ietf.org/html/draft-nottingham-http-new-status-04
 */

/**
 * Exception for 429 Too Many Requests responses.
 *
 * @see https://tools.ietf.org/html/draft-nottingham-http-new-status-04
 */
class Requests_Exception_HTTP_429 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 429;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Too Many Requests';
}
