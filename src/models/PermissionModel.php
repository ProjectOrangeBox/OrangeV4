<?php

namespace projectorangebox\orange\model;

use projectorangebox\orange\model\DatabaseModel;

/**
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
class PermissionModel extends DatabaseModel
{
	protected $table; /* picked up from auth config */
	protected $additional_cache_tags = '.acl';
	protected $entity = '\projectorangebox\orange\model\entities\permissionEntity';
	protected $rules = [
		'id'          => ['field' => 'id', 'label' => 'Id', 'rules' => 'required|integer|max_length[10]|less_than[4294967295]|filter_int[10]'],
		'key'         => ['field' => 'key', 'label' => 'Key', 'rules' => 'required|strtolower|max_length[255]|filter_input[255]|is_uniquem[PermissionModel.key.id]'],
		'description' => ['field' => 'description', 'label' => 'Description', 'rules' => 'required|max_length[255]|filter_input[255]|is_uniquem[PermissionModel.description.id]'],
		'group'       => ['field' => 'group', 'label' => 'Group', 'rules' => 'required|max_length[255]|filter_input[255]'],
		'migration'   => ['field' => 'migration', 'label' => 'Migration', 'rules' => 'max_length[255]'],
	];

	/**
	 * __construct
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
	public function __construct()
	{
		/* get the table name from the auth config file */
		$this->table = config('auth.permission table');

		/* let the parent do it's work */
		parent::__construct();

		/* ready to go */
		log_message('info', 'PermissionModel Class Initialized');
	}

	/**
	 * roles
	 * Insert description here
	 *
	 * @param $role
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	public function roles($role_id)
	{
		$dbc = $this->_database
			->from(config('auth.role permission table'))
			->join(config('auth.role table'), config('auth.role table') . '.id = ' . config('auth.role permission table') . '.role_id')
			->where(['permission_id' => (int) $role_id])
			->get();

		return ($this->_database->num_rows() > 0) ? $dbc->result() : [];
	}

	/**
	 * find_permission_id
	 * Insert description here
	 *
	 * @param $permission
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	public function find_permission_id($permission): int
	{
		return (int) ((int) $permission > 0) ? $permission : $this->PermissionModel->column('id')->get_by(['key' => $permission]);
	}

	/**
	 * insert
	 * Insert description here
	 *
	 * @param $data
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	public function insert(array $data)
	{
		$success = parent::insert($data);

		$this->_refresh();
		$this->delete_cache_by_tags();

		return $success;
	}

	/**
	 * update
	 * Insert description here
	 *
	 * @param $data
	 *
	 * @return
	 *
	 * @access
	 * @static
	 * @throws
	 * @example
	 */
	public function update(array $data)
	{
		$success = parent::update($data);

		$this->_refresh();
		$this->delete_cache_by_tags();

		return $success;
	}

	/**
	 * _refresh
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
	public function _refresh()
	{
		$records = $this->get_many();

		/* automatically adds permission to admin role */
		foreach ($records as $record) {
			ci('RoleModel')->add_permission(ADMIN_ROLE_ID, $record->id);
		}

		/* makes sure nobody user has NO permissions */
		ci('RoleModel')->remove_permission(NOBODY_USER_ID);
	}

	/* migration */
	public function migration_add($key = null, $group = null, $description = null, $migration = null)
	{
		$this->skip_rules = true;

		/* we already verified the key that's the "real" primary key */
		$success = (!$this->exists(['key' => $key])) ? $this->insert(['key' => $key,	'group' => $group, 'description' => $description, 'migration' => $migration]) : false;

		$this->_refresh();

		return $success;
	}

	public function migration_remove(string $migration = null): bool
	{
		$this->skip_rules = true;

		unset($this->has['delete_role']);

		$success = $this->delete_by(['migration' => $migration]);

		$this->_refresh();

		return $success;
	}
} /* end class */
