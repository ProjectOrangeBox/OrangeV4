#!/usr/bin/env php
<?php

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$tools->showAsServiceArray($tools->processFound($tools->find($tools->buildRegex('{folder}/pear_plugins/{file}\.php',true),$tools->packages(true)),['plugin_{file}','\{NAMESPACE}\{file}']),true);