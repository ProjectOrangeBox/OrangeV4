<?php

/* Composer Autoloader NOT loaded at this point */

define('NOVALUE', '#PLEASE_THROW_AN_EXCEPTION#');

/* Bring the Application root path file traits in */
require __DIR__ . '/FS.php';

/* set the root folder path */
FS::setRoot(__ROOT__, true);

/* Orginal CI index.php - except last line - see below */
require __DIR__ . '/bootstrap/Index.php';

/* load the users ENVIRONMENT bootstrap file if present */
if (file_exists(APPPATH . 'Bootstrap.' . ENVIRONMENT . '.php')) {
	require APPPATH . 'Bootstrap.' . ENVIRONMENT . '.php';
}

/* load the users bootstrap file if present */
if (file_exists(APPPATH . 'Bootstrap.php')) {
	require APPPATH . 'Bootstrap.php';
}

/* Load our Bootstrap */
require __DIR__ . '/bootstrap/Functions.php';

/* Load the "global" namespace wrappers functions */
require __DIR__ . '/bootstrap/Wrappers.php';

/* Standard CodeIgniter */
require_once BASEPATH . 'core/CodeIgniter.php';

/* Composer Autoloader in now loaded */
