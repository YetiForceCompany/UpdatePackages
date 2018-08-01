<?php
/**
 * Exception for 402 Payment Required responses.
 */

/**
 * Exception for 402 Payment Required responses.
 */
class Requests_Exception_HTTP_402 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 402;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Payment Required';
}
