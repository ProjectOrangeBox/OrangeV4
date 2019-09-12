#!/usr/bin/env php
<?php

define('__ROOT__',dirname(dirname(realpath($_SERVER['argv'][0]))));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$tools->showAsServiceArray($tools->processFound($tools->find($tools->buildRegex('{folder}/views/{file}\.hbs'),$tools->displayPackages()),['hbs-template-{file}','{0}']),true,true);