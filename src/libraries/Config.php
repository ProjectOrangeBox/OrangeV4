<?php

namespace projectorangebox\orange\library;

use CI_Config;
use projectorangebox\orange\library\exceptions\MVC\ConfigException;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Extension to CodeIgniter Config Class
 *
 * `dot_item_lookup($keyvalue,$default)` lookup configuration using dot notation with optional default
 *
 * `set_dot_item($name,$value)` set non permanent value in config
 *
 * `flush()` flush the cached configuration
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses # SettingModel - Orange Settings Model Service
 * @uses # export cache - Orange Export Cache
 * @uses # load_config() - Orange Config File Loader
 * @uses # convert_to_real() - Orange convert string values into PHP real values where possible
 *
 * @config no_database_settings boolean
 *
 */

class Config extends CI_Config
{
	protected $fileCache = [];
	protected $fileLoaded = false;
	protected $databaseLoaded = false;

	/**
	 * $hasDatabase
	 *
	 * @var mixed string|bool
	 */
	protected $hasDatabase = false;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		if (isset($this->config['database_settings']) && $this->config['database_settings'] !== false) {
			$this->hasDatabase = $this->config['database_settings'];
		}

		log_message('info', 'Orange Config Class Initialized');
	}

	/**
	 * override parent
	 *
	 * Fetch a config file item
	 *
	 * @param	string	$item	Config item name
	 * @param	string	$index	Index name
	 * @return	string|null	The configuration item or NULL if the item doesn't exist
	 */
	public function item($item, $index = '')
	{
		$this->_lazyLoad();

		return (\strpos($item, '.') !== false) ? ci('orange')->getDotNotation($this->config, $item, $index) : parent::item($item, $index);
	}

	/**
	 * override parent
	 *
	 * Set a config file item
	 *
	 * @param	string	$item	Config item key
	 * @param	string	$value	Config item value
	 * @return	void
	 */
	public function set_item($item, $value)
	{
		$this->_lazyLoad();

		return (\strpos($item, '.') !== false) ? ci('orange')->setDotNotation($this->config, $item, $value) : parent::set_item($item, $value);
	}

	/**
	 *
	 * Flush the cached data for the NEXT request
	 *
	 * @access public
	 *
	 * @throws
	 * @return bool
	 *
	 */
	public function flush(bool $clearThisSession = false): bool
	{
		log_message('debug', 'Config::flush');

		/* delete the database configs if they are there */
		$cacheDatabaseFilePath = $this->getCacheFilePath('database');

		if (\file_exists($cacheDatabaseFilePath)) {
			\unlink($cacheDatabaseFilePath);

			if ($clearThisSession) {
				$this->databaseLoaded = false;
			}
		}

		$cacheFilePath = $this->getCacheFilePath('file');

		/* delete the file configs */
		if ($clearThisSession) {
			$this->fileLoaded = false;
		}

		return (\file_exists($cacheFilePath)) ? \unlink($cacheFilePath) : true;
	}

	/**
	 *
	 * Load the combined Application, Environmental, Database Configuration values
	 *
	 * @access protected
	 *
	 * @return void
	 *
	 */
	/**
	 * _lazyLoad
	 *
	 * @return void
	 */
	protected function _lazyLoad(): void
	{
		if (!$this->fileLoaded) {
			$this->config = \array_replace($this->config, $this->getFileConfig());
		}

		/* if this has a database model and the database is attached to CI then we can load again this time with the database */
		if ($this->hasDatabase && function_exists('DB') && !$this->databaseLoaded) {
			$this->config = \array_replace($this->config, $this->getDatabaseConfig());
		}
	}

	/**
	 * getFileConfig
	 *
	 * @return void
	 */
	protected function getFileConfig(): array
	{
		/* this keeps it from looping */
		$this->fileLoaded = true;

		$fileConfig = [];

		$cacheFilePath = $this->getCacheFilePath('file');

		if (ENVIRONMENT == 'development' || !file_exists($cacheFilePath)) {
			/**
			 * The application config folder has 1 of every
			 * known config file so using this and a combination of
			 * loadConfig we can as load the environmental
			 * configuration files
			 */
			foreach (glob(APPPATH . '/config/*.php') as $filepath) {
				$basename = basename($filepath, '.php');

				foreach (\loadConfigFile($basename) as $key => $value) {
					/* normalize */
					$fileConfig[$this->_normalizeGroup($basename)][$this->_normalizeKey($key)] = $value;
				}
			}

			\FS::var_export_file($cacheFilePath, $fileConfig);
		} else {
			$fileConfig = include $cacheFilePath;
		}

		return $fileConfig;
	}

	/**
	 * getCacheFilePath
	 *
	 * @param string $type
	 * @return void
	 */
	protected function getCacheFilePath(string $type): string
	{
		return $this->config['cache_path'] . ENVIRONMENT . '.config.' . $type . '.php';
	}

	/**
	 * getDatabaseConfig
	 *
	 * @return void
	 */
	protected function getDatabaseConfig(): array
	{
		/* this keeps it from looping */
		$this->databaseLoaded = true;

		$databaseConfig = [];

		$cacheFilePath = $this->getCacheFilePath('database');

		if (ENVIRONMENT == 'development' || !file_exists($cacheFilePath)) {
			$config = (new $this->hasDatabase)->get_enabled();

			if (is_array($config)) {
				foreach ($config as $record) {
					$databaseConfig[$this->_normalizeGroup($record->group)][$this->_normalizeKey($record->name)] = ci('orange')->convertToReal($record->value);
				}
			}

			\FS::var_export_file($cacheFilePath, $databaseConfig);
		} else {
			$databaseConfig = include $cacheFilePath;
		}

		return $databaseConfig;
	}

	/**
	 * _normalize
	 *
	 * @param string $string
	 * @return void
	 */
	protected function _normalizeKey(string $string): string
	{
		return strtolower($string);
	}

	protected function _normalizeGroup(string $groupName): string
	{
		return str_replace(' ', '_', strtolower($groupName));
	}

	/**
	 * merged - can be used buy the class constructs to load & check required with defaults configuration
	 *
	 * @param string $group
	 * @param array $required
	 * @param mixed array
	 * @return void
	 */
	public function merged(string $group, array $required, array $userConfig = []): array
	{
		$config = parent::item($group);

		if (!\is_array($config)) {
			$config = [];
		}

		$config = \array_replace($config, $userConfig);

		foreach ($required as $name => $default) {
			if (\is_integer($name)) {
				$name = $default;
				$default = null;
			}

			if (!isset($config[$name])) {
				if ($default === null) {
					throw new ConfigException('Could not locate a configuration value ' . $group . '.' . $name . ' and no default was provided.');
				}

				$config[$name] = $default;
			}
		}

		return $config;
	}
} /* end class */
