#!/usr/bin/env php
<?php

define('__ROOT__',dirname(dirname(realpath($_SERVER['argv'][0]))));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$tools->showAsServiceArray($tools->processFound($tools->find($tools->buildRegex('{folder}validate/rules/{file}\.php'),$tools->displayPackages()),['validation_{file}','\{NAMESPACE}\{file}']),true,true);