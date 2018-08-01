<?php
/**
 * Exception for 411 Length Required responses.
 */

/**
 * Exception for 411 Length Required responses.
 */
class Requests_Exception_HTTP_411 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 411;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Length Required';
}
