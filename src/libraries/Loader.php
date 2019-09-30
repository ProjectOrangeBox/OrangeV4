<?php

namespace projectorangebox\orange\library;

use CI_Loader;

class Loader extends CI_Loader
{

	public function view($__view, $__data = [], $__return = false)
	{
		/* everything inside view path */
		/* $__path = VIEWPATH.trim($__view,'/').'.php'; */

		/* everything registered in the service locator configuration file */
		$__path = __ROOT__.ci('servicelocator')->find('view',trim($__view,'/'));

		if (!\file_exists($__path)) {
			\show_error(404,'Could not find the view file '.$__view.'.');
		}

		extract($__data, EXTR_PREFIX_INVALID, '_');

		/* turn on output buffering */
		ob_start();

		/* bring in the view file */
		include $__path;

		/* get the current buffer contents and delete current output buffer */
		$__html = ob_get_clean();

		if (!$__return) {
			echo $__html;
		}

		return $__html;
	}

} /* end class */