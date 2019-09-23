<?php

namespace projectorangebox\orange\library;

use CI_Input;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Extension to CodeIgniter Input Class
 *
 * Provides unified Request Method to read php://input
 * A way to set / change the request data
 * valid validation on input returning success
 * filter validation on input replacing input with filtered
 * filtered validation on input returning filtered input
 * A way to dynamically remap the input
 * read a cookie like other input with default option
 * manually set the request type
 * test if the request is ajax or cli
 * stash / unstash input between requests
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses # session - CodeIgniter Session
 * @uses # validate - Orange validate
 *
 * @config config.encryption_key
 *
 */

class Input extends CI_Input
{
	/**
	 * Contains the current POST or PUT or PATCH request data
	 *
	 * @var array
	 */
	protected $_request = [];

	/**
	 * Contains the current request type
	 * HTML (default), ajax, cli
	 *
	 * @var string
	 */
	protected $requestType = '';

	/**
	 * The input stash session key
	 *
	 * @var string
	 */
	protected $stashKey = '_stash_hash_key_';

	/**
	 * $requestMethod
	 *
	 * @var string
	 */
	protected $requestMethod = '';

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 */
	public function __construct()
	{
		/* grab raw input for patch and such */
		$this->set_raw_input_stream(file_get_contents('php://input'),true);

		/* did we get anything? if not fall back to the posted input if any */
		if (!count($this->_request)) {
			$this->_request = $_POST;
		}

		/* call the parent classes constructor */
		parent::__construct();

		/* setup the request type based on a few things */
		$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		$isJson = (!empty($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/json') !== false);
		$isCli = (PHP_SAPI === 'cli' OR defined('STDIN'));

		if ($isAjax || $isJson) {
			$this->requestType = 'ajax';
		} elseif ($isCli) {
			$this->requestType = 'cli';
		} else {
			$this->requestType = 'html';
		}

		/* get the http request method or default to cli */
		$this->requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'cli';

		log_message('info', 'Orange Input Class Initialized');
	}

	/**
	 *
	 * Fetch post or put data
	 *
	 * #### Example
	 * ```php
	 * request('name','nothing supplied',true)
	 * request(null,null,true)
	 * request()
	 * ```
	 * @access public
	 *
	 * @param $index input parameter name
	 * @param $default default value if empty
	 * @param bool $xss_clean whether to apply XSS filtering
	 *
	 * @return
	 *
	 */
	public function request($index = null, $default = null, bool $xss_clean = false)
	{
		log_message('debug', 'Input::request::'.$index);

		/* pull the value from our array and process with our built in function */
		$value = $this->_fetch_from_array($this->_request, $index, $xss_clean);

		/* was anything returned? if no return the default */
		return ($value === null) ? $default : $value;
	}

	/**
	 *
	 * Change or replace request data
	 *
	 * @access public
	 *
	 * @param $index request index key or array or key value pairs
	 * @param $replace_value value if true is provided and $index is a array then the entire request will be replaced
	 *
	 * @return Input
	 *
	 */
	public function set_request($index = null, $replace_value = null) : Input
	{
		if (is_array($index) && $replace_value === true) {
			$this->_request = $index;
		} elseif (is_array($index)) {
			foreach ($index as $i=>$v) {
				$this->set_request($i, $v);
			}
		} else {
			$this->_request[$index] = $replace_value;
		}

		return $this;
	}

	/**
	 *
	 * Treat cookie like request with default value
	 *
	 * @access public
	 *
	 * @param $index
	 * @param $default
	 * @param $xss_clean false
	 *
	 * @return mixed
	 *
	 * #### Example
	 * ```php
	 * ci('input')->cookies('username','unknown',true)
	 * ci('input')->cookies(null,null,true)
	 * ci('input')->cookies()
	 * ```
	 */
	public function cookie($index = null, $default = null, $xss_clean = false)
	{
		$value = $this->_fetch_from_array($_COOKIE, $index, false);

		$value = ($value === null) ? $default : $value;

		return ($xss_clean) ? $this->security->xss_clean($value) : $value;
	}

	public function get_raw_input_stream() : string
	{
		return $this->_raw_input_stream;
	}

	/**
	 * set_raw_input_stream
	 *
	 * @param string $rawInputStream
	 * @return void
	 */
	public function set_raw_input_stream(string $rawInputStream,bool $parse = true) : void
	{
		$this->_raw_input_stream = $rawInputStream;

		if ($parse) {
			parse_str($this->_raw_input_stream, $this->_request);
		}
	}

	/**
	 *
	 * Manually set the current request type for testing purposes
	 *
	 * @access public
	 *
	 * @param string $request_type [cli|ajax|html]
	 *
	 * @throws \Exception
	 * @return \Input
	 *
	 */
	public function set_request_type(string $requestType) : Input
	{
		$requestType = strtolower($requestType);

		/* options include cli, ajax, html */
		if (!in_array($requestType, ['cli','ajax','html'])) {
			throw new \Exception(__METHOD__.' unknown type '.$requestType.'.');
		}

		$this->requestType = $requestType;

		return $this;
	}

	/**
	 * Manually set the current http method for testing purposes
	 *
	 * @param string $requestMethod
	 * @return void
	 */
	public function set_http_method(string $requestMethod) : Input
	{
		$requestMethod = strtolower($requestMethod);

		/* options include cli, ajax, html */
		if (!in_array($requestMethod, ['cli','get','head','post','put','delete','connect','options','trace','patch'])) {
			throw new \Exception(__METHOD__.' unknown type '.$requestMethod.'.');
		}

		$this->requestMethod = $requestMethod;

		return $this;
	}

	/**
	 * get_http_method
	 *
	 * @return void
	 */
	public function get_http_method() : string
	{
		return strtolower($this->requestMethod);
	}

	/**
	 * get_request_type
	 *
	 * @return void
	 */
	public function get_request_type() : string
	{
		return $this->requestType;
	}

	/**
	 *
	 * Determine if this is a ajax request
	 *
	 * @access public
	 *
	 * @return bool
	 *
	 */
	public function is_ajax_request() : bool
	{
		return ($this->requestType == 'ajax');
	}

	/**
	 * Determine if this is a Command Line request
	 *
	 * @access public
	 *
	 * @return bool
	 *
	 */
	public function is_cli_request() : bool
	{
		return ($this->requestType == 'cli');
	}

	/**
	 *
	 * Stash the request data for later retrieval
	 *
	 * @return \Input
	 *
	 */
	public function stash() : Input
	{
		/* is there even an array to store? */
		if (is_array($this->_request)) {
			ci('session')->set_tempdata($this->stashKey, $this->_request, 3600); /* fixed at 10 minutes */
		}

		return $this;
	}

	/**
	 *
	 * Load the request from the stashed data
	 *
	 * @return bool
	 *
	 */
	public function unstash() : bool
	{
		/* read the stashed data if any */
		$stashed = ci('session')->tempdata($this->stashKey);

		/* clear the stashed data */
		ci('session')->unset_tempdata($this->stashKey);

		/* set the request to the stashed data or nothing is it's not an array */
		$this->_request = (is_array($stashed)) ? $stashed : [];

		return is_array($stashed);
	}
} /* end class */
