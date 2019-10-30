<?php

define('NOVALUE', '#PLEASE_THROW_AN_EXCEPTION#');

/* Orginal CI index.php */
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

/* static wrapper for file functions based on Application Root (__ROOT__) */
require __DIR__ . '/bootstrap/App.php';

/* Standard CodeIgniter */
require_once BASEPATH . 'core/CodeIgniter.php';
