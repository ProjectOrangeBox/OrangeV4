<?php

/* Where is this application located? */
define('__ROOT__', realpath(__DIR__));

/* Where is orange located? we need this to bootstrap the system. */
define('ORANGEPATH',__ROOT__.'/vendor/projectorangebox/orange-v4');

/* What is the name of THIS file */
define('SELF',pathinfo(__FILE__, PATHINFO_BASENAME));

/* Where is the WWW folder (ie usually this folder) */
define('WWW',dirname(__FILE__));

/* bootstrap */
require ORANGEPATH.'/src/libraries/Bootstrap.php';
