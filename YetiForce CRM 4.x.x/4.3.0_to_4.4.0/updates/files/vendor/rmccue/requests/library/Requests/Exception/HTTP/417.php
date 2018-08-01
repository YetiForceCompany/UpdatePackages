<?php
/**
 * Exception for 417 Expectation Failed responses.
 */

/**
 * Exception for 417 Expectation Failed responses.
 */
class Requests_Exception_HTTP_417 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 417;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Expectation Failed';
}
