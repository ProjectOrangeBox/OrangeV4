<?php

namespace projectorangebox\orange\library;

use Exception;
use projectorangebox\orange\library\ServiceLocatorInterface;
use projectorangebox\orange\library\exceptions\Internal\MethodNotFoundException;
use projectorangebox\orange\library\exceptions\MVC\ServiceException;

class ServiceLocator implements ServiceLocatorInterface
{
	static protected $config = [];

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
	 */
	public function __call(string $name,array $arguments)
	{
		$return = true;

		$name = strtolower($name);

		if (substr($name,0,4) == 'find') {
			$return = $this->find(substr($name,4),$arguments[0]);
		} elseif(substr($name,0,3) === 'add') {
			$return = $this->add(substr($name,3),$arguments[0],$arguments[1]);
		}	else {
			throw new MethodNotFoundException(sprintf('No method named "%s" found.', $name));
		}

		return $return;
	}

	public function find(string $type,string $name): string
	{
		$type = strtolower($type);
		$name = strtolower($name);

		if (!isset(self::$config[$type])) {
			throw new ServiceException(sprintf('Could not locate a %s type.', $type));
		}

		if (!isset(self::$config[$type][$name])) {
			throw new ServiceException(sprintf('Could not locate a %s type named %s.',$type,$name));
		}

		return self::$config[$type][$name];
	}

	public function add(string $type,string $name,string $serviceClass): bool
	{
		$type = strtolower($type);
		$name = strtolower($name);

		if (!isset(self::$config[$type])) {
			self::$config[$type] = [];
		}

		self::$config[$type][$name] = $serviceClass;

		return true;
	}

/**
 * addAlias
 *
 * @param string $alias
 * @param string $real
 * @return void
 */
	public function addAlias(string $alias, string $real): void
	{
		self::$config['alias'][strtolower($alias)] = $real;
	}

/**
 * serviceAlias
 *
 * @param string $name
 * @return void
 */
	public function alias(string $name): string
	{
		return self::$config['alias'][strtolower($name)] ?? $name;
	}

	/**
	 * singleton
	 *
	 * $instance = singleton('user',['name'=>'Johnny']);
	 * $instance = singleton('auth');
	 *
	 * $instance = singleton('\namespace\class');
	 * $instance = singleton('\namespace\class',['name'=>'Johnny']);
	 * $instance = singleton('\namespace\class',['name'=>'Johnny'],'user');
	 *
	 * @param string $name
	 * @param mixed array
	 * @param mixed string
	 * @return object
	 */
	public function get(string $name, array $userConfig = [], string $as = null): object
	{
		$instance = get_instance();

		$serviceName = ($as) ? $as : $this->alias($name);

		/* has this service been attached yet? */
		if (!isset($instance->$serviceName)) {
			$instance->$serviceName = $this->create($serviceName,$userConfig);
		}

		/* now grab the reference */
		return $instance->$serviceName;
	}

	/**
	 * ciFactory
	 *
	 * @param string $serviceName
	 * @param mixed array
	 * @return object
	 */
	public function create(string $name, array $userConfig = []): object
	{
		$instance = get_instance();

		$name = $this->alias($name);

		$config = [];

		/* try to load it's configuration if configuration library loaded */
		if (isset($instance->config)) {
			$serviceConfig = $instance->config->item($name);

			$config = (is_array($serviceConfig)) ? array_replace($serviceConfig,$userConfig) : $userConfig;
		}

		try {
			$serviceClass = $this->findService($name, false);
		} catch (Exception $e) {
			$serviceClass = $name;
		}

		return new $serviceClass($config);
	}

} /* end class */