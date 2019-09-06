<?php

use projectorangebox\orange\library\input\RequestRemap;

// namespace ;

/* static methods in global namespace */

class Orange {
	static protected $servicePrefixes = [
		'view'=>'#',
		'pear_plugin'=>'plugin_',
		'validation_rule'=>'validation_',
		'input_filter'=>'filter_',
	];

 /**
  * $fileConfigs
  *
  * @var array
  */
	static protected $fileConfigs = [];

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
	static public function loadFileConfig(string $filename,bool $throwException = true ,string $variableVariable = 'config') : array
	{
		$filename = strtolower($filename);

		if (!isset(self::$fileConfigs[$filename])) {
			$configFound = false;

			/* they either return something or use the CI default $config['...'] format so set those up as empty */
			$returnedApplicationConfig = $returnedEnvironmentConfig = $$variableVariable = [];

			if (file_exists(APPPATH.'config/'.$filename.'.php')) {
				$configFound = true;
				$returnedApplicationConfig = require APPPATH.'config/'.$filename.'.php';
			}

			if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/'.$filename.'.php')) {
				$returnedEnvironmentConfig = require APPPATH.'config/'.ENVIRONMENT.'/'.$filename.'.php';
			}

			self::$fileConfigs[$filename] = (array)$returnedEnvironmentConfig + (array)$returnedApplicationConfig + (array)$$variableVariable;

			if (!$configFound && $throwException) {
				throw new \Exception(sprintf('Could not location a configuration file named "%s".',APPPATH.'config/'.$filename.'.php'));
			}
		}

		return self::$fileConfigs[$filename];
	}

	/**
	 *
	 * fileConfig
	 *
	 * @param string $dotNotation - config filename
	 * @param mixed return value - if none giving it will throw an error if the array key doesn't exist
	 * @return mixed - based on $default value
	 *
	 */
	static public function getFileConfig(string $dotNotation, $default = NOVALUE) /* mixed */
	{
		$dotNotation = strtolower($dotNotation);

		if (strpos($dotNotation,'.') === false) {
			$value = self::loadFileConfig($dotNotation);
		} else {
			list($filename,$key) = explode('.',$dotNotation,2);

			$array = self::loadFileConfig($filename);

			if (!isset($array[$key]) && $default === NOVALUE) {
				throw new \Exception('Find Config Key could not locate "'.$key.'" in "'.$filename.'".');
			}

			$value = (isset($array[$key])) ? $array[$key] : $default;
		}

		return $value;
	}

 /**
  * findService
  *
  * @param string $serviceName
  * @param mixed bool
  * @return void
  */
	static public function findService(string $serviceName,bool $throwException = true,string $prefix = '') /* mixed false or string */
	{
		$serviceName = strtolower($serviceName);

		$services = self::loadFileConfig('services');

		$key = self::servicePrefix($prefix).$serviceName;

		$service = (isset($services[$key])) ? $services[$key] : false;

		if ($throwException && !$service) {
			throw new \Exception(sprintf('Could not locate a service named "%s".',$serviceName));
		}

		return $service;
	}

	static public function findView(string $name,bool $throwException = true)
	{
		return self::findService($name,$throwException,'view');
	}

	static public function findFilter(string $name,bool $throwException = true)
	{
		return self::findService($name,$throwException,'input_filter');
	}

	static public function findRule(string $name,bool $throwException = true)
	{
		return self::findService($name,$throwException,'validation_rule');
	}

	static public function findPear(string $name,bool $throwException = true)
	{
		return self::findService($name,$throwException,'pear_plugin');
	}

	static public function addService(string $serviceName, string $class) : void
	{
		self::$fileConfigs['services'][strtolower($serviceName)] = $class;
	}

 /**
	* named this way to match PHPs var_export
  * var_export_file
  *
  * @param string $cacheFilePath
  * @param mixed $data
  * @return void
  */
	static public function var_export_file(string $cacheFilePath,/* mixed */ $data) : bool
	{
		if (is_array($data) || is_object($data)) {
			$data = '<?php return '.str_replace(['Closure::__set_state','stdClass::__set_state'], '(object)', var_export($data, true)).';';
		} elseif (is_scalar($data)) {
			$data = '<?php return "'.str_replace('"', '\"', $data).'";';
		} else {
			throw new \Exception('Cache export save unknown data type.');
		}

		return (bool)atomic_file_put_contents($cacheFilePath, $data);
	}

	/**
	 * The most Basic MVC View loader
	 *
	 * @param string $_view view filename
	 * @param array $_data list of view variables
	 *
	 * @throws \Exception
	 *
	 * @return string
	 *
	 * @example $html = view('admin/users/show',['name'=>'Johnny Appleseed']);
	 *
	 */
	static public function view(string $__view, array $__data = []) : string
	{
		/* import variables into the current symbol table from an only prefix invalid/numeric variable names with _ 	*/
		extract($__data, EXTR_PREFIX_INVALID, '_');

		/* if the view isn't there then findView will throw an error BEFORE output buffering is turned on */
		$__path = __ROOT__.self::findView($__view,true);

		/* turn on output buffering */
		ob_start();

		/* bring in the view file */
		include $__path;

		/* return the current buffer contents and delete current output buffer */
		return ob_get_clean();
	}

 /**
  * viewServicePrefix
  *
  * @param mixed string
  * @return void
  */
	static public function servicePrefix(string $key) : string
	{
		return (isset(self::$servicePrefixes[$key])) ? self::$servicePrefixes[$key] : '';
	}

 /**
  * getAppPath
  *
  * @param string $path
  * @return void
  */
	static public function getAppPath(string $path) : string
	{
		/* remove anything below the __ROOT__ folder from the passed path */
		return (substr($path,0,strlen(__ROOT__)) == __ROOT__) ? substr($path,strlen(__ROOT__)) : $path;
	}

 /**
  * regular expression search packages and application for files
  *
  * @param string $regex
  * @return void
  */
	static public function applicationSearch(string $regex) : array
	{
		$found = [];

		/* get the packages from the configuration folder autoload packages key */
		foreach (self::getPackages() as $package) {
			$packageFolder = __ROOT__.'/'.$package;


			if (is_dir($packageFolder)) {
				foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packageFolder)),'#^'.$regex.'$#i', \RecursiveRegexIterator::GET_MATCH) as $match) {
					if (!is_dir($match[0])) {
						$match[0] = self::getAppPath($match[0]);

						$found[$match[0]] = $match;
					}
				}
			}
		}

		/* return just a numbered array */
		return $found;
	}

 /**
  * getPackages
  *
  * @return void
  */
	static public function getPackages() : array
	{
		$config = self::loadFileConfig('autoload',true,'autoload');

		/* add application as package */
		array_unshift($config['packages'],'application');

		return $config['packages'];
	}

	/**
	 * Show output in Browser Console
	 *
	 * @param mixed $var converted to json
	 * @param string $type - browser console log types [log]
	 *
	 */
	static public function console(/* mixed */ $var, string $type = 'log') : void
	{
		echo '<script type="text/javascript">console.'.$type.'('.json_encode($var).')</script>';
	}

	/**
	 * Try to convert a value to it's real type
	 * this is nice for pulling string from a database
	 * such as configuration values stored in string format
	 *
	 * @param string $value
	 *
	 * @return mixed
	 *
	 */
	static public function convertToReal(string $value) /* mixed */
	{
		$converted = $value;

		switch (trim(strtolower($value))) {
		case 'true':
			$converted = true;
			break;
		case 'false':
			$converted = false;
			break;
		case 'empty':
			$converted = '';
			break;
		case 'null':
			$converted = null;
			break;
		default:
			if (is_numeric($value)) {
				$converted = (is_float($value)) ? (float)$value : (int)$value;
			} else {
				/* if it's json this will return something other than null */
				$json = @json_decode($value, true);

				$converted = ($json !== null) ? $json : $value;
			}
		}

		return $converted;
	}

	/**
	 * Try to convert a value back into a string
	 * this is nice for storing string into a database
	 * such as configuration values stored in string format
	 *
	 * @param mixed $value
	 *
	 * @return string
	 *
	 */
	static public function convertToString($value) : string
	{
		$converted = $value;

		if (is_array($value)) {
			return str_replace('stdClass::__set_state', '(object)', var_export($value, true));
		} elseif ($value === true) {
			$converted = 'true';
		} elseif ($value === false) {
			$converted = 'false';
		} elseif ($value === null) {
			$converted = 'null';
		} else {
			$converted = (string) $value;
		}

		return $converted;
	}

	/**
	 * This will collapse a array with multiple values into a single key=>value pair
	 *
	 * @param array $array
	 * @param string $key id
	 * @param string $value null
	 * @param string $sort null
	 *
	 * @return array
	 *
	 */
	static public function simplifyArray(array $array, string $key = 'id', string $value = null, string $sort = null) : array
	{
		$value = ($value) ? $value : $key;

		$simplifiedArray = [];

		foreach ($array as $row) {
			if (is_object($row)) {
				if ($value == '*') {
					$simplifiedArray[$row->$key] = $row;
				} else {
					$simplifiedArray[$row->$key] = $row->$value;
				}
			} else {
				if ($value == '*') {
					$simplifiedArray[$row[$key]] = $row;
				} else {
					$simplifiedArray[$row[$key]] = $row[$value];
				}
			}
		}

		$sort_flags = SORT_NATURAL | SORT_FLAG_CASE;

		switch ($sort) {
			case 'desc':
			case 'd':
			case 'krsort':
				krsort($simplifiedArray, $sort_flags);
			break;
			case 'asc':
			case 'a':
			case 'ksort':
				ksort($simplifiedArray, $sort_flags);
			break;
			case 'sort':
			case 'asort':
				asort($simplifiedArray, $sort_flags);
			break;
			case 'arsort':
			case 'rsort':
				arsort($simplifiedArray, $sort_flags);
			break;
		}

		return $simplifiedArray;
	}

	/**
	 *
	 * Simple view merger
	 * replace {tags} with data in the passed data array
	 *
	 * @access
	 *
	 * @param string $template
	 * @param array $data []
	 *
	 * @return string
	 *
	 * #### Example
	 * ```
	 * $html = quick_merge('Hello {name}',['name'=>'Johnny'])
	 * ```
	 */
	static public function quickMerge(string $template, array $data = []) : string
	{
		if (preg_match_all('/{([^}]+)}/m', $template, $matches)) {
			foreach ($matches[1] as $key) {
				$template = str_replace('{'.$key.'}', $data[$key], $template);
			}
		}

		return $template;
	}

 /**
  * remapInputStream
	*
	* Preprocess the raw input stream
  *
  * @param array $rules
  * @param mixed bool
  * @return void
  */
	static public function remapInputStream(array $rules) /* mixed */
	{
		ci('input')->set_request((new RequestRemap)->processRaw($rules,ci('input')->get_raw_input_stream())->get(),true);
	}

 /**
  * getDotNotation
  *
  * @param array $array
  * @param string $notation
  * @param mixed $default
  * @return void
  */
	static public function getDotNotation(array $array,string $notation, $default = null) /* mixed */
	{
		$value = $default;

		if (is_array($array) && array_key_exists($notation,$array)) {
			$value = $array[$notation];
		} elseif (is_object($array) && property_exists($array,$notation)) {
			$value = $array->$notation;
		} else {
			$segments = explode('.',$notation);

			foreach ($segments as $segment) {
				if (is_array($array) && array_key_exists($segment,$array)) {
					$value = $array = $array[$segment];
				} elseif (is_object($array) && property_exists($array,$segment)) {
					$value = $array = $array->$segment;
				} else {
					$value = $default;
					break;
				}
			}
		}

		return $value;
	}

	static public function setDotNotation(array &$array,string $notation, $value) : void
	{
    $keys = explode('.', $notation);

		while (count($keys) > 1) {
			$key = array_shift($keys);

			if (!isset($array[$key])) {
				$array[$key] = [];
			}

			$array = &$array[$key];
    }

    $key = reset($keys);

		$array[$key] = $value;
	}

} /* end class */