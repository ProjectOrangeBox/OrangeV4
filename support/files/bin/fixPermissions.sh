#!/usr/bin/env php
<?php

$levelUp = (strpos($where = dirname(realpath($_SERVER['argv'][0])),'/vendor/projectorangebox/orange-v4/support/') !== false) ? 6 : 1;

define('__ROOT__',dirname($where,$levelUp));

require __ROOT__.'/vendor/projectorangebox/orange-v4/support/shell.tools.php';

$tools = new tools;

$config = __ROOT__ . '/bin/config.json';

if (!file_exists($config)) {
	$tool->error('Could not locate config file.');
}

$configObj = json_decode(file_get_contents($config));

if (!isset($configObj->chown)) {
	$tool->error('Change Owner (chown) not set in config.json');
}

if (!isset($configObj->chgrp)) {
	$tool->error('Change Group (chgrp) not set in config.json');
}

$tools->exec('find '.__ROOT__.' -type f | xargs chmod 664');
$tools->exec('find '.__ROOT__.' -type d | xargs chmod 775');

$tools->exec('find '.__ROOT__.' -type f | xargs chown '.$configObj->chown);
$tools->exec('find '.__ROOT__.' -type d | xargs chown '.$configObj->chown);

$tools->exec('find '.__ROOT__.' -type f | xargs chgrp '.$configObj->chgrp);
$tools->exec('find '.__ROOT__.' -type d | xargs chgrp '.$configObj->chgrp);

$tools->exec('find '.__ROOT__.' -name \'*.sh\' -type f | xargs chmod 775');

$tools->exec('find '.__ROOT__.'/var/* -type d | xargs chmod 777');
