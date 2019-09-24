<?php

namespace projectorangebox\orange\library;

class ServiceLocator
{
	protected $servicesConfig = [];
	protected $fileConfigs = [];

	public function __construct()
	{
		$this->loadFileConfig('services');

		$this->servicesConfig = &$this->fileConfigs['services'];
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

		$service = (isset($this->servicesConfig['services'][$key])) ? $this->servicesConfig['services'][$key] : false;

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
		return (isset($this->servicesConfig['prefixes'][$key])) ? $this->servicesConfig['prefixes'][$key] : '';
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
		$this->servicesConfig['prefixes'][$key] = $prefix;
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
		$this->servicesConfig['services'][strtolower($serviceName)] = $class;
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
		$this->servicesConfig['alias'][$alias] = $real;
	}

/**
 * serviceAlias
 *
 * @param string $name
 * @return void
 */
	function serviceAlias(string $name): string
	{
		return (isset($this->servicesConfig['alias'][$name])) ? $this->servicesConfig['alias'][$name] : $name;
	}

	/**
	 * ciSingleton
	 *
	 * $instance = ciSingleton('user',['name'=>'Johnny']);
	 * $instance = ciSingleton('auth');
	 *
	 * $instance = ciSingleton('\namespace\class');
	 * $instance = ciSingleton('\namespace\class',['name'=>'Johnny']);
	 * $instance = ciSingleton('\namespace\class',['name'=>'Johnny'],'user');
	 *
	 * @param string $name
	 * @param mixed array
	 * @param mixed string
	 * @return object
	 */
	function ciSingleton(string $name, array $userConfig = [], string $as = null): object
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
	function ciFactory(string $serviceName, array $userConfig = null): object
	{
		if (strpos($serviceName,'\\') !== false) {
			$serviceClass = $serviceName;

			$config = [];
		} else {
			$serviceClass = $this->findService($serviceName, true);

			$serviceConfig = get_instance()->config->item($serviceName);

			$config = array_replace((array) $serviceConfig, (array) $userConfig);
		}

		return new $serviceClass($config);
	}

	/* low level config */

	/**
	 *
	 * fileConfig
	 *
	 * @param string $dotNotation - config filename
	 * @param mixed return value - if none giving it will throw an error if the array key doesn't exist
	 * @return mixed - based on $default value
	 *
	 */
	function getFileConfig(string $dotNotation, $default = NOVALUE) /* mixed */
	{
		$dotNotation = strtolower($dotNotation);

		if (strpos($dotNotation, '.') === false) {
			$value = $this->loadFileConfig($dotNotation);
		} else {
			list($filename, $key) = explode('.', $dotNotation, 2);

			$array = $this->loadFileConfig($filename);

			if (!isset($array[$key]) && $default === NOVALUE) {
				throw new \Exception('Find Config Key could not locate "' . $key . '" in "' . $filename . '".');
			}

			$value = (isset($array[$key])) ? $array[$key] : $default;
		}

		return $value;
	}

	/**
	 *
	 * Low Level configuration file loader
	 * this does NOT include any database configurations
	 *
	 * @param string $filename filename
	 * @param string $variable array variable name there configuration is stored in [config]
	 *
	 * @return array
	 *
	 */
	function loadFileConfig(string $filename, bool $throwException = true, string $variableVariable = 'config'): array
	{
		$filename = strtolower($filename);

		/* did we load the file yet? */
		if (!isset($this->fileConfigs[$filename])) {
			$configFound = false;

			/* they either return something or use the CI default $config['...'] format so set those up as empty */
			$returnedApplicationConfig = $returnedEnvironmentConfig = $$variableVariable = [];

			if (file_exists(APPPATH . 'config/' . $filename . '.php')) {
				$configFound = true;
				$returnedApplicationConfig = require APPPATH . 'config/' . $filename . '.php';
			}

			if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/' . $filename . '.php')) {
				$returnedEnvironmentConfig = require APPPATH . 'config/' . ENVIRONMENT . '/' . $filename . '.php';
			}

			$this->fileConfigs[$filename] = (array) $returnedEnvironmentConfig + (array) $returnedApplicationConfig + (array) $$variableVariable;

			if (!$configFound && $throwException) {
				throw new \Exception(sprintf('Could not location a configuration file named "%s".', APPPATH . 'config/' . $filename . '.php'));
			}
		}

		return $this->fileConfigs[$filename];
	}

} /* end class */