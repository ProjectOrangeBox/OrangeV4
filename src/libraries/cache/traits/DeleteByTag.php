<?php

namespace projectorangebox\orange\library\cache\traits;

trait DeleteByTag
{

	/**
	 *
	 * Delete cache records based on dot notation "tags"
	 *
	 * @access public
	 *
	 * @param mixed $args array, period separated list of tags or multiple arguments
	 *
	 * @return Cache
	 *
	 * #### Example
	 * ```php
	 * delete_cache_by_tags('acl','user','roles');
	 * delete_cache_by_tags('acl.user.roles');
	 * delete_cache_by_tags(['acl','user','roles']);
	 *
	 * ci('cache')->using('file')->deleteByTags('acl');
	 * ci('cache')->using('apc')->deleteByTags('acl');
	 *
	 * ```
	 */
	public function deleteByTags($args) /* mixed depending on who I'm attached to */
	{
		/*
		determine if it's a array, period separated list of tags or multiple arguments
		make sure it's an array
		*/
		if (is_array($args)) {
			$tags = $args;
		} elseif (strpos($args, '.') !== false) {
			$tags = explode('.', $args);
		} else {
			$tags = func_get_args();
		}

		/* log a debug entry */
		log_message('debug', 'delete_cache_by_tags ' . implode(', ', $tags));

		/* trigger a event incase somebody else needs to know send in our array of tags by reference */
		ci('event')->trigger('delete.cache.by.tags', $tags, $this);

		/* get all of the currently loaded cache driver cache keys */
		$cachedKeys = $this->cache_keys();

		/* if the cache key has 1 or more matching tag delete the entry */
		if (is_array($cachedKeys)) {
			foreach ($cachedKeys as $key) {
				if (count(array_intersect(explode('.', $key), $tags))) {
					$this->delete($key);
				}
			}
		}

		return $this;
	}
} /* end trait */
