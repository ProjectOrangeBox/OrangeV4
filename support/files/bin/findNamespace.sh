#!/usr/bin/env php
<?php

/* find anything with a namespace */

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$a = $tools->packages(true);
$b = $tools->buildRegex('(.*)\.php',true);
$c = $tools->find($b,$a);

$array = [];

foreach ($c as $match) {
	getNameSpace($match[1].'.php',$array);
}

$tools->showAsServiceArray($array,true);

function getNameSpace(string $file,array &$array): void
{
	$tokens = token_get_all(file_get_contents($file));
	$namespace = false;

	foreach ($tokens as $idx=>$token) {
		switch($token[0]) {
			case T_NAMESPACE;
				$namespaceTxt = '';

				for ($i = 1; $i <= 64; $i++) {
					$namespaceTxt .= $tokens[$idx+$i][1];
				}

				$namespaceTxt = trim($namespaceTxt);

				$namespace = substr($namespaceTxt,0,strpos($namespaceTxt,PHP_EOL));
			break;
			case T_CLASS;
				$class = $tokens[$idx+2][1];

				/* does it have a namespace? if so add it */
				if ($namespace) {
					$array[] = [strtolower($class),'\\'.$namespace.'\\'.$class];
				}

				break 2; /* break from switch and foreach */
			break;
		}
	}
}