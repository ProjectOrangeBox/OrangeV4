<?php

namespace projectorangebox\orange\library\validate\rules;

use projectorangebox\orange\library\validate\Validation_rule;

/**
 * Validate_alpha_space
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
 * @help anything other than alpha, space or dash characters.
 *
 */
class Alpha_space extends Validation_rule
{
	public function validate(&$field, string $options = '') : bool
	{
		$this->error_string = '%s may only contain alpha characters, spaces, and dashes.';
		return (bool) preg_match('/^[a-z -]+$/i', $field);
	}
}
