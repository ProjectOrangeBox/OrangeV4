<?php

namespace projectorangebox\orange\library\cache;

use CI_Cache_apc;

/* wrapper */

class Apc extends CI_Cache_apc
{
	use traits\DeleteByTag;
	use traits\Inline;
	use traits\Ttl;

	/**
	 * cache_info
	 *
	 * @param mixed $type
	 * @return void
	 */
	public function cache_info($type = 'user')
	{
		return apc_cache_info($type);
	}

	/**
	 * cache_keys
	 *
	 * @return void
	 */
	public function cache_keys() : array
	{
		$keys = [];

		$info = apc_cache_info('user');

		foreach ($info['cache_list'] as $cache_list) {
			$keys[] = $cache_list['info'];
		}

		return $keys;
	}

} /* end class */