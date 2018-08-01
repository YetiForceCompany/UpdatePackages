<?php
/**
 * Exception for 305 Use Proxy responses.
 */

/**
 * Exception for 305 Use Proxy responses.
 */
class Requests_Exception_HTTP_305 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 305;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Use Proxy';
}
