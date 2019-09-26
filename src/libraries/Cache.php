<?php

namespace projectorangebox\orange\library;

use orange;

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
	static protected $ttl = 0;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array &$config=null)
	{
		if (is_array($config)) {
			$this->config = &$config;
		}

		/* combined config */
		$this->config = array_replace(ci('config')->item('config'),$this->config);

		$this->adapter = isset($this->config['cache_default']) ? $this->config['cache_default'] : 'dummy';

		if (!$this->driver($this->adapter)->is_supported()) {
			throw new \Exception('Cache Driver '.$this->adapter.' is a unsupported.');
		}

		self::$ttl = isset($this->config['cache_ttl']) ? $this->config['cache_ttl'] : 0;

		log_message('info', 'Orange Cache Class Initialized');
	}

	public function __get($name)
	{
		/* if the driver doesn't exist the driver() method will throw a exception */
		return $this->driver($name);
	}

	public function __call($name, $arguments)
	{
		/* test for supported methods */
		if (!in_array($name,['get','save','delete','increment','decrement','clean','cache_info','get_metadata','cache','deleteByTags','ttl'])) {
			throw new \Exception($name.' is a unsupported method.');
		}

		return call_user_func_array([$this->driver($this->adapter),$name],$arguments);
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
			$service = ci('servicelocator')->find('service','cache_driver_'.$name);

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
	 * @param mixed $cache_ttl
	 * @param bool $use_window - use a cache "window" which should help prevent a stampede.
	 *
	 * @return int
	 *
	 */
	static public function ttl(/* mixed */ $cache_ttl = null,bool $use_window = true) : int
	{
		/**
		 * if cache_ttl boolean this is used as the $use_window variable and cache_ttl is set to the value in the the configration file
	 	 * if cache_ttl is null then cache_ttl is set to the value in the the configration file
	 	 * else cache_ttl is set the integer value of what was sent in for cache_ttl
		 */
		if (is_bool($cache_ttl)) {
			$use_window = $cache_ttl;
			$cache_ttl = self::$ttl;
		} elseif (is_null($cache_ttl)) {
			$cache_ttl = self::$ttl;
		} else {
			$cache_ttl = (int)$cache_ttl;
		}

		/* are we using the window option? */
		if ($use_window) {
			/* let determine the window size based on there cache time to live length no more than 5 minutes */
			$window = min(300, ceil($cache_ttl * .02));

			/* add it to the cache_ttl to get our "new" cache time to live */
			$cache_ttl += mt_rand(-$window, $window);
		}

		return $cache_ttl;
	}

} /* end class */
