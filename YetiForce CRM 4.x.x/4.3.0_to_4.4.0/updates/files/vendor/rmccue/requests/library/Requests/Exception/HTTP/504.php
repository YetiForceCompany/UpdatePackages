<?php
/**
 * Exception for 504 Gateway Timeout responses.
 */

/**
 * Exception for 504 Gateway Timeout responses.
 */
class Requests_Exception_HTTP_504 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 504;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Gateway Timeout';
}
