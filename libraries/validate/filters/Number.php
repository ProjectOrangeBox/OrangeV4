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
 * @help filter for float optional length
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
class Number extends Input_filter
{
	public function filter(&$field, string $options = '') : void
	{
		$field  = preg_replace('/[^\-\+0-9.]+/', '', $field);

		$prefix = '';

		if (isset($field[0])) {
			$prefix = ($field[0] == '-' || $field[0] == '+') ? $field[0] : '';
		}

		$field  = $prefix.preg_replace('/[^0-9.]+/', '', $field);
		$this->field($field)->length($options);
	}
}
