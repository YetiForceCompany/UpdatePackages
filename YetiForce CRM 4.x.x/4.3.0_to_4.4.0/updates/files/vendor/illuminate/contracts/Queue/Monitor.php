<?php

namespace Illuminate\Contracts\Queue;

interface Monitor
{
	/**
	 * Register a callback to be executed on every iteration through the queue loop.
	 *
	 * @param mixed $callback
	 */
	public function looping($callback);

	/**
	 * Register a callback to be executed when a job fails after the maximum amount of retries.
	 *
	 * @param mixed $callback
	 */
	public function failing($callback);

	/**
	 * Register a callback to be executed when a daemon queue is stopping.
	 *
	 * @param mixed $callback
	 */
	public function stopping($callback);
}
