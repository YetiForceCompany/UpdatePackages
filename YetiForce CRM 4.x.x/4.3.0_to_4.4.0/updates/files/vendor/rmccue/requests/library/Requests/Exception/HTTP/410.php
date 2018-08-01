<?php
/**
 * Exception for 410 Gone responses.
 */

/**
 * Exception for 410 Gone responses.
 */
class Requests_Exception_HTTP_410 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 410;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Gone';
}
