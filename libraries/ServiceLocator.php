<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\serviceLocator\ServiceLocator_interface;

class ServiceLocator implements ServiceLocator_interface
{
	protected $config = [];

	public function __construct(array &$config)
	{
		$this->config = &$config;
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
		$name = strtolower($name);

		if (substr($name,0,4) == 'find') {
			/* find + prefix */
			$key = $this->servicePrefix(substr($name,4),true,$arguments[0]);

			if (!isset($this->config['services'][$key])) {
				throw new \Exception(sprintf('Could not locate a service named "%s".', $arguments[0]));
			}

			return $this->config['services'][$key];
		} elseif(substr($name,0,3) === 'add') {
			/* add + prefix */
			$this->config['services'][$this->servicePrefix(substr($name,3),true,$arguments[0])] = $arguments[1];

			return;
		}

		throw new \Exception(sprintf('No method named "%s" found.', $name));
	}

	/**
	 * ServicePrefix
	 *
	 * @param mixed string
	 * @return void
	 */
	public function servicePrefix(string $key, bool $throwException = false,string $classname = ''): string
	{
		if (!isset($this->config['prefixes'][$key]) && $throwException) {
			throw new \Exception(sprintf('Service Prefix "%s" not found.', $key));
		}

		return (isset($this->config['prefixes'][$key])) ? strtolower($this->config['prefixes'][$key].$classname) : strtolower(''.$classname);
	}

/**
 * addServicePrefix
 *
 * @param string $key
 * @param string $prefix
 * @return void
 */
	public function addServicePrefix(string $key, string $prefix): void
	{
		$this->config['prefixes'][$key] = $prefix;
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
		$this->config['alias'][$alias] = $real;
	}

/**
 * serviceAlias
 *
 * @param string $name
 * @return void
 */
	public function serviceAlias(string $name): string
	{
		return (isset($this->config['alias'][$name])) ? $this->config['alias'][$name] : $name;
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

		$serviceName = ($as) ? $as : $this->serviceAlias($name);

		/* has this service been attached yet? */
		if (!isset($instance->$serviceName)) {
			$config = [];

			/* try to load it's configuration if configuration library loaded */
			if (isset($instance->config)) {
				$serviceConfig = $instance->config->item($serviceName);

				$config = (is_array($serviceConfig)) ? array_replace($serviceConfig,$userConfig) : $userConfig;
			}

			/* is it a named service? if it is use the namespaced name instead of the name sent into the function */
			if ($namedService = $this->findService($name, false)) {
				$name = $namedService;
			}

			/* try to let composer autoload load it */
			if (class_exists($name, true)) {
				/* create a new instance and attach the singleton to the CodeIgniter super object */
				$instance->$serviceName = new $name($config);
			} else {
				/*
				else try to let CodeIgniter load it the old fashion way
				using the _model suffix we can assume it's a model we are trying to load
				*/
				if (substr($name, -6) == '_model') {
					$instance->load->model($name, $serviceName);
				} else {
					/* library will take a config so let's try to find it if it exists */
					$instance->load->library($name, $config);
				}
			}
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

		$config = [];

		/* try to load it's configuration if configuration library loaded */
		if (isset($instance->config)) {
			$serviceConfig = $instance->config->item($name);

			$config = (is_array($serviceConfig)) ? array_replace($serviceConfig,$userConfig) : $userConfig;
		}

		/* is it a named service? if it is use the namespaced name instead of the name sent into the function */
		if ($namedService = $this->findService($name, false)) {
			$name = $namedService;
		}

		return new $name($config);
	}

} /* end class */