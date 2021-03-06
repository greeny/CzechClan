<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Security;

use Tempeus\Model\PermissionRepository;
use Tempeus\Model\Role;
use Tempeus\Model\RoleRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;
use Nette\Security\User;

class Authorizator implements IAuthorizator
{
	/** @var string[] */
	protected $roles = array();

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/** @var \Tempeus\Model\RoleRepository */
	protected $roleRepository;

	/** @var \Tempeus\Model\PermissionRepository */
	protected $permissionRepository;

	/** @var Permission */
	protected $permission;

	/** @var \Tempeus\Model\Permission[] */
	protected $permissions = array();

	/**
	 * @param RoleRepository       $roleRepository
	 * @param PermissionRepository $permissionRepository
	 * @param IStorage             $storage
	 */
	public function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository, IStorage $storage)
	{
		$this->cache = new Cache($storage, 'authorizator');
		$this->roleRepository = $roleRepository;
		$this->permissionRepository = $permissionRepository;
		$this->permission = $permission = new Permission();
		$permission->addRole('owner');
		$permission->addRole('quest');
		$permission->addRole('member', 'quest');
		$permission->addResource('admin');
		$permission->addResource('ticket');
		$permission->allow('owner');
		$this->initialize();
	}

	public function hasAdminAccess(User $user)
	{
		foreach($this->permissions as $permission) {
			if(in_array($permission->role->name, $user->getRoles())) {
				return TRUE;
			}
		}
		foreach($user->getRoles() as $role) {
			if($this->permission->isAllowed($role, 'admin', 'access')) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function isAdminAllowed(User $user, $game, $resource = NULL)
	{
		$roles = $user->getRoles();
		if(in_array('owner', $roles)) {
			return TRUE;
		}
		foreach($this->permissions as $permission) {
			if(in_array($permission->role->name, $roles) && (
					($permission->game && $permission->game->slug === $game) ||
					($permission->game === NULL && $game === 'general')
				)) {
				if(!$resource || ($resource && $permission->resource === $resource)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Performs a role-based authorization.
	 *
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function isAllowed($role, $resource, $privilege)
	{
		if(!in_array($role, $this->roles)) {
			return $this->permission->isAllowed($role, $resource, $privilege);
		}
		$allowed = false;

		return $allowed;
	}

	protected function initialize()
	{
		$this->addRoles($this->roleRepository->findAll());
		$this->addPermissions($this->permissionRepository->findAll());
	}

	protected function addRoles($roles)
	{
		foreach($roles as $role) {
			$this->addRole($role);
		}
	}

	protected function addRole(Role $role)
	{
		if($role->parent) {
			$this->addRole($role->parent);
		}
		if(!$this->permission->hasRole($role->name)) {
			$parent = $role->parent ? $role->parent->name : NULL;
			$this->permission->addRole($role->name, $parent);
			$this->roles[] = $role->name;
		}
	}

	protected function addPermissions($permissions)
	{
		foreach($permissions as $permission) {
			$this->addPermission($permission);
		}
	}

	protected function addPermission(\Tempeus\Model\Permission $permission)
	{
		$this->permissions[] = $permission;
	}
}
