<?php

namespace projectorangebox\orange\library\validate\rules;

use projectorangebox\orange\library\validate\Validation_rule;

/**
 * Validate_alpha_dash
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
 * @help contains anything other than alphabetical, underscore, dash characters.
 *
 */
class Is_uniquem extends Validation_rule
{
	public function validate(&$field, string $options = ''): bool
	{
		/* is_uniquem[model_name.column_name.$_POST[primary_key]] */
		$this->error_string = '%s is already being used.';

		list($model, $column, $postkey) = explode('.', $options, 3);

		if (empty($model)) {
			return false;
		}

		if (empty($column)) {
			return false;
		}

		if (empty($postkey)) {
			return false;
		}

		return ci($model)->is_uniquem($field, $column, $postkey);
	}
}
