<?php

namespace projectorangebox\orange\library\cache;

use CI_Cache_memcached;

/* wrapper */

class Memcached extends CI_Cache_memcached
{
	use traits\DeleteByTag;
	use traits\Inline;
	use traits\Ttl;

	/**
	 * cache_keys
	 *
	 * @return array
	 */
	public function cache_keys() : array
	{
		$keys = [];

		/* !!todo */

		return $keys;
	}

} /* end class */