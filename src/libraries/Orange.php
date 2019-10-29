<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\exceptions\IO\FileNotFoundException;
use projectorangebox\orange\library\input\RequestRemap;

class Orange
{
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
	public function view(string $__view, array $__data = []): string
	{
		/* import variables into the current symbol table from an only prefix invalid/numeric variable names with _ 	*/
		extract($__data, EXTR_PREFIX_INVALID, '_');

		/* if the view isn't there then findView will throw an error BEFORE output buffering is turned on */
		$__path = __ROOT__ . ci('servicelocator')->find('view',$__view);

		/* turn on output buffering */
		ob_start();

		/* bring in the view file */
		include $__path;

		/* return the current buffer contents and delete current output buffer */
		return ob_get_clean();
	}

	/**
	 * regular expression search packages and application for files
	 *
	 * @param string $regex
	 * @return void
	 */
	public function applicationSearch(string $regex): array
	{
		$found = [];

		/* get the packages from the configuration folder autoload packages key */
		foreach ($this->getPackages() as $package) {
			$packageFolder = __ROOT__ . '/' . $package;


			if (is_dir($packageFolder)) {
				foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packageFolder)), '#^' . $regex . '$#i', \RecursiveRegexIterator::GET_MATCH) as $match) {
					if (!is_dir($match[0])) {
						$match[0] = getRootPath($match[0]);

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
	public function getPackages(): array
	{
		/* manually load because we are not using the standard $config variable to store the configuration */
		$config = \loadConfigFile('autoload', true, 'autoload');

		/* add application as package */
		array_unshift($config['packages'], 'application');

		return $config['packages'];
	}

	/**
	 * Show output in Browser Console
	 *
	 * @param mixed $var converted to json
	 * @param string $type - browser console log types [log]
	 *
	 */
	public function console(/* mixed */$var, string $type = 'log'): void
	{
		echo '<script type="text/javascript">console.' . $type . '(' . json_encode($var) . ')</script>';
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
	public function convertToReal(string $value) /* mixed */
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
					$converted = (is_float($value)) ? (float) $value : (int) $value;
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
	public function convertToString($value): string
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
	public function simplifyArray(array $array, string $key = 'id', string $value = null, string $sort = null): array
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
	public function quickMerge(string $template, array $data = []): string
	{
		if (preg_match_all('/{([^}]+)}/m', $template, $matches)) {
			foreach ($matches[1] as $key) {
				$template = str_replace('{' . $key . '}', $data[$key], $template);
			}
		}

		return $template;
	}

	/**
	 * getDotNotation
	 *
	 * @param array $array
	 * @param string $notation
	 * @param mixed $default
	 * @return void
	 */
	public function getDotNotation(array $array, string $notation, $default = null) /* mixed */
	{
		$value = $default;

		if (is_array($array) && array_key_exists($notation, $array)) {
			$value = $array[$notation];
		} elseif (is_object($array) && property_exists($array, $notation)) {
			$value = $array->$notation;
		} else {
			$segments = explode('.', $notation);

			foreach ($segments as $segment) {
				if (is_array($array) && array_key_exists($segment, $array)) {
					$value = $array = $array[$segment];
				} elseif (is_object($array) && property_exists($array, $segment)) {
					$value = $array = $array->$segment;
				} else {
					$value = $default;
					break;
				}
			}
		}

		return $value;
	}

	public function setDotNotation(array &$array, string $notation, $value): void
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

	public function datauri(string $path,bool $html = true): string
	{
		$completePath = __ROOT__.'/'.trim($path,'/');

		if (!\file_exists($completePath)) {
			throw new FileNotFoundException('Output could not locate the image at '.$path);
		}

		/* Read image path, convert to base64 encoding */
		$imageData = base64_encode(file_get_contents($completePath));

		/* Format the image SRC:  data:{mime};base64,{data}; */
		$src = 'data: '.mime_content_type($completePath).';base64,'.$imageData;

		/* Echo out a sample image */
		return ($html) ? '<img src="'.$src.'">' : $src;
	}

} /* end class */
