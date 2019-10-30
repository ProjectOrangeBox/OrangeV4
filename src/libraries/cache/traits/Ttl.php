<?php

namespace projectorangebox\orange\library\cache\traits;

trait Ttl {

	/**
	 *
	 * Wrapper function to use this library in a closure fashion
	 * of course these are request only cached items
	 *
	 * @access public
	 *
	 * @param int $ttl null
	 *
	 * @return init
	 *
	 */
	public function ttl(int $ttl = null) : int
	{
		return ci('cache')->ttl($ttl);
	}

} /* end trait */