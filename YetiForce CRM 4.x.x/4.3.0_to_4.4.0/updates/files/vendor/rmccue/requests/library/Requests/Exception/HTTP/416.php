<?php
/**
 * Exception for 416 Requested Range Not Satisfiable responses.
 */

/**
 * Exception for 416 Requested Range Not Satisfiable responses.
 */
class Requests_Exception_HTTP_416 extends Requests_Exception_HTTP
{
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $code = 416;

	/**
	 * Reason phrase.
	 *
	 * @var string
	 */
	protected $reason = 'Requested Range Not Satisfiable';
}
