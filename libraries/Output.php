<?php

namespace projectorangebox\orange\library;

use CI_Output;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Extension to CodeIgniter Output Class
 *
 * Provides automatic handling of
 * JSON output
 * nocache header
 * setting & deleting cookies
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 * @uses # input - CodeIgniter Input
 *
 * @config base_url
 *
 */

class Output extends CI_Output
{
	/**
	 * JSON encoding for all json output
	 *
	 * @var int
	 */
	protected $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

	/**
	 * Send a JSON responds
	 *
	 * @access public
	 *
	 * @param $data null
	 * @param $val null
	 * @param $raw false
	 *
	 * @return Output
	 *
	 * #### Example
	 * ```php
	 * ci('output')->json('name','Johnny');
	 * ci('output')->json(['name'=>'Johnny']);
	 * ci('output')->json('{name:"Johnny"}',null,true);
	 * ci('output')->json(null,null,true); # use loader (view) variables
	 * ```
	 */
	public function json($data = null, $val = null, $raw = false) : Output
	{
		/* what the heck do we have here... */
		if ($raw && $data === null) {
			$json = $val;
		} elseif ($raw && $data !== null) {
			$json = '{"'.$data.'":'.$val.'}';
		} elseif (is_array($data) || is_object($data)) {
			$json = json_encode($data, $this->jsonOptions);
		} elseif (is_string($data) && $val === null) {
			$json = $data;
		} elseif ($data === null && $val === null) {
			$json = json_encode(ci()->load->get_vars(), $this->jsonOptions);
		} else {
			$json = json_encode([$data => $val], $this->jsonOptions);
		}

		$this
			->enable_profiler(false)
			->nocache()
			->set_content_type('application/json', 'utf-8')
			->set_output($json);

		return $this;
	}

	public function setJsonOptions(int $options) : Output
	{
		$this->jsonOptions = $options;

		return $this;
	}

	/**
	 *
	 * Send a nocache header
	 *
	 * @access public
	 *
	 * @return Output
	 *
	 */
	public function nocache() : Output
	{
		$this
			->set_header('Expires: Sat,26 Jul 1997 05:00:00 GMT')
			->set_header('Cache-Control: no-cache,no-store,must-revalidate,max-age=0')
			->set_header('Cache-Control: post-check=0,pre-check=0', false)
			->set_header('Pragma: no-cache');

		return $this;
	}

	/**
	 *
	 * Wrapper for input's set cookie because it more of a "output" function
	 *
	 * @access public
	 *
	 * @param $name
	 * @param string $value
	 * @param int $expire
	 * @param string $domain
	 * @param string $path /
	 * @param string $prefix
	 * @param bool $secure FALSE
	 * @param bool $httponly FALSE
	 *
	 * @return Output
	 *
	 */
	public function set_cookie($name = '', string $value = '', int $expire = 0, string $domain = '', string $path = '/', string $prefix = '', bool $secure = false, bool $httponly = false) : Output
	{
		ci('input')->set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly);

		return $this;
	}

	/**
	 *
	 * Delete all cookies (ie. set to a time in the past since which will make the browser ignore them
	 *
	 * @access public
	 *
	 * @return Output
	 *
	 */
	public function delete_all_cookies() : Output
	{
		foreach (ci('input')->cookie() as $name=>$value) {
			ci('input')->set_cookie($name, $value, (time() - 3600), config('config.base_url'));
		}

		return $this;
	}

	/**
	 *
	 * Provided to allow mocking to override and not exit
	 *
	 * @access public
	 *
	 * @param int $code 1
	 *
	 * @return void
	 *
	 */
	public function _exit(int $code = 1) : void
	{
		exit($code);
	}

	/* provide integer to string for HTTP status codes */
	public function statusCode(int $code) : string
	{
		$map = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing', // WebDAV; RFC 2518
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information', // since HTTP/1.1
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status', // WebDAV; RFC 4918
			208 => 'Already Reported', // WebDAV; RFC 5842
			226 => 'IM Used', // RFC 3229
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other', // since HTTP/1.1
			304 => 'Not Modified',
			305 => 'Use Proxy', // since HTTP/1.1
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect', // since HTTP/1.1
			308 => 'Permanent Redirect', // approved as experimental RFC
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot', // RFC 2324
			419 => 'Authentication Timeout', // not in RFC 2616
			420 => 'Enhance Your Calm', // Twitter
			420 => 'Method Failure', // Spring Framework
			422 => 'Unprocessable Entity', // WebDAV; RFC 4918
			423 => 'Locked', // WebDAV; RFC 4918
			424 => 'Failed Dependency', // WebDAV; RFC 4918
			424 => 'Method Failure', // WebDAV)
			425 => 'Unordered Collection', // Internet draft
			426 => 'Upgrade Required', // RFC 2817
			428 => 'Precondition Required', // RFC 6585
			429 => 'Too Many Requests', // RFC 6585
			431 => 'Request Header Fields Too Large', // RFC 6585
			444 => 'No Response', // Nginx
			449 => 'Retry With', // Microsoft
			450 => 'Blocked by Windows Parental Controls', // Microsoft
			451 => 'Redirect', // Microsoft
			451 => 'Unavailable For Legal Reasons', // Internet draft
			494 => 'Request Header Too Large', // Nginx
			495 => 'Cert Error', // Nginx
			496 => 'No Cert', // Nginx
			497 => 'HTTP to HTTPS', // Nginx
			499 => 'Client Closed Request', // Nginx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates', // RFC 2295
			507 => 'Insufficient Storage', // WebDAV; RFC 4918
			508 => 'Loop Detected', // WebDAV; RFC 5842
			509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
			510 => 'Not Extended', // RFC 2774
			511 => 'Network Authentication Required', // RFC 6585
			598 => 'Network read timeout error', // Unknown
			599 => 'Network connect timeout error', // Unknown
		];

		return (isset($map[$code])) ? $map[$code] : '';
	}

} /* end class */
