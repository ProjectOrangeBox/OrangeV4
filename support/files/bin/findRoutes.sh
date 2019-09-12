#!/usr/bin/env php
<?php

define('__ROOT__',dirname(dirname(realpath($_SERVER['argv'][0]))));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools();

$regularEx = $tools->buildRegex('{folder}/controllers/{controller}\.php');
$matches = $tools->find($regularEx,$tools->displayPackages());
$found = $tools->processFound($matches,['{url}',"{method}'=>'{namespace}{class}"],'callback');

$tools->showAsServiceArray($found);

function callback($matches,$options,$tools) {
	list($key,$value) = $options;

	foreach (file(__ROOT__.$matches[0]) as $line) {
		if (preg_match('#(.*)@route(\s*)(?<url>\S*)(\s*)(?<method>\S*)(\s*)(?<class>\S*)(.*)#', trim($line), $m)) {
			$matches['url'] = $m['url'];

			if (empty($m['class'])) {
				$matches['class'] = $m['method'];
				$matches['method'] = 'get';
			} else {
				$matches['class'] = $m['class'];
				$matches['method'] = $m['method'];
			}

			$tools->merge($matches,$key,$value);
		}
	}
}