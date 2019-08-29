<?php

define('NOVALUE','#PLEASE_THROW_AN_EXCEPTION#');

/* new global functions */
require 'bootstrap/Functions.php';

/* orange static functions to keep them out of "global" namespace */
require 'bootstrap/Orange.php';

/* global wrappers functions */
require 'bootstrap/Wrappers.php';

/* standard CodeIgniter */
require_once BASEPATH.'core/CodeIgniter.php';