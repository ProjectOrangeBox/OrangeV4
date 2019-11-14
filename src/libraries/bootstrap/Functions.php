<?php

/* you can override this function if you need to return a different server locator */
if (!function_exists('getServiceLocator')) {
	function getServiceLocator(): \projectorangebox\orange\library\ServiceLocatorInterface
	{
		return new \projectorangebox\orange\library\ServiceLocator(\loadConfigFile('services'));
	}
}

/**
 * ci
 *
 * Brand new heavy lifter
 * Supports:
 *
 * $foo = ci('factory',$myconfig,true);
 *
 * $bar = ci('bar',$myconfig);
 * $foobar = ci('foo',$myconfig,'foobar');
 * $fb = ci('fb');
 *
 * $ci = ci();
 *
 */

if (!function_exists('ci')) {
	/**
	 * ci
	 *
	 * @param mixed string
	 * @param mixed array
	 * @param mixed string | bool
	 * @return object
	 */
	function ci(string $name = null, array $userConfig = [],/* string|bool */ $as = null): object
	{
		/* i am the keeper of the service locator */
		static $serviceLocator;

		/* did we attach the service locator yet? */
		if (!$serviceLocator) {
			/* this function can be overridden if needed */
			$serviceLocator = getServiceLocator();

			if (!$serviceLocator instanceof \projectorangebox\orange\library\ServiceLocatorInterface) {
				die('Your service locator does not implement "projectorangebox\orange\library\ServiceLocatorInterface"');
			}
		}

		/* a little messy but since I control the service locator... */
		if (strtolower($name) === 'servicelocator') {
			return $serviceLocator;
		}

		/* Are we looking for a named service? factory or singleton? CodeIgniter "super" object? */
		return ($name) ? ($as === true) ? $serviceLocator->create($name, $userConfig) : $serviceLocator->get($name, $userConfig, $as) : get_instance();
	}
}

/* override the CodeIgniter loader to use composer and our services send in the file based config array */
if (!function_exists('load_class')) {
	/*
	 * CodeIgniter Startup Load Order
	 *
	 * Benchmark
	 * Exceptions
	 * Hooks
	 * Config
	 * Log
	 * Utf8
	 * URI
	 * Router
	 * Input
	 * Security
	 * Output
	 * Lang
	 * Loader
	 *
	 * load_class
	 *
	 * @param string $class
	 * @return object
	 */
	function &load_class(string $class): object
	{
		/* exists only in a local function scope */
		static $_classes = [];

		/* has it already been loaded? */
		if (!isset($_classes[$class])) {
			/**
			 * Tell CI is_loaded function
			 * so these can be attach to the Controller
			 * once it's built
			 * then they can be accessed using $this-> syntax in the controller
			 */
			is_loaded($class);

			/* this will throw an error if the service does not exist */
			$name = ci('servicelocator')->find('service', $class);

			$_classes[$class] = new $name;
		}

		return $_classes[$class];
	}
}

/**
 *
 * Orange Assertion Handler
 *
 * @param $file
 * @param $line
 * @param $code
 * @param $desc
 *
 * @return void
 *
 */
if (!function_exists('_assert_handler')) {
	function _assert_handler($file, $line, $code, $desc = ''): void
	{
		/* CLI */
		if (defined('STDIN')) {
			echo json_encode(['file' => $file, 'line' => $line, 'description' => $desc], JSON_PRETTY_PRINT);

			/* AJAX */
		} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			echo json_encode(['file' => $file, 'line' => $line, 'description' => $desc], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);

			/* HTML */
		} else {
			echo '<!doctype html><title>Assertion Failed</title>';
			echo '<style>body, html { text-align: center; padding: 150px; background-color: #492727; font: 20px Helvetica, sans-serif; color: #fff; font-size: 18px;}h1 { font-size: 150%; }article { display: block; text-align: left; width: 650px; margin: 0 auto; }</style>';
			echo '<article><h1>Assertion Failed</h1>	<div><p>File: ' . $file . '</p><p>Line: ' . $line . '</p><p>Code: ' . $code . '</p><p>Description: ' . $desc . '</p></div></article>';
		}

		exit(1);
	}
}

/**
 * get a environmental variable with support for default
 *
 * @param $key string environmental variable you want to load
 * @param $default mixed the default value if environmental variable isn't set
 *
 * @return string
 *
 * @throws \Exception
 *
 * #### Example
 * ```
 * $foo = env('key');
 * $foo = env('key2','default value');
 * ```
 */
if (!function_exists('env')) {
	function env(string $key, $default = NOVALUE) /* mixed */
	{
		if (!isset($_ENV[$key]) && $default === NOVALUE) {
			throw new \Exception('The environmental variable "' . $key . '" is not set and no default was provided.');
		}

		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}
}

/* stateless */
function stripFromStart(string $string, string $strip): string
{
	return (substr($string, 0, strlen($strip)) == $strip) ? substr($string, strlen($strip)) : $string;
}

/* stateless */
function stripFromEnd(string $string, string $strip): string
{
	return (substr($string, -strlen($strip)) == $strip) ? substr($string, 0, strlen($string) - strlen($strip)) : $string;
}

/**
 * Simple Logging function for debugging purposes
 * the file name is ALWAYS orange_debug.log
 * and saved in the paths config file log path
 *
 * @params ...mixed
 *
 * @return the number of bytes that were written to the file, or FALSE on failure.
 *
 */
if (!function_exists('l')) {
	function l(): int
	{
		/* get the number of arguments passed */
		$args = func_get_args();

		$log[] = date('Y-m-d H:i:s');

		/* loop over the arguments */
		foreach ($args as $idx => $arg) {
			/* is it's not scalar then convert it to json */
			$log[] = (!is_scalar($arg)) ? chr(9) . json_encode($arg) : chr(9) . $arg;
		}

		/* write it to the log file */
		return file_put_contents(\ci('config')->item('config.log_path') . '/orange_debug.log', implode(chr(10), $log) . chr(10), FILE_APPEND | LOCK_EX);
	}
}

if (!function_exists('site_url')) {
	/**
	 * site_url
	 * Returns your site URL, as specified in your config file.
	 * also provides auto merging of "merge" fields in {} format
	 *
	 * @param $uri
	 * @param $protocol
	 *
	 * @return
	 *
	 * #### Example
	 * ```
	 * $url = site_url('/{www theme}/assets/css');
	 * ```
	 */
	function site_url($uri = '', string $protocol = null): string
	{
		/* Call CodeIgniter version first if it has a protocol if not just use ours */
		if ($protocol) {
			$uri = ci('config')->site_url($uri, $protocol);
		}

		/* where is the cache file? */
		$cacheFilePath = ci('config')->item('config.cache_path') . 'paths.php';

		/* are we in development mode or is the cache file missing */
		if (ENVIRONMENT == 'development' || !file_exists($cacheFilePath)) {
			$array['keys'] = $array['values'] = [];

			/* build the array for easier access later */
			if (is_array($paths = config('paths', null))) {
				foreach ($paths as $find => $replace) {
					$array['keys'][] = '{' . strtolower($find) . '}';
					$array['values'][] = (substr($replace, 0, 1) == '@') ? ci('config')->item(substr($replace, 1)) : $replace;
				}
			}

			App::var_export_file($cacheFilePath, $array);
		} else {
			$array = include $cacheFilePath;
		}

		/* return the merge str replace */
		return str_replace($array['keys'], $array['values'], $uri);
	}
}

if (!function_exists('loadConfigFile')) {
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
	function loadConfigFile(string $filename, bool $throwException = true, string $variableVariable = 'config'): array
	{
		/* this "should" only have the services & base config */
		static $fileConfig;

		$filename = strtolower($filename);

		/* did we load the file yet? */
		if (!isset($fileConfig[$filename])) {
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

			$fileConfig[$filename] = (array) $returnedEnvironmentConfig + (array) $returnedApplicationConfig + (array) $$variableVariable;

			if (!$configFound && $throwException) {
				throw new \Exception(sprintf('Could not location a configuration file named "%s".', APPPATH . 'config/' . $filename . '.php'));
			}
		}

		return $fileConfig[$filename];
	}
}

if (!function_exists('configMerge')) {
	function configMerge(string $group, array $required, array &$userConfig = []): array
	{
		return ci('config')->merged($group, $required, $userConfig);
	}
}
