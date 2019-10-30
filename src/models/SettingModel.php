<?php

namespace projectorangebox\orange\model;

use projectorangebox\orange\model\DatabaseModel;

/**
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
 */

/*
CREATE TABLE `orange_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) unsigned NOT NULL DEFAULT 1,
  `created_ip` varchar(16) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT 0,
  `updated_ip` varchar(16) DEFAULT NULL,
  `read_role_id` int(11) unsigned NOT NULL DEFAULT 1,
  `edit_role_id` int(11) unsigned NOT NULL DEFAULT 1,
  `delete_role_id` int(11) unsigned NOT NULL DEFAULT 1,
  `group` varchar(128) DEFAULT NULL,
  `name` varchar(128) CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1 NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `help` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `options` text DEFAULT NULL,
  `migration` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group` (`group`) USING BTREE,
  KEY `idx_enabled` (`enabled`) USING BTREE,
  KEY `idx_name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
*/

class SettingModel extends DatabaseModel
{
	protected $table = 'orange_settings';
	protected $has = [
		'read_role' => 'read_role_id',
		'edit_role' => 'edit_role_id',
		'delete_role' => 'delete_role_id',
		'created_by' => 'created_by',
		'created_on' => 'created_on',
		'created_ip' => 'created_ip',
		'updated_by' => 'updated_by',
		'updated_on' => 'updated_on',
		'updated_ip' => 'updated_ip',
	];
	protected $rules = [
		'id'             => ['field' => 'id', 'label' => 'Id', 'rules' => 'required|integer|max_length[10]|less_than[4294967295]|filter_int[10]'],
		'group'          => ['field' => 'group', 'label' => 'Group', 'rules' => 'required|max_length[64]|filter_input[64]|trim'],
		'name'           => ['field' => 'name', 'label' => 'Name', 'rules' => 'required|max_length[64]|filter_input[64]|trim'],
		'value'          => ['field' => 'value', 'label' => 'Value', 'rules' => 'max_length[16384]|filter_textarea[16384]'],
		'enabled'        => ['field' => 'enabled', 'label' => 'Enabled', 'rules' => 'if_empty[0]|in_list[0,1]|filter_int[1]|max_length[1]|less_than[2]'],
		'help'           => ['field' => 'help', 'label' => 'Help', 'rules' => 'max_length[255]|filter_input[255]'],
		'options'        => ['field' => 'options', 'label' => 'Options', 'rules' => 'max_length[16384]|filter_textarea[16384]'],
		'migration'			 => ['field' => 'migration', 'label' => 'Migration', 'rules' => 'max_length[255]'],
	];

	/**
	 * pull
	 * Insert description here
	 *
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	public function get_enabled()
	{
		$this->_database->where(['enabled' => 1]);

		$dbc = $this->_database->get($this->table);

		return $dbc->result();
	}

	/**
	 * delete_cache_by_tags
	 * Insert description here
	 *
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	protected function delete_cache_by_tags(): DatabaseModel
	{
		ci('config')->flush();

		return parent::delete_cache_by_tags();
	}

	/* migration */
	public function migration_add($name = null, $group = null, $value = null, $help = null, $options = null, $migration = null, $optional = [])
	{
		$this->skip_rules = true;

		$defaults = [
			'read_role_id' => ADMIN_ROLE_ID,
			'edit_role_id' => ADMIN_ROLE_ID,
			'delete_role_id' => ADMIN_ROLE_ID,
			'created_on' => date('Y-m-d H:i:s'),
			'created_by' => 0,
			'created_ip' => '0.0.0.0',
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => 0,
			'updated_ip' => '0.0.0.0',
		];

		$columns = array_merge($defaults, ['name' => $name, 'group' => $group, 'value' => $value, 'help' => $help, 'options' => $options, 'migration' => $migration]);

		/* these override everything */
		foreach ($optional as $key => $val) {
			$columns[$key] = $val;
		}

		/* we already verified the key that's the "real" primary key */
		return (!$this->exists(['name' => $name, 'group' => $group])) ? $this->insert($columns) : false;
	}

	public function migration_remove(string $migration = null): bool
	{
		$this->skip_rules = true;

		unset($this->has['delete_role']);

		return $this->delete_by(['migration' => $migration]);
	}
}
