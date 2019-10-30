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
class If_empty extends Validation_rule
{
	/*
	if_empty[never()] - in the future
	if_empty[now()] - now defaults to U
	if_empty[user()] - defaults to id
	if_empty[user(name)] - user name
	if_empty[#foobar] - if empty put the value foobar in there
	 */
	public function validate(&$field, string $options = ''): bool
	{
		if (trim($field) === '' || $field === null) {
			/* save a copy for later */
			$replace = $options;

			/* either pass right thru or run use one of these values */
			if (preg_match('/(.*)\((.*?)\)/', $options, $matches)) {
				switch ($matches[1]) {
					case 'never':
						$format  = ($matches[2]) ? $matches[2] : 'U';
						$replace = date($format, strtotime('2999-12-31 23:59:59'));
						break;
					case 'now':
						$format  = ($matches[2]) ? $matches[2] : 'U';
						$replace = date($format);
						break;
					case 'user':
						$param = ($matches[2]) ? $matches[2] : 'id';
						if (is_object(ci('user'))) {
							/* if it's empty make it 1 */
							$replace = (!empty(ci('user')->$param)) ? ci('user')->$param : 1;
						} else {
							$replace = 1; /* default to root user id / root user default group */
						}
						break;
					case 'session':
						$param   = ($matches[2]) ? $matches[2] : 'id';
						$replace = ci('session')->userdata($param);
						break;
					case 'ip':
						$replace = ci('input')->ip_address();
						break;
					default:
						if (substr($matches[1], 0, 1) == '#') {
							$replace = substr($matches[2], 1);
						}
				}
			}

			$field = $replace;
		}

		return true;
	}
}
