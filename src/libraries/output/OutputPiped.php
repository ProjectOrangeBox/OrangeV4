<?php

namespace projectorangebox\orange\library\output;

use projectorangebox\orange\library\output\Output;

class OutputPiped extends Output
{
	protected $headerSent = false;
	protected $inject = [];
	protected $functionName = '_i';

	public function __construct()
	{
		parent::__construct();

		ini_set('implicit_flush', 1);
		ob_implicit_flush(1);
	}

	/* override parent */
	public function append_output($output)
	{
		$this->echo($output);

		return $this;
	}

	/* override parent */
	public function _display($output = '')
	{
		$this->echo($output);

		foreach ($this->inject as $name => $output) {
			$this->inject($name, $output);
		}

		return $this;
	}

	/**
	 * echo
	 *
	 * @param mixed string
	 * @return void
	 */
	public function echo(string $output = ''): Output
	{
		if (!$this->headerSent) {
			$this->headerSent = true;

			/* The parent _display will build correct header  and send it */
			parent::_display('');
		}

		/* Output String */
		echo $output;

		/* Send the output buffer & Flush system output buffer */
		ob_flush();
		flush();

		return $this;
	}

	/**
	 * injector - The injector Javascript code
	 *
	 * @param mixed bool
	 * @return void
	 */
	public function injector(bool $send = true): string
	{
		$injector = '<script>function ' . $this->functionName . '(i,c){let e=document.getElementById(i);if(e){e.outerHTML=c}}</script>';

		if (!$send) {
			$this->echo($injector);
		}

		return $injector;
	}

	/**
	 * addInjection - Add injection for injection when the page is "complete"
	 *
	 * @param string $name
	 * @param string $output
	 * @return void
	 */
	public function addInjection(string $name, string $output): Output
	{
		/* else save for final display */
		$this->inject[$name] = $output;

		return $this;
	}

	/**
	 * inject - inject NOW!
	 *
	 * @param string $name
	 * @param string $output
	 * @return void
	 */
	public function inject(string $name, string $output): Output
	{
		/* must have wrapping <script> tags so it can be run ASAP (no need to wait for the closing script tag) */
		$this->echo('<script>' . $this->functionName . '("' . $name . '",' . json_encode($output) . ');</script>');

		return $this;
	}
} /* end class */
