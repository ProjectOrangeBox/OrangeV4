<?php

define('NOVALUE','#PLEASE_THROW_AN_EXCEPTION#');

/* load the users bootstrap file if present */
if (file_exists(APPPATH.'Bootstrap.php')) {
  require APPPATH.'Bootstrap.php';
}

/* new global functions */
require 'bootstrap/Functions.php';

/* global wrappers functions */
require 'bootstrap/Wrappers.php';

/* standard CodeIgniter */
require_once BASEPATH.'core/CodeIgniter.php';