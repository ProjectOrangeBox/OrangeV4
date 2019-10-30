<?php

namespace projectorangebox\orange\model;

/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * Database Model Entity Abstract Class.
 *
 * This models as database record and provides a automatic "save" function
 *
 * Handles login, logout, refresh user data
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0.0
 * @filesource
 *
 */

abstract class DatabaseModelEntity
{
	/**
	 * The string name of the model this entity is attached to
	 * this is used to call the insert or update method on
	 * if left blank on the entity it will try to determine the model name it self.
	 *
	 * @var string
	 */
	protected $_model_name = null;

	/**
	 * Reference to the parent model class
	 *
	 * @var null
	 */
	protected $_model_ref = null;

	/**
	 * Save only these columns
	 *
	 * @var null
	 */
	protected $_save_columns = null;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 */
	public function __construct(&$config = [])
	{
		if (isset($config['model'])) {
			$this->_model_ref = &$config['model'];
		} else {
			/* if nothing provided on the child entity strip off the _entity part and replace with _model */
			$model_name = (!is_string($this->_model_name)) ? strtolower(substr(get_called_class(), 0, -7) . '_model') : $this->_model_name;

			$this->_model_ref = &ci($model_name);
		}

		log_message('info', 'DatabaseModelEntity Class Initialized');
	}

	/**
	 *
	 * Provide a save method to auto save (update) a entity back to the database
	 *
	 * @access public
	 *
	 * @return bool
	 *
	 */
	public function save(): bool
	{
		/* get the primary key */
		$primary_id = $this->_model_ref->get_primary_key();

		/* if save columns is set then only use those properties */
		if (is_array($this->_save_columns)) {
			foreach ($this->_save_columns as $col) {
				$data[$col] = $this->$col;
			}
		} else {
			/* else use all public properties */
			$data = get_object_vars($this);
		}

		/**
		 * if the primary id is empty then insert the entity
		 * The following values are considered to be empty:
		 *
		 * "" (an empty string)
		 * 0 (0 as an integer)
		 * 0.0 (0 as a float)
		 * "0" (0 as a string)
		 * NULL
		 * FALSE
		 * array() (an empty array)
		 */
		if (empty($data[$primary_id])) {
			$success = $this->$primary_id = $this->_model_ref->insert($data);
		} else {
			/* else it's a update */
			$success = $this->_model_ref->update($data);
		}

		/* return boolean success */
		return (bool) $success;
	}
} /* end class */
