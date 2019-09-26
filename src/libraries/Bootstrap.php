<?php

define('NOVALUE','#PLEASE_THROW_AN_EXCEPTION#');

/* Orginal CI index.php */
require 'bootstrap/Index.php';

/* load the users bootstrap file if present */
if (file_exists(APPPATH.'Bootstrap.php')) {
  require APPPATH.'Bootstrap.php';
}

/* Load our Bootstrap */
require 'bootstrap/Functions.php';

/* Load the "global" namespace wrappers functions */
require 'bootstrap/Wrappers.php';

/* Standard CodeIgniter */
require_once BASEPATH.'core/CodeIgniter.php';
