<?php

namespace projectorangebox\orange\library\page;

use projectorangebox\orange\library\Page;

class Asset
{
	/**
	 * local storage of page's configuration
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * $page
	 *
	 * @var undefined
	 */
	protected $page;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(Page $page, array &$config = [])
	{
		$this->page = &$page;

		$this->config = &$config;

		log_message('info', 'Page Asset Class Initialized');
	}

	/**
	 *
	 * Create the html for a link tag
	 * <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	 *
	 * @access public
	 *
	 * @param string $file
	 *
	 * @return string
	 *
	 * #### Example
	 * ```php
	 * $html = ci('page')->link_html('/assets/css/style.css');
	 * ```
	 */
	public function link_html(string $file): string
	{
		return $this->ary2element('link', array_merge($this->config['link_attributes'], ['href' => $file]));
	}

	/**
	 *
	 * Create the html for a script tag
	 * <script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.min.js"></script>
	 *
	 * @access public
	 *
	 * @param string $file
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```php
	 *
	 * ```
	 */
	public function script_html(string $file): string
	{
		return $this->ary2element('script', array_merge($this->config['script_attributes'], ['src' => $file]));
	}

	/**
	 *
	 * Convert a key value pair array into html attributes
	 *
	 * @access public
	 *
	 * @param string $element
	 * @param array $attributes
	 * @param $content false
	 *
	 * @throws
	 * @return string
	 *
	 * #### Example
	 * ```php
	 * $html = ci('page')->ary2element('a',['class'=>'bold','id'=>'id3'],'link!');
	 * ```
	 */
	public function ary2element(string $element, array $attributes, string $content = '', array $data = null): string
	{
		/* uses CodeIgniter Common.php _stringify_attributes function */
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$attributes['data-' . stripFromStart($key, 'data-')] = $value;
			}
		}

		return (in_array($element, ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'])) ?
			'<' . $element . _stringify_attributes($attributes) . '/>' : '<' . $element . _stringify_attributes($attributes) . '>' . $content . '</' . $element . '>';
	}

	/**
	 *
	 * Add a meta tag to the view variable
	 * <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	 *
	 * @access public
	 *
	 * @param $attr
	 * @param string $name null
	 * @param string $content null
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @throws
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->meta('charset','UTF-8');
	 * ```
	 */
	public function meta($attr, string $name = null, string $content = null, int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		if (is_array($attr)) {
			extract($attr);
		}

		$this->page->add('meta', '<meta ' . $attr . '="' . $name . '"' . (($content) ? ' content="' . $content . '"' : '') . '>' . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add javascript to the page script view variable
	 *
	 * @access public
	 *
	 * @param string $script
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->script('alert("Page Ready!");');
	 * ```
	 */
	public function script(string $script, int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		$this->page->add('script', $script . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add domready javascript to the page domready view variable
	 *
	 * @access public
	 *
	 * @param string $script
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->domready('alert("Page Ready!");');
	 * ```
	 */
	public function domready(string $script, int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		$this->page->add('domready', $script . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add Title to the page title view variable
	 *
	 * @access public
	 *
	 * @param string $title
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->title('My Web Page');
	 * ```
	 */
	public function title(string $title = '', int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		$this->page->add('title', $title, $priority);

		return $this;
	}

	/**
	 *
	 * Add css to the page style view variable
	 *
	 * @access public
	 *
	 * @param string $style
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->style('. { font-size: 9px }');
	 * ```
	 */
	public function style(string $style, int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		$this->page->add('style', $style . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add a script tag to the view variable
	 * <script ...></script>
	 *
	 * @access public
	 *
	 * @param string $script
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->script('/assets/javascript.js');
	 * ci('page')->script('/assets/javascript.js',PAGE::PRIORITY_HIGHEST);
	 * ```
	 */
	public function js($file = '', int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		if (is_array($file)) {
			foreach ($file as $f) {
				$this->js($f, $priority);
			}
			return $this;
		}

		$this->page->add('js', $this->script_html($file) . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add a link tag to the view variable
	 * <link ... />
	 *
	 * @access public
	 *
	 * @param $file
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->css('/assets/application.cs');
	 * ```
	 */
	public function css($file = '', int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		if (is_array($file)) {
			foreach ($file as $f) {
				$this->css($f, $priority);
			}
			return $this;
		}

		$this->page->add('css', $this->link_html($file) . PHP_EOL, $priority);

		return $this;
	}

	/**
	 *
	 * Add a Javascript variable to the view variable
	 *
	 * @access public
	 *
	 * @param string $key
	 * @param $value
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 * @param bool $raw false
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->js_variable('name','Johnny Appleseed');
	 * ci('page')->js_variable('name','{name: "Johnny Appleseed"}',PAGE::PRIORITY_NORMAL,true);
	 * ```
	 */
	public function js_variable(string $key, $value, int $priority = PAGE::PRIORITY_NORMAL, bool $raw = false): Asset
	{
		if ($raw) {
			$value = 'var ' . $key . '=' . $value . ';';
		} else {
			$value = ((is_scalar($value)) ? 'var ' . $key . '="' . str_replace('"', '\"', $value) . '";' : 'var ' . $key . '=' . json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ';');
		}

		$this->page->add('js_variables', $value, $priority);

		return $this;
	}

	/**
	 *
	 * Add a Javascript variables to the view variable
	 *
	 * @access public
	 *
	 * @param array $array
	 *
	 * @throws
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->js_variables(['name'=>'Johnny','age'=>23]);
	 * ```
	 */
	public function js_variables(array $array): Asset
	{
		foreach ($array as $k => $v) {
			$this->js_variable($k, $v);
		}

		return $this;
	}

	/**
	 *
	 * Add a class variable to the body class view variable
	 *
	 * @access public
	 *
	 * @param $class
	 * @param int $priority PAGE::PRIORITY_NORMAL
	 *
	 * @return Page
	 *
	 * #### Example
	 * ```php
	 * ci('page')->body_class('body-wrapper');
	 * ci('page')->body_class('body-wrapper o-theme');
	 * ci('page')->body_class(['body-wrapper','o-theme']);
	 * ```
	 */
	public function body_class($class, int $priority = PAGE::PRIORITY_NORMAL): Asset
	{
		$classes = (is_string($class)) ? explode(' ', $class) : (array) $class;

		foreach ($classes as $class) {
			$this->page->add('body_class', ' ' . strtolower(trim($class)), $priority);
		}

		return $this;
	}
} /* end class */
