<?php

namespace projectorangebox\orange\library\validate\filters;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

use projectorangebox\orange\library\validate\Input_filter;

/**
 * Validation Filter
 *
 * @help convert string to time and then formatted using date
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
class Convert_date extends Input_filter
{
	public function filter(&$field, string $options = '') : void
	{
		$options = ($options) ? $options : 'Y-m-d H:i:s';

		$field = date($options, strtotime($field));
	}
}
