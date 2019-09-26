<?php

namespace projectorangebox\orange\library;

use projectorangebox\orange\library\validate\Input;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Validate class.
 *
 * Run Validation & Filters on passed variables
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0
 *
 */
class Validate
{
	/**
	 * Storage for attached (Closure) validations
	 *
	 * @var array
	 */
	protected $attached = [];

	/**
	 * Storage the current validations error string usually in sprintf format
	 *
	 * @var string
	 */
	protected $error_string = '';

	/**
	 * Storage for the Human readable version of the field name can be used in the error string as sprintf parameter 1 ie. Last_name Last Name
	 *
	 * @var string
	 */
	protected $error_human = '';

	/**
	 * Storage for the Error options which can be used in the error string as sprintf parameter 2 ie. [1,34,67] becomes 1, 34, 67
	 *
	 * @var string
	 */
	protected $error_params = '';

	/**
	 * Storage for the field value which can be used in the error string as sprintf parameter 3
	 *
	 * @var string
	 */
	protected $error_field_value = '';

	/**
	 * Storage for the current field data being validated
	 *
	 * @var array
	 */
	protected $field_data = [];

	/**
	 * Local reference of validate configuration
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Local reference of Orange Error Object
	 *
	 */
	protected $errors = [];

	/**
	 * $request
	 *
	 * @var undefined
	 */
	public $input;

	/**
	 * $rules
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 * @param array $config []
	 *
	 */
	public function __construct(array &$config=null)
	{
		if (is_array($config)) {
			$this->config = &$config;
		}

		/* setup the "chain" request object */
		$this->input = new Input($this,ci('input'));

		log_message('info', 'Orange Validate Class Initialized');
	}

	/**
	 *
	 * Attach a validation rule as a Closure
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param closure $closure
	 *
	 * @return Validate
	 *
	 * #### Example
	 * ```php
	 * ci('validate')->attach('filter_lower',function(&$field, $options) { return strtolower($field); });
	 * ci('validate')->attach('return_true',function(&$field, $options) { return true; });
	 * ```
	 */
	public function attach(string $name, \closure $closure) : Validate
	{
		$this->attached[$name] = $closure;

		return $this;
	}

	/**
	 * variable - one time validation
	 *
	 * Process & Return
	 *
	 * @param mixed $input
	 * @param mixed $rules
	 * @return void
	 */
	public function isValid($input,$rules) : bool
	{
		/* one time validation */
		$local = new Validate();

		$data = is_array($input) ? $input : ['input'=>$input];
		$rules = is_array($rules) ? $rules : ['input'=>$rules];

		return $local->set_data($data)->set_rules($rules)->run()->success();
	}

	/**
	 * filter - one time filter
	 *
	 * Process & Return
	 *
	 * @param mixed $input
	 * @param mixed $rules
	 * @return void
	 */
	public function filter($input,$rules) /* mixed */
	{
		/* one time validation */
		$local = new Validate();

		$data = is_array($input) ? $input : ['input'=>$input];
		$rules = is_array($rules) ? $rules : ['input'=>$rules];

		$local->set_data($data)->set_rules($rules)->run();

		return $data['input'];
	}

	/**
	 * run
	 *
	 * @param mixed $rules
	 * @param mixed &$fields
	 * @param mixed string
	 * @return void
	 */
	public function run(string $namedGroup = 'default') : Validate
	{
		if (!isset($this->rules[$namedGroup])) {
			throw new \Exception('Validate rule group "'.$namedGroup.'" was not found.');
		}

		/* process each field and rule as a single rule, field, and human label */
		foreach ($this->rules[$namedGroup] as $rule) {
			$this->_single($rule['field'],$rule['rule'],$rule['human']);
		}

		return $this;
	}

	public function set_data(array &$fields) : Validate
	{
		$this->field_data = &$fields;

		return $this;
	}

	public function set_rules(array $rules,string $key='default') : Validate
	{
		foreach ($rules as $k=>$v) {
			$rulesToUse = (isset($v['rules'])) ? $v['rules'] : $v;

			$humanToUse = (isset($v['label'])) ? $v['label'] : $k;
			$humanToUse = (isset($v['human'])) ? $v['human'] : $humanToUse;

			$fieldToUse = (isset($v['field'])) ? $v['field'] : $k;

			$this->rules[$key][$fieldToUse] = ['rule'=>$rulesToUse,'human'=>$humanToUse,'field'=>$fieldToUse];
		}

		return $this;
	}

	/**
	 * success
	 *
	 * @return void
	 */
	public function success() : bool
	{
		return count($this->errors) == 0;
	}

	public function reset() : Validate
	{
		$this->errors = [];

		return $this;
	}

	public function errors() : array
	{
		return array_values($this->errors);
	}

	/**
	 *
	 * Run Validation rules on a single field value
	 *
	 * @access public
	 *
	 * @param $rules
	 * @param &$field
	 * @param string $human null
	 *
	 * @return Validate
	 *
	 */
	protected function _single(string $key, string $rules, string $human = null) : Validate
	{
		$rules = explode('|', $rules);

		/* do we have any rules? */
		if (count($rules)) {
			/* field value before any validations / filters */
			if (!isset($this->field_data[$key])) {
				$this->field_data[$key] = null;
			}

			$this->error_field_value =  $this->field_data[$key];

			foreach ($rules as $rule) {
				if ($this->_process_rule($key,$rule,$human) === false) {
					break; /* break from for each */
				}
			}
		}

		return $this;
	}

	protected function _process_rule(string $key, string $rule, string $human) : bool
	{
		/* no rule? exit processing of the $rules array */
		if (empty($rule)) {
			log_message('debug', 'No rule provied to validate against.');

			return false;
		}

		/* do we have this special rule? */
		if ($rule == 'allow_empty' && empty($this->field_data[$key])) {
			log_message('debug', 'Allow Empty validation rule skipping the rest because the field is empty.');

			return false;
		}

		$param = '';

		if (preg_match(';(?<rule>.*)\[(?<param>.*)\];', $rule, $matches, PREG_OFFSET_CAPTURE, 0)) {
			$rule = $matches['rule'];
			$param = $matches['param'];
		}

		$this->_makeHumanLookNice($human,$rule);
		$this->_makeParamsLookNice($param);

		/* take action on a validation or filter - filters MUST always start with "filter_" */
		return (substr(strtolower($rule), 0, 7) == 'filter_') ? $this->_filter($key, $rule, $param) : $this->_validation($key, $rule, $param);
	}

	protected function _makeHumanLookNice($human,$rule)
	{
		/* do we have a human readable field name? if not then try to make one */
		$this->error_human = ($human) ? $human : strtolower(str_replace('_', ' ', $rule));
	}

	protected function _makeParamsLookNice($param)
	{
		/* try to format the parameters into something human readable incase they need this in there error message  */
		if (strpos($param, ',') !== false) {
			$this->error_params = str_replace(',', ', ', $param);

			if (($pos = strrpos($this->error_params, ', ')) !== false) {
				$this->error_params = substr_replace($this->error_params, ' or ', $pos, 2);
			}
		} else {
			$this->error_params = $param;
		}
	}

	/**
	 *
	 * Run a filter rule.
	 * Filters always start with the filter_ prefix
	 * Filters always return true (success)
	 * if you need to register a error use a validation
	 *
	 * @access protected
	 *
	 * @param &$field
	 * @param string $rule
	 * @param string $param null
	 *
	 * @return bool
	 *
	 */
	protected function _filter(string $key, string $rule, string $param = null) : bool
	{
		/* filters start with filter_ */
		$shortRule = substr($rule,7);
		$className = $rule;

		if (isset($this->attached[$className])) {
			$this->attached[$className]($this->field_data[$key], $param);
		} elseif ($namedService = ci('servicelocator')->findInputFilter(str_replace('filter_', '',strtolower($className)),false)) {
			(new $namedService($this->field_data))->filter($this->field_data[$key], $param);
		} elseif (function_exists($shortRule)) {
			$this->field_data[$key] = ($param) ? $shortRule($this->field_data[$key], $param) : $shortRule($this->field_data[$key]);
		} else {
			throw new \Exception('Could not locate the filter named "'.$rule.'".');
		}

		/* filters don't fail */
		return true;
	}

	/**
	 *
	 * Run a validation rule.
	 * returns true on success and false on error
	 *
	 * @access protected
	 *
	 * @param &$field
	 * @param string $rule
	 * @param string $param null
	 *
	 * @return bool
	 *
	 */
	protected function _validation(string $key, string $rule, string $param = null) : bool
	{
		/* rules don't start with anything */
		$shortRule = $className = $rule;

		/* default error */
		$this->error_string = '%s is not valid.';

		if (isset($this->attached[$className])) {
			$success = $this->attached[$className]($this->field_data[$key], $param, $this->error_string, $this->field_data, $this);
		} elseif ($namedService = ci('servicelocator')->findValidationRule($className,false)) {
			$success = (new $namedService($this->field_data, $this->error_string))->validate($this->field_data[$key], $param);
		} elseif (function_exists($shortRule)) {
			$success = ($param) ? $shortRule($this->field_data[$key], $param) : $shortRule($this->field_data[$key]);
		} else {
			throw new \Exception('Could not locate the validate rule "'.$rule.'".');
		}

		/* if success is really really false then it's a error */
		if ($success === false) {
			/**
			 * sprintf argument 1 human name for field
			 * sprintf argument 2 human version of options (computer generated)
			 * sprintf argument 3 field value
			 */
			$this->errors[$this->error_human] = sprintf($this->error_string, $this->error_human, $this->error_params, $this->error_field_value);
		} else {
			/* not a boolean then it's something useable */
			if (!is_bool($success)) {
				$this->field_data[$key] = $success;

				$success = true;
			}
		}

		return $success;
	}

} /* end class */
