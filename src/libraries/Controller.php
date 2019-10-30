<?php

namespace projectorangebox\orange\library;

use CI_Controller;

class Controller extends CI_Controller
{

	public function __construct()
	{
		/* let the parent controller do it's work */
		parent::__construct();

		/**
		 * Is the site up?
		 *
		 * If this is a cli request then it's always up
		 * If they have ISOPEN cookie matching application.is open cookie configuration value then continue processing
		 * Else Show the 503 server down error page
		 *
		 */
		if (!ci('input')->is_cli_request()) {
			if (!config('application.site open', true)) {
				if ($_COOKIE['ISOPEN'] !== config('application.is open cookie', md5(uniqid(true)))) {
					$this->errors->display(503, ['heading' => 'Please Stand By', 'message' => 'Site Down for Maintenance']);
				}
			}
		}

		ci('router')->onRequest($this->input);
	}

	public function _output($output)
	{
		/* we need to create a variable to pass by reference */
		$output = (string) $output;

		ci('router')->onResponse($output);

		echo $output;
	}
} /* end class */
