<?php

namespace projectorangebox\orange\library\cache;

use App;
use CI_Cache_file;

/* wrapper */

class File extends CI_Cache_file
{
	use traits\DeleteByTag;
	use traits\Inline;
	use traits\Ttl;

	public function __construct()
	{
		parent::__construct();

		$this->_cache_path = FS::path($this->_cache_path);
	}

	/**
	 * cache_keys
	 *
	 * @return array
	 */
	public function cache_keys(): array
	{
		$keys = [];

		foreach (glob($this->_cache_path . '*') as $path) {
			$keys[] = basename($path);
		}

		return $keys;
	}
} /* end class */
