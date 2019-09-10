<?php

class tools {
	protected $regex;
	protected $merge;
	protected $callback;
	protected $foreground_colors = [];

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

		define('BASEPATH',__ROOT__);

		/* .env file */
		if (!file_exists('.env')) {
			$this->error(getcwd().'/.env file missing.');
		}

		/* bring in the system .env files */
		$_ENV = array_merge($_ENV,parse_ini_file('.env',true,INI_SCANNER_TYPED));

		define('APPPATH',__ROOT__.'/application/');
		define('ENVIRONMENT', isset($_ENV['CI_ENV']) ? $_ENV['CI_ENV'] : 'development');

		$this->println('Application Root');
		$this->println(__ROOT__);
	}

	public function println(string $input = '',bool $die = false,$stream = STDOUT) {
		return $this->print($input.PHP_EOL,$die,$stream);
	}

	/* STDOUT or STDERR */
	public function print(string $input,bool $die = false,$stream = STDOUT) {
		foreach ($this->foreground_colors as $color=>$console) {
			$input = str_replace('<'.$color.'>',"\033[".$console."m",$input);
			$input = str_replace('</'.$color.'>',"\033[0m",$input);
		}

		fwrite($stream, $input);

		if ($die) {
			exit(0);
		}
	}

	public function error(string $input,bool $die = true) {
		return $this->println('<light_red>'.$input,$die,STDERR);
	}

	public function find(string $regex,string $merge,Closure $callback = null) {
		$defaultClosure = function(array $matches,string $merge,tools $that) : void {
			$that->merge($matches,$merge);
		};

		$callback = ($callback) ?? $defaultClosure;

		$searchPaths = $this->displayPackages();

		$regex = $this->buildRegex($regex);

		$this->println("Matching files against the regular expression");
		$this->println($regex);
		$this->println("Merge");
		$this->println($merge);

		$this->println("Found");
		$this->println("-- Cut & Paste as needed --");
		$this->println();

		foreach ($this->applicationSearch($regex,$searchPaths) as $matches) {
			$callback($matches,$merge,$this);
		}

		$this->println();
	}

	public function buildRegex(string $regex) : string
	{
		$regexMatch = str_replace('{','(?<',$regex);
		$regexMatch = str_replace('}','>.*)',$regexMatch);

		return $regexMatch;
	}

	public function displayPackages() : array
	{
		$searchPathsFile = __ROOT__.'/bin/searchPaths.json';

		$this->println('Searching the following "packages"');
		$this->println('These are loaded from the '.$searchPathsFile.' file');

		if (!file_exists($searchPathsFile)) {
			$this->error('Could not locate the search path JSON file.');
		}

		$searchPaths = json_decode(file_get_contents($searchPathsFile),true,8);

		if (!is_array($searchPaths)) {
			$this->error('Search path json error.');
		}

		foreach ($searchPaths as $package) {
			$this->println('../'.$package);
		}

		$this->println();

		return $searchPaths;
	}

	public function applicationSearch(string $regex, array $paths) : array
	{
		$found = [];

		/* get the packages from the configuration folder autoload packages key */
		foreach ($paths as $package) {
			$packageFolder = __ROOT__.'/'.$package;


			if (is_dir($packageFolder)) {
				foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($packageFolder)),'#^'.$regex.'$#i', \RecursiveRegexIterator::GET_MATCH) as $match) {
					if (!is_dir($match[0])) {
						$match[0] = $this->getAppPath($match[0]);

						$found[$match[0]] = $match;
					}
				}
			}
		}

		/* return just a numbered array */
		return $found;
	}

	public function merge(array $mergeData,string $text)
	{
		$mergeData['APPPATH'] = APPPATH;
		$mergeData['ROOT'] = __ROOT__;
		$mergeData['NAMESPACE'] = $this->getNamespace($mergeData[0]);

		if (preg_match_all('/{([^}]+)}/m', $text, $matches)) {
			foreach ($matches[1] as $key) {
				if (strpos($key,'|') !== false) {
					$filters = explode('|',$key);
					$newkey = array_pop($filters);
					$value = $mergeData[$newkey];

					foreach ($filters as $filter) {
						$value = $filter($value);
					}
				} else {
					$value = $mergeData[$key];
				}

				$text = str_replace('{'.$key.'}',$value,$text);
			}
		}

		$this->println($text);
	}

	public function getAppPath(string $path) : string
	{
		/* remove anything below the __ROOT__ folder from the passed path */
		return (substr($path,0,strlen(__ROOT__)) == __ROOT__) ? substr($path,strlen(__ROOT__)) : $path;
	}

	public function getNamespace(string $filepath) : string
	{
		$namespace = '';

		if (preg_match('/namespace (?<namespace>.*);/m', file_get_contents(__ROOT__.$filepath), $matches)) {
			$namespace = $matches['namespace'];
		}

		return trim($namespace);
	}

} /* end class */