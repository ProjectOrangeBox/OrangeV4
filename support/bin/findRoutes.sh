#!/usr/bin/env php
<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);


define('__ROOT__',realpath(''));

echo __ROOT__.PHP_EOL;

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

echo 'Application Root: '.__ROOT__.PHP_EOL.PHP_EOL;

echo 'Searching:'.PHP_EOL;

foreach (\orange::getPackages() as $package) {
	echo '/'.$package.PHP_EOL;
}

echo PHP_EOL;

echo '-- Cut & Paste as needed --'.PHP_EOL.PHP_EOL;


/**
 *
 * @httpPost 'snakes/([a-zA-Z]+)/edit/(\d+)' => 'forder1\folder2\admin\welcome::index'
 *
 * $route['snakes/([a-zA-Z]+)/edit/(\d+)']['post'] = 'forder1\folder2\admin\welcome::index';
 *
 * Auto replace URL with the folder path inside the controllers folder
 * Auto replace Controller with the Directory path based on the ROOT level unless it is in application
 * Auto replace Method with the next found public method
 *
 * @httpGet ~ => *::*
 * @httpPost
 * @httpDelete
 * @httpPut
 * @httpPatch
 *
 * @cli
 *
 *
 */

foreach (\orange::applicationSearch('(.*)/controllers/(.*)\.php') as $file) {
	process($file,'controller');
}

echo PHP_EOL;

exit(1);

function process(string $realPath,string $stripFromControllerName) : void
{
	$last = '';
	$lines = file(__ROOT__.$realPath);

	foreach ($lines as $line) {

		if (preg_match_all('%[^\@]+@(http|cli)(\S*) (\S*) => (\S*)%i',$line, $match, PREG_SET_ORDER, 0)) {
			/* found another route */
			println($last);

			$pathinfo = pathinfo($realPath);

			$request = (strtolower($match[0][1]) == 'http') ? strtolower($match[0][2]) : strtolower($match[0][1]);
			$url = $match[0][3];

			if ($url == '~') {
				$controllersFolder = '/controllers/';

				$new = strtolower($pathinfo['dirname'].'/'.$pathinfo['filename']);

				if (substr($new,-strlen($stripFromControllerName)) == $stripFromControllerName) {
					$new = substr($new,0,-strlen($stripFromControllerName));
				}

				$url = substr($new,strpos($new,$controllersFolder) + strlen($controllersFolder)).'~';
			}

			$controller = $match[0][4];

			$directory = ($pathinfo['dirname'] != '/application/controllers') ? $pathinfo['dirname'].'/'.$pathinfo['filename'] : $pathinfo['filename'];

			$controller = str_replace('*:',str_replace('/','\\',$directory).':',$controller);

			$last = format($request,$url,$controller);
		}

		if (preg_match('%(\s*)public(\s*)function(\s*)([a-z0-9_-]*)%i', $line, $match, PREG_OFFSET_CAPTURE, 0)) {
			$method = $match[4][0];

			$last = str_replace('::*','::'.$method,$last);
			$last = ($method == 'index') ? str_replace('~','',$last) : str_replace('~','/'.$method,$last);
		}
	}

	/* do we have a route still in there? */
	println($last);
}

function format(string $request,string $url,string $controller) : string
{
	/*
	'form/input' => 'FormController::index',
	'products/(:num)' => ['DelEte' => 'product/delete/$1'],
	*/

	return ($request == 'get') ? sprintf("'%s' => '%s',",$url,$controller) : sprintf("'%s' => ['%s'=>'%s'],",$url,$request,$controller);
}

function println(string &$last) : void
{
	if (!empty($last)) {
		echo $last.PHP_EOL;

		$last = '';
	}
}