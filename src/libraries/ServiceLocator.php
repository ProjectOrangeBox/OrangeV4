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

	static protected $servicesKey = 'service';

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
	 *
	 * ci('serviceLocator')->findView('home');
	 * ci('serviceLocator')->findValidationRule('cleanup');
	 * ci('serviceLocator')->findService('cleanup');
	 * ci('serviceLocator')->find + a prefix(..);
	 *
	 * ci('serviceLocator')->addView('home','main/index');
	 * ci('serviceLocator')->addValidationRule('cleanup','\library\validate\rules\Cleanup');
	 * ci('serviceLocator')->addService('cleanup','\library\validate\rules\Cleanup');
	 * ci('serviceLocator')->add + a prefix(..,..);
	 *
	 * boolean = ci('serviceLocator')->hasService('cleanup');
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
			throw new ServiceException(sprintf('Could not locate a %s type.', $type));
		} elseif (!isset(self::$config[$type][$name])) {
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

		return isset(self::$config[$type], self::$config[$type][$this->alias($name)]);
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
	public function alias(string $name): string
	{
		return self::$config['alias'][strtolower($name)] ?? $name;
	}

	/**
	 * singleton
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
		/* get the CodeIgniter Super Object */
		$instance = get_instance();

		/* is there are alias for this service name NOTE: these are NOT typed */
		$singletonName = $as ?? $this->alias($serviceName);

		/* normalize the instance */
		$singletonName = strtolower($singletonName);

		/* has this service been attached yet? */
		if (!isset($instance->$singletonName)) {
			$instance->$singletonName = $this->create($serviceName, $userConfig);
		}

		/* now grab the reference */
		return $instance->$singletonName;
	}

	/**
	 * factory
	 *
	 * Create a new instance each time this is called
	 *
	 * @param string $serviceName
	 * @param mixed array
	 * @return object
	 */
	public function create(string $serviceName, array $userConfig = []): object
	{
		$instance = get_instance();

		$rawServiceName = $serviceName;

		/* is this service known by an alias? */
		$serviceName = $this->alias($serviceName);

		/* normalize the instance */
		$serviceName = strtolower($serviceName);

		$config = [];

		/* try to load it's configuration if configuration library loaded */
		if (isset($instance->config)) {
			$serviceConfig = $instance->config->item($serviceName);

			$config = (is_array($serviceConfig)) ? array_replace($serviceConfig, $userConfig) : $userConfig;
		}

		/* What is the namespaced class? */
		$serviceClass = ($this->has(self::$servicesKey, $serviceName)) ? $this->find(self::$servicesKey, $serviceName) : $rawServiceName;

		return new $serviceClass($config);
	}
} /* end class */
