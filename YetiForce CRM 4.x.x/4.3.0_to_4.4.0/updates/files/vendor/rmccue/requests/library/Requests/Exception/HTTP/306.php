<?php
/**
 * Exception for 306 Switch Proxy responses.
 */

/**
 * Exception for 306 Switch Proxy responses.
 */
class Requests_Exception_HTTP_306 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 306;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Switch Proxy';
}
