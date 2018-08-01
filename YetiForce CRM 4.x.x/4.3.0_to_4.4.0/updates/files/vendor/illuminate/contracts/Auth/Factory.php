<?php

namespace Illuminate\Contracts\Auth;

interface Factory
{
	/**
	 * Get a guard instance by name.
	 *
	 * @param string|null $name
	 *
	 * @return mixed
	 */
	public function guard($name = null);

	/**
	 * Set the default guard the factory should serve.
	 *
	 * @param string $name
	 */
	public function shouldUse($name);
}
