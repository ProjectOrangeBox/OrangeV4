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
	 * Reference to the parent model class
	 *
	 * @var null
	 */
	protected $_modelReference = null;

	/**
	 * Save only these columns
	 *
	 * @var null
	 */
	protected $_saveColumns = null;


	protected $_primaryKey = null;

	/**
	 *
	 * Constructor
	 *
	 * @access public
	 *
	 */
	public function __construct(&$model = null, $primaryKey = null)
	{
		if ($model) {
			$this->_modelReference = &$model;
		}

		if ($primaryKey) {
			$this->_primaryKey = $primaryKey;
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
		$saveData = [];

		/* if save columns is set then only use those properties */
		if (is_array($this->_saveColumns)) {
			foreach ($this->_saveColumns as $col) {
				$saveData[$col] = $this->$col;
			}
		} else {
			/* else use all public properties */
			$saveData = \get_object_vars($this);
		}

		$primaryId = $this->_modelReference->save($saveData);

		if ($primaryId !== false) {
			$this->_primaryKey = $primaryId;
		}

		return ($primaryId !== false);
	}
} /* end class */
