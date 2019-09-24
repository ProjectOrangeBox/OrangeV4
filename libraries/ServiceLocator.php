<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\serviceLocator\ServiceLocator_interface;

class ServiceLocator implements ServiceLocator_interface
{
	protected $config = [];

	public function __construct()
	{
		/* force load the services config file into the config array */
		$this->config = \loadConfigFile('services');
	}

	/**
	 * findService
	 *
	 * @param string $serviceName
	 * @param mixed bool
	 * @return void
	 */
	function findService(string $serviceName, bool $throwException = true, string $prefix = '') /* mixed false or string */
	{
		/* normalize */
		$serviceName = strtolower($serviceName);

		$key = $this->servicePrefix($prefix) . $serviceName;

		$service = (isset($this->config['services'][$key])) ? $this->config['services'][$key] : false;

		if ($throwException && !$service) {
			throw new \Exception(sprintf('Could not locate a service named "%s".', $serviceName));
		}

		return $service;
	}

	/**
	 * ServicePrefix
	 *
	 * @param mixed string
	 * @return void
	 */
	function servicePrefix(string $key): string
	{
		return (isset($this->config['prefixes'][$key])) ? $this->config['prefixes'][$key] : '';
	}

/**
 * addServicePrefix
 *
 * @param string $key
 * @param string $prefix
 * @return void
 */
	function addServicePrefix(string $key, string $prefix): void
	{
		$this->config['prefixes'][$key] = $prefix;
	}

/**
 * addService
 *
 * @param string $serviceName
 * @param string $class
 * @return void
 */
	function addService(string $serviceName, string $class): void
	{
		$this->config['services'][strtolower($serviceName)] = $class;
	}

/**
 * addAlias
 *
 * @param string $alias
 * @param string $real
 * @return void
 */
	function addAlias(string $alias, string $real): void
	{
		$this->config['alias'][$alias] = $real;
	}

/**
 * serviceAlias
 *
 * @param string $name
 * @return void
 */
	function serviceAlias(string $name): string
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
	function singleton(string $name, array $userConfig = [], string $as = null): object
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
	function factory(string $name, array $userConfig = []): object
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