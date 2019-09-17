#!/usr/bin/env php
<?php

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$argv = $_SERVER['argv'];

if (count($argv) < 2) {
	echo 'Please enter folder.'.PHP_EOL;
	exit(1);
}

$folder = trim($argv[1],'/');
$prefix = isset($argv[2]) ? $argv[2] : '';

$tools = new tools;

$tools->showAsServiceArray($tools->processFound($tools->find($tools->buildRegex('{folder}/'.$folder.'/{file}\.php',true),$tools->packages(true)),[$prefix.'{file}','\{NAMESPACE}\{file}']),true);