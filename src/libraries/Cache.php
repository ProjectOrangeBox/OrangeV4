<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\exceptions\Internal\MethodNotFoundException;
use projectorangebox\orange\library\exceptions\MVC\UnsupportedException;

/**
 * Extension to the CodeIgniter Cache Library
 *
 * Adds additional request & export cache libraries
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses event Event
 *
 * @config cache_path `ROOTPATH.'/var/cache/'`
 * @config cache_default `dummy`
 * @config cache_ttl `60`
 *
 */
class Cache
{
	/**
	 * configuration storage
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * $drivers
	 *
	 * @var array
	 */
	protected $drivers = [];

	/**
	 * Reference to the driver
	 *
	 * @var mixed
	 */
	protected $adapter = 'dummy';

	/**
	 * $ttl
	 *
	 * @var integer
	 */
	protected $ttl = 0;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array &$config = [])
	{
		$this->config = ci('config')->merged('config', ['cache_default' => 'dummy', 'cache_ttl' => 0], $config);

		/* Bring in my global namespace function */
		require_once 'cache/Cache.functions.php';

		/* setup the default adapter incase they don't specifiy one */
		$this->adapter = $this->config['cache_default'];

		if (!$this->driver($this->adapter)->is_supported()) {
			throw new UnsupportedException($this->adapter);
		}

		log_message('info', 'Orange Cache Class Initialized');
	}

	/**
	 * defaultAdapter
	 *
	 * Get the default cache driver if you don't "pick" one.
	 *
	 * @return void
	 */
	public function defaultAdapter(): string
	{
		return $this->adapter;
	}

	public function __get($name)
	{
		/* if the driver doesn't exist the driver() method will throw a exception */
		return $this->driver($name);
	}

	public function __call($name, $arguments)
	{
		/* test for supported methods */
		if (!in_array($name, ['get', 'save', 'delete', 'increment', 'decrement', 'clean', 'cache_info', 'get_metadata', 'cache', 'deleteByTags', 'ttl'])) {
			throw new MethodNotFoundException($name);
		}

		return call_user_func_array([$this->driver($this->adapter), $name], $arguments);
	}

	// ------------------------------------------------------------------------

	/**
	 * Is the requested driver supported in this environment?
	 *
	 * @param	string	$driver	The driver to test
	 * @return	array
	 */
	public function is_supported($driver)
	{
		return $this->driver($driver)->is_supported();
	}

	/**
	 * driver
	 *
	 * @param string $name
	 * @return void
	 */
	protected function driver(string $name) /* mixed */
	{
		if (!isset($this->drivers[$name])) {
			$service = ci('servicelocator')->find('service', 'cache_driver_' . $name);

			/* attach driver */
			$this->drivers[$name] = new $service($this->config);
		}

		/* return the driver */
		return $this->drivers[$name];
	}

	/**
	 *
	 * Get the current Cache Time to Live with optional "window" support to negate a cache stamped
	 *
	 * @access public
	 *
	 * @param mixed $cacheTTL
	 * @param bool $useWindow - use a cache "window" which should help prevent a stampede.
	 *
	 * @return int
	 *
	 */
	public function ttl(int $cacheTTL = null, bool $useWindow = true): int
	{
		$cacheTTL = $cacheTTL ?? $this->config['cache_ttl'];

		/* are we using the window option? */
		if ($useWindow) {
			/*
			let determine the window size based on there cache time to live length no more than 5 minutes
			if your traffic to the cache data is that light then cache stampede shouldn't be a problem
			*/
			$window = min(300, ceil($cacheTTL * .02));

			/* add it to the cache_ttl to get our "new" cache time to live */
			$cacheTTL += mt_rand(-$window, $window);
		}

		return $cacheTTL;
	}
} /* end class */
