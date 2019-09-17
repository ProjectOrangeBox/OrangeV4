#!/usr/bin/env php
<?php

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools();

$tools->showAsServiceArray($tools->processFound($tools->find($tools->buildRegex('{folder}/controllers/{controller}\.php',true),$tools->packages(true)),['{url}',"{method}'=>'{class}"]));

function processMatch($matches,$options,$tools) {
	$tag = '@route ';

	foreach (file(__ROOT__.$matches[0]) as $line) {
		if (strpos($line,$tag) !== false) {
			list($arrayKey,$arrayValue) = $options;

			$parts = explode(' ',trim(substr($line,strpos($line,$tag)+strlen($tag))));

			$matches['url'] = $parts[0];
			$matches['method'] = $parts[1];
			$matches['class'] = $parts[2];

			if (count($parts) == 2) {
				$matches['class'] = $parts[1];
				$matches['method'] = 'get';
			}

			$matches['method'] = strtolower($matches['method']);

			/* change the template if the method is get since it's the default */
			if ($matches['method'] == 'get') {
				$arrayValue = '{class}';
			}

			/**
			 * this handles * as the controller
			 * @route /example/handlebars post *::compileCliAction
			 */
			$matches['class'] = str_replace('*',substr($matches[0],0,-4),$matches['class']);

			$tools->addProcessed($tools->merge($matches,$arrayKey,$arrayValue));
		}
	}
}
