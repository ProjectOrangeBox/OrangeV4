<?php

namespace projectorangebox\orange\library;

use CI_Loader;
use Exception;

class Loader extends CI_Loader
{

	public function view($__view, $__data = [], $__return = false)
	{
		/* find view with fall back */
		$__path = $this->_findView($__view);

		extract($__data, EXTR_PREFIX_INVALID, '_');

		/* turn on output buffering */
		ob_start();

		/* bring in the view file */
		include $__path;

		/* get the current buffer contents and delete current output buffer */
		$__html = ob_get_clean();

		if (!$__return) {
			ci('output')->append_output($__html);
		}

		return $__html;
	}

	protected function _findView(string $view): string
	{
		try {
			$path = __ROOT__ . ci('servicelocator')->find('view', trim($view, '/'));
		} catch (Exception $e) {
			$path = VIEWPATH . trim($view, '/') . '.php';
		}

		if (!\file_exists($path)) {
			\show_error(404, 'Could not find the view file ' . $view . '.');
		}

		return $path;
	}
} /* end class */
