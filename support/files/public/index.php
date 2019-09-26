<?php

/* Where is this application located? */
define('__ROOT__', realpath(__DIR__));

define('ORANGEPATH',__ROOT__.'/vendor/projectorangebox/orange-v4');

/* The name of THIS file */
define('SELF',pathinfo(__FILE__, PATHINFO_BASENAME));

/* This folder */
define('WWW',dirname(__FILE__));

require ORANGEPATH.'/src/libraries/Bootstrap.php';
