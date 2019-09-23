<?php

namespace projectorangebox\orange\library\validate\rules;

use projectorangebox\orange\library\validate\Validation_rule;

/**
 * Validate_greater_than_equal_to
 * Insert description here
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2018
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 *
 * required
 * core:
 * libraries:
 * models:
 * helpers:
 * functions:
 *
 * @help greater than or equal to the parameter value, or not numeric.
 *
 */
class Greater_than_equal_to extends Validation_rule
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s must contain a number greater than or equal to %s.';

		if (!is_numeric($field)) {
			return false;
		}

		return is_numeric($field) ? ($field >= $options) : false;
	}
}
