<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\ServiceLocatorInterface;
use projectorangebox\orange\library\exceptions\Internal\MethodNotFoundException;
use projectorangebox\orange\library\exceptions\MVC\ServiceException;

class ServiceLocator implements ServiceLocatorInterface
{
	/**
	 * $config
	 *
	 * The services array
	 *
	 * @var array
	 */
	static protected $config = [];

	/**
	 * __construct
	 *
	 * @param array $config
	 * @return this
	 */
	public function __construct(array $config)
	{
		self::$config = &$config;
	}

	/**
	 * -- returns namespaced class
	 * ci('serviceLocator')->find{config array key}(..);
	 *
	 * -- returns boolean
	 * ci('serviceLocator')->has{config array key}(..);
	 *
	 * -- returns true
	 * ci('serviceLocator')->add{config array key}(..);
	 *
	 */
	public function __call(string $name, array $arguments)
	{
		$name = strtolower($name);

		if (substr($name, 0, 4) == 'find') {
			$responds = $this->find(substr($name, 4), $arguments[0]);
		} elseif (substr($name, 0, 3) === 'add') {
			$responds = $this->add(substr($name, 3), $arguments[0], $arguments[1]);
		} elseif (substr($name, 0, 3) === 'has') {
			$responds = $this->has(substr($name, 3), $arguments[0]);
		} else {
			/* fatal */
			throw new MethodNotFoundException(sprintf('No method named "%s" found.', $name));
		}

		return $responds;
	}

	/**
	 * find
	 *
	 * Search for a service based on type and named
	 * If nothing found a fatal exception is thrown
	 * Use "has" to check first if you need to
	 *
	 * @param string $type
	 * @param string $name
	 * @return string
	 */
	public function find(string $type, string $name): string
	{
		$type = strtolower($type);
		$name = strtolower($name);

		/* if the name you are looking for is missing it's fatal */
		if (!isset(self::$config[$type])) {
			/* fatal */
			throw new ServiceException(sprintf('Could not locate a %s type.', $type));
		} elseif (!isset(self::$config[$type][$name])) {
			/* fatal */
			throw new ServiceException(sprintf('Could not locate a %s type named %s.', $type, $name));
		}

		return self::$config[$type][$name];
	}

	/**
	 * has
	 *
	 * Does this type and name exsist?
	 *
	 * @param string $type
	 * @param string $name
	 * @return boolean
	 */
	public function has(string $type, string $name): bool
	{
		$type = strtolower($type);

		return isset(self::$config[$type], self::$config[$type][$this->findAlias($name)]);
	}

	/**
	 * add
	 *
	 * Add a service by type and name
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $serviceClass
	 * @return boolean
	 */
	public function add(string $type, string $name, string $value): bool
	{
		$type = strtolower($type);
		$name = strtolower($name);

		/* add the parent level if it's not there already */
		if (!isset(self::$config[$type])) {
			self::$config[$type] = [];
		}

		self::$config[$type][$name] = $value;

		return true;
	}

	/**
	 * serviceAlias
	 *
	 * Is there are service alias for this serivce based on name passed
	 *
	 * @param string $name
	 * @return string
	 */
	protected function findAlias(string $name): string
	{
		/* return orginal name if no alias exists */
		return self::$config['alias'][strtolower($name)] ?? $name;
	}

	/**
	 * get - singleton
	 *
	 * return the same instance each time
	 *
	 * $instance = singleton('user',['name'=>'Johnny']);
	 * $instance = singleton('auth');
	 *
	 * $instance = singleton('\namespace\class');
	 * $instance = singleton('\namespace\class',['name'=>'Johnny']);
	 * $instance = singleton('\namespace\class',['name'=>'Johnny'],'user');
	 *
	 * @param string $serviceName
	 * @param mixed array
	 * @param mixed string
	 * @return object
	 */
	public function get(string $serviceName, array $userConfig = [], string $as = null): object
	{
		/* is there are alias for this service name NOTE: these are NOT typed */
		$singletonName = $as ?? $this->findAlias($serviceName);

		/* normalize the instance */
		$singletonName = strtolower($singletonName);

		/* has this service been attached yet? */
		if (!isset(get_instance()->$singletonName)) {
			/* create the single instance */
			get_instance()->$singletonName = $this->create($serviceName, $userConfig);
		}

		/* now grab the reference */
		return get_instance()->$singletonName;
	}

	/**
	 * creat - factory
	 *
	 * Create a new instance each time this is called
	 *
	 * @param string $serviceName
	 * @param mixed array
	 * @return object
	 */
	public function create(string $serviceName, array $userConfig = []): object
	{
		/* save the orginal name */
		$rawServiceName = $serviceName;

		/* is this service known by an alias? */
		$serviceName = $this->findAlias($serviceName);

		/* normalize the instance */
		$serviceName = strtolower($serviceName);

		/* default to sent in user config */
		$config = $userConfig;

		/* try to load it's configuration if configuration library loaded */
		if (isset(get_instance()->config)) {
			/* get configuration if matching config filename exists */
			$serviceConfig = get_instance()->config->item($serviceName);

			/* did we get back an array? */
			if (is_array($serviceConfig)) {
				/* yes! replace the matching keys of the user config over the loaded config */
				$config = array_replace($serviceConfig, $userConfig);
			}
		}

		/* What is the namespaced class? */
		$serviceClass = ($this->has('service', $serviceName)) ? $this->find('service', $serviceName) : $rawServiceName;

		/* return a new instance of the class */
		return new $serviceClass($config);
	}
} /* end class */
