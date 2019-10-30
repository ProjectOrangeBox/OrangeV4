<?php

namespace projectorangebox\orange\library\cache\traits;

trait Inline
{

	/**
	 *
	 * Wrapper function to use this library in a closure fashion
	 * of course these are request only cached items
	 *
	 * @access public
	 *
	 * @param string $key
	 * @param callable $closure
	 * @param int $ttl null
	 *
	 * @return mixed
	 *
	 * #### Example
	 * ```php
	 * $cached = ci('cache')->request->cache('foobar',function(){ return 'cache me for 60 seconds!' });
	 * ```
	 */
	public function cache(string $key, callable $closure, int $ttl = null)
	{
		if (!$value = $this->get($key)) {
			$value = $closure();
			$this->save($key, $value, $this->ttl($ttl));
		}

		return $value;
	}
} /* end trait */
