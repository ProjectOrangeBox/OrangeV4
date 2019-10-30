<?php

namespace projectorangebox\orange\library\abstracts;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Authorization class.
 *
 * Handles login, logout, refresh user data
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 * @filesource
 *
 */
abstract class Middleware
{
	/**
	 *
	 * Called on request before the controller method is called
	 * This method is overridden in the child class
	 *
	 * @access public
	 *
	 * @return void
	 *
	 */
	public function request(\CI_Input &$input): bool
	{
		return true;
	}

	/**
	 *
	 * Called on responds after the controller method is called
	 * This method is overridden in the child class
	 *
	 * @access public
	 *
	 * @param string $output
	 *
	 * @return string
	 *
	 */
	public function response(string &$output): bool
	{
		return true;
	}
} /* end class */
