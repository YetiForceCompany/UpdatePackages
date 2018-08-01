<?php
/**
 * Exception for 404 Not Found responses.
 */

/**
 * Exception for 404 Not Found responses.
 */
class Requests_Exception_HTTP_404 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 404;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Not Found';
}
