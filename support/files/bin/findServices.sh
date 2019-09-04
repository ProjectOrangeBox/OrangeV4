#!/usr/bin/env php
<?php

ini_set('display_errors', 1);

define('__ROOT__',realpath(''));

chdir(__ROOT__);

define('BASEPATH',__ROOT__);

/* .env file */
if (!file_exists('.env')) {
	echo getcwd().'/.env file missing.';
	exit(1); // EXIT_ERROR
}

/* bring in the system .env files */
$_ENV = array_merge($_ENV,parse_ini_file('.env',true,INI_SCANNER_TYPED));

define('APPPATH',__ROOT__.'/application/');
define('ENVIRONMENT', isset($_ENV['CI_ENV']) ? $_ENV['CI_ENV'] : 'development');

require __ROOT__.'/packages/projectorangebox/orange/libraries/bootstrap/Functions.php';
require __ROOT__.'/packages/projectorangebox/orange/libraries/bootstrap/Orange.php';

echo 'Application Root: '.PHP_EOL.__ROOT__.PHP_EOL.PHP_EOL;

echo 'Searching:'.PHP_EOL;

foreach (\orange::getPackages() as $package) {
	echo '/'.$package.PHP_EOL;
}

echo PHP_EOL.'Found:'.PHP_EOL;
echo '-- Cut & Paste as needed --'.PHP_EOL;
echo PHP_EOL;

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if ($argc > 1) {
	$arg1 = (isset($argv[1])) ? $argv[1] : '';
	$arg2 = (isset($argv[2])) ? $argv[2] : '';
	$arg3 = (isset($argv[3])) ? $argv[3] : '';

	find($arg1,$arg2,$arg3);
} else {
	find('validate/filters','input_filter','Input Filters');
	find('pear_plugins','pear_plugin','Pear Plugins');
	find('validate/rules','validation_rule','Validation Rules');
	find('views','view','Views',function($file,$prefixKey) {
		if (preg_match('%(.*)/views/(.*).php%',$file, $matches, PREG_OFFSET_CAPTURE, 0)) {
			echo "'".\orange::servicePrefix('view').strtolower($matches[2][0])."' => '".$matches[0][0]."',".PHP_EOL;
		}
	});
}

function find(string $folder,string $prefixKey,string $tile,closure $closure = null) {
	echo '/* '.$tile.' */'.PHP_EOL;

	$folder = trim($folder,'/');

	$closure = ($closure) ? $closure : function($file,$prefixKey) {
		if (preg_match('/namespace (.*);/m', file_get_contents(__ROOT__.$file), $matches, PREG_OFFSET_CAPTURE, 0)) {
			echo "'".\orange::servicePrefix($prefixKey).strtolower(basename($file,'.php'))."' => '".'\\'.$matches[1][0].'\\'.basename($file,'.php')."',".PHP_EOL;
		}
	};

	foreach (\orange::applicationSearch('(.*)/'.$folder.'/(.*)\.php') as $file) {
		$closure($file,$prefixKey);
	}

	echo PHP_EOL;
}