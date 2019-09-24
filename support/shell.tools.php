<?php

class tools
{
	protected $regex;
	protected $merge;
	protected $callback;
	protected $foreground_colors = [];
	protected $processed = [];

	public function __construct()
	{
		// Set up shell colors
		$this->foreground_colors['off'] = '0;0';

		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		ini_set('display_errors', 1);
		error_reporting(E_ALL ^ E_NOTICE);

		chdir(__ROOT__);

		define('BASEPATH', __ROOT__);

		/* .env file */
		if (!file_exists('.env')) {
			$this->error(getcwd() . '/.env file missing.');
		}

		/* bring in the system .env files */
		$_ENV = array_merge($_ENV, parse_ini_file('.env', true, INI_SCANNER_TYPED));

		define('APPPATH', __ROOT__ . '/application/');
		define('ENVIRONMENT', isset($_ENV['CI_ENV']) ? $_ENV['CI_ENV'] : 'development');

		$this->println('Application Root');
		$this->println(__ROOT__);
	}

	public function println(string $input = '', bool $die = false, $stream = STDOUT): void
	{
		$this->print($input . PHP_EOL, $die, $stream);
	}

	/* STDOUT or STDERR */
	public function print(string $input, bool $die = false, $stream = STDOUT): void
	{
		foreach ($this->foreground_colors as $color => $console) {
			$input = str_replace('<' . $color . '>', "\033[" . $console . "m", $input);
			$input = str_replace('</' . $color . '>', "\033[0m", $input);
		}

		fwrite($stream, $input);

		if ($die) {
			exit(0);
		}
	}

	public function error(string $input, bool $die = true): void
	{
		$this->println('<light_red>' . $input, $die, STDERR);
	}

	public function processFound(array $found, array $options): array
	{
		foreach ($found as $matches) {
			if (function_exists('processMatch')) {
				processMatch($matches, $options, $this);
			} else {
				$this->addProcessed($this->merge($matches, $options[0], $options[1]));
			}
		}

		return $this->processed;
	}

	public function showAsServiceArray(array $array, bool $convertNamespace = false, string $sort = 'none'): void
	{
		$this->println("Found");
		$this->println("-- Cut & Paste as needed --");
		$this->println();

		switch ($sort) {
			case 'asort':
			case 'value':
				asort($array);
				break;
			case 'ksort':
			case 'key':
				ksort($array);
				break;
		}

		foreach ($array as $mixed) {
			list($key, $value) = $mixed;

			echo "'" . strtolower($key) . "' => ";

			if ($convertNamespace) {
				$value = str_replace('/', '\\', $value);
			}

			echo (strpos($value, '=>') !== false) ? "['" . $value . "']" : "'" . $value . "'";

			echo "," . PHP_EOL;
		}

		$this->println();
	}

	public function buildRegex(string $regex, bool $displayOutput = false): string
	{
		$regexMatch = str_replace('{', '(?<', $regex);
		$regexMatch = str_replace('}', '>.*)', $regexMatch);

		if ($displayOutput) {
			$this->println();
			$this->println("Matching files against the regular expression");
			$this->println($regexMatch);
			$this->println();
		}

		return $regexMatch;
	}

	public function packages(bool $displayOutput = false): array
	{
		$config = __ROOT__ . '/bin/config.json';

		if ($displayOutput) {
			$this->println('Searching the following "packages"');
			$this->println('These are loaded from the ' . $config . ' file');
		}

		if (!file_exists($config)) {
			$this->error('Could not locate config file.');
		}

		$configObj = json_decode(file_get_contents($config));

		if (!is_array($configObj->search)) {
			$this->error('Search path json error.');
		}

		if ($displayOutput) {
			foreach ($configObj->search as $package) {
				$this->println('../' . $package);
			}

			$this->println();
		}

		return $configObj->search;
	}

	public function find(string $regex, array $paths): array
	{
		$found = [];

		/* get the packages from the configuration folder autoload packages key */
		foreach ($paths as $package) {
			$packageFolder = __ROOT__ . '/' . $package;

			if (is_dir($packageFolder)) {
				foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packageFolder)), '#^' . $regex . '$#i', \RecursiveRegexIterator::GET_MATCH) as $match) {
					if (!is_dir($match[0])) {
						$match[0] = $this->getRootPath($match[0]);
						$match['package'] = $package;

						$found[$match[0]] = $match;
					}
				}
			} else {
				$this->error('Could not locate '.$packageFolder);
			}
		}

		/* return just a numbered array */
		return $found;
	}

	public function merge(array $mergeData, string $keyMerge, string $valueMerge): array
	{
		$mergeData['APPPATH'] = APPPATH;
		$mergeData['ROOT'] = __ROOT__;
		$mergeData['NAMESPACE'] = $this->getNamespace($mergeData[0]);
		$zero = chr(0);

		$text = $keyMerge . $zero . $valueMerge;

		if (preg_match_all('/{([^}]+)}/m', $text, $matches)) {
			foreach ($matches[1] as $key) {
				if (strpos($key, '|') !== false) {
					$filters = explode('|', $key);
					$newkey = array_pop($filters);
					$value = $mergeData[$newkey];

					foreach ($filters as $filter) {
						$value = $filter($value);
					}
				} else {
					$value = $mergeData[$key];
				}

				$text = str_replace('{' . $key . '}', $value, $text);
			}
		}

		return explode($zero, $text, 2);
	}

	public function addProcessed($mixed): void
	{
		$this->processed[] = $mixed;
	}

	public function getRootPath(string $path): string
	{
		/* remove anything below the __ROOT__ folder from the passed path */
		return (substr($path, 0, strlen(__ROOT__)) == __ROOT__) ? substr($path, strlen(__ROOT__)) : $path;
	}

	public function getNamespace(string $filepath): string
	{
		$namespace = '';

		if (preg_match('/namespace (?<namespace>.*);/m', file_get_contents(__ROOT__ . $filepath), $matches)) {
			$namespace = $matches['namespace'];
		}

		return trim($namespace);
	}
} /* end class */
