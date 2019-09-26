#!/usr/bin/env php
<?php

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$a = $tools->packages(true);

$search = $_SERVER['argv'][1] ?? 'views';

$combined = [];

foreach (explode(',',$search) as $v) {
	list($folder,$ext) = explode('::',$v,2);

	$ext = ($ext) ?? '.php';

	$b = $tools->buildRegex('{folder}/'.$folder.'/{file}\\'.$ext,true);
	$c = $tools->find($b,$a);

	$combined = $combined + $c;
}

$array = [];

foreach ($combined as $match) {
	$array[] = [$match['file'],$match[0]];
}

$tools->showAsServiceArray($array,true);