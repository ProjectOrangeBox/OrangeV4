<?php

namespace projectorangebox\orange\library\validate;

use projectorangebox\orange\library\Validate;
use projectorangebox\orange\library\Input as CI_Input;

class Input
{
	/**
	 * $input
	 *
	 * @var undefined
	 */
	protected $input;

	/**
	 * $validate
	 *
	 * @var undefined
	 */
	protected $parent;

	/**
	 * __construct
	 *
	 * @param mixed $parent
	 * @return void
	 */
	public function __construct(Validate $parent, CI_Input $input)
	{
		$this->parent = $parent;
		$this->input = $input;
	}

	/**
	 * is_valid
	 *
	 * @param array $keysRules
	 * @return void
	 */
	public function isValid(array $rules): bool
	{
		$fields = $this->input->request();

		return $this->parent->set_rules($rules)->set_data($fields)->run()->success();
	}

	/**
	 * filter
	 *
	 * @param array $keysRules
	 * @return void
	 */
	public function filter(array $rules): array
	{
		$fields = $this->input->request();

		$this->parent->set_rules($rules)->set_data($fields)->run();

		$this->input->set_request($fields);

		return $fields;
	}
} /* end class */
