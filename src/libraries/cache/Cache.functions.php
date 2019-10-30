<?php

/* add global wrapper function */
if (!function_exists('ttl')) {
	function ttl(int $cache_ttl = null, bool $use_window = true): int
	{
		return ci('cache')->ttl($cache_ttl, $use_window);
	}
}
