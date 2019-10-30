<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\page\Asset;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * HTML Page Building
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses # load - CodeIgniter Loader
 * @uses # output - CodeIgniter Output
 * @uses # event - Orange Event
 * @uses # Orange view(...)
 *
 * @config script_attributes `['src' => '', 'type' => 'text/javascript', 'charset' => 'utf-8']`
 * @config link_attributes `['href' => '', 'type' => 'text/css', 'rel' => 'stylesheet']`
 * @config domready_javascript `document.addEventListener("DOMContentLoaded",function(e){%%});`
 * @config page_prefix `page_`
 * @config page_ array of additional
 * @config page_min boolean
 *
 * @define PAGE_MIN
 *
 */
class Page
{
	const PRIORITY_LOWEST = 10;
	const PRIORITY_LOW = 20;
	const PRIORITY_NORMAL = 50;
	const PRIORITY_HIGH = 80;
	const PRIORITY_HIGHEST = 90;

	/**
	 * storage for the page variables
	 *
	 * @var array
	 */
	protected $variables = [];

	/**
	 * local storage of page's configuration
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * CodeIgniter Loader Object
	 *
	 * @var object
	 */
	protected $load;

	/**
	 * CodeIgniter Output Object
	 *
	 * @var object
	 */
	protected $output;

	/**
	 * Orange Event Object
	 *
	 * @var object
	 */
	protected $event;

	/**
	 * View variable prefix for all page variables
	 *
	 * @var string
	 */
	protected $page_variable_prefix = '';

	/**
	 * Storage for if the current view is extending another view?
	 *
	 * @var boolean | string
	 */
	protected $extending = false;

	/**
	 * $asset
	 *
	 * @var undefined
	 */
	public $asset;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array &$config = null)
	{
		if (is_array($config)) {
			$this->config = &$config;
		}

		/* pear plugin is a static class which manages pear plugins and is loaded into the global namespace so views can use it easily */
		require_once __DIR__ . '/page/Pear.php';

		$this->asset = new Asset($this, $config);

		$this->load = ci('load');
		$this->output = ci('output');
		$this->event = ci('event');

		log_message('info', 'Orange Page Class Initialized');
	}

	/**
	 *
	 * Render view content string
	 * optionally "extending" another "parent" template
	 *
	 * @access public
	 *
	 * @param string $view null
	 * @param array $data null
	 *
	 * @throws \Exception
	 * @return Page
	 *
	 */
	public function render(string $view, array $data = null): Page
	{
		log_message('debug', 'page::render::' . $view);

		$view = trim(\stripFromEnd($view, '.php'), '/');

		/* called everytime - use with caution */
		$this->event->trigger('page.render', $this, $view);

		/* called only when a trigger matches the view */
		$this->event->trigger('page.render.' . $view, $this, $view);

		/* this is going to be the "main" section */
		$view_content = $this->view($view, $data);

		if ($this->extending) {
			$view_content = $this->view((string) $this->extending);
		}

		/* called everytime - use with caution  */
		$this->event->trigger('page.render.content', $view_content, $view, $data);

		/* append to the output responds */
		$this->output->append_output($view_content);

		return $this;
	}

	/**
	 *
	 * Pages View Rendering
	 * using oranges most basic view function
	 *
	 * @access public
	 *
	 * @param string $view_file null
	 * @param array $data null
	 * @param $return true optionally a string which then inserts this into the view data array with this as it's variable name
	 *
	 * @throws \Exception
	 * @return mixed
	 *
	 * #### Example
	 * ```php
	 * ci('page')->view('folder/my_view',['name'=>'Johnny']);
	 * ci('page')->view('folder/my_block',['name'=>'Johnny Appleseed'],'name_block');
	 * ```
	 */
	public function view(string $view_file = null, array $data = null, $return = true)
	{
		$data = (is_array($data)) ? array_merge($this->load->get_vars(), $data) : $this->load->get_vars();

		/**
		 * call core orange function view()
		 *
		 * Throws Exception if view not found.
		 */
		$buffer = ci('orange')->view($view_file, $data);

		if (is_string($return)) {
			$this->data($return, $buffer);
		}

		return ($return === true) ? $buffer : $this;
	}

	/**
	 *
	 * Insert view data
	 * wrapper for CodeIgniters Loader
	 *
	 * @access public
	 *
	 * @param $name string or array
	 * @param $value null
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->data('name','Johnny');
	 * ci('page')->data(['name'=>'Johnny','age'=>23]);
	 * ```
	 */
	public function data($name, $value = null): Page
	{
		$this->load->vars($name, $value);

		return $this;
	}

	/**
	 *
	 * Specify a additional template which will be loaded after the view template
	 * this allows you to use a "base" template and extend it with your view
	 * you may only extend 1 template.
	 *
	 * @access public
	 *
	 * @param string $template null
	 *
	 * @throws \Exception
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->extend('_templates/default');
	 * ```
	 */
	public function extend(string $template = null): Page
	{
		if ($this->extending) {
			throw new \Exception('You are already extending "' . $this->extending . '" therefore we cannot extend "' . $template . '".');
		}

		$this->extending = $template;

		return $this;
	}

	/**
	 *
	 * Retrieve a page variable (with "post" priority processing)
	 * included page variables: title, meta, body_class, css, style, js, script, js_variables, script, domready
	 *
	 * @access public
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 * #### Example
	 * ```php
	 * $script_html = ci('page')->value('script');
	 * ```
	 */
	public function value(string $name): string
	{
		$html = $this->load->get_var($name);

		/* if it's empty than maybe is it a page variable? */
		if (empty($html)) {
			$html = $this->load->get_var($this->page_variable_prefix . $name);
		}

		/* does this variable key exist */
		if (isset($this->variables[$name])) {
			/* has it already been sorted */
			if (!$this->variables[$name][0]) {
				/* no we must sort it */
				array_multisort($this->variables[$name][1], SORT_DESC, SORT_NUMERIC, $this->variables[$name][2]);

				/* mark it as sorted */
				$this->variables[$name][0] = true;
			}

			foreach ($this->variables[$name][2] as $append) {
				$html .= $append;
			}
		}

		return trim($html);
	}

	/**
	 *
	 * Add ANY variable to the view variables with priority
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $priority ASSET::PRIORITY_NORMAL
	 * @param bool $prevent_duplicates true
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->add('custom_var','<p>Custom Stuff!</p>');
	 * ```
	 */
	public function add(string $name, string $value, int $priority = PAGE::PRIORITY_NORMAL, bool $prevent_duplicates = true): Page
	{
		$key = md5($value);

		if (!isset($this->variables[$name][3][$key]) || !$prevent_duplicates) {
			$this->variables[$name][0] = !isset($this->variables[$name]); /* sorted */
			$this->variables[$name][1][] = (int) $priority; /* unix priority */
			$this->variables[$name][2][] = $value; /* actual html content (string) */
			$this->variables[$name][3][$key] = true; /* prevent duplicates */
		}

		return $this;
	}
} /* end page */
