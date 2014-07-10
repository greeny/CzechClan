<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Security;

use CzechClan\Model\PermissionRepository;
use CzechClan\Model\Role;
use CzechClan\Model\RoleRepository;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;

class Authorizator implements IAuthorizator
{
	/** @var string[] */
	protected $roles = array();

	/** @var \Nette\Caching\Cache */
	protected $cache;

	/** @var \CzechClan\Model\RoleRepository */
	protected $roleRepository;

	/** @var \CzechClan\Model\PermissionRepository */
	protected $permissionRepository;

	/** @var Permission */
	protected $permission;

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
		$permission->allow('owner');
		$this->initialize();
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
		// FIXME how to get game here??

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
		if($this->permission->hasRole($role->name)) {
			$this->permission->addRole($role->name, $role->parent->name);
			$this->roles[] = $role->name;
		}
	}

	protected function addPermissions($permissions)
	{
		foreach($permissions as $permission) {
			$this->addPermission($permission);
		}
	}

	protected function addPermission(\CzechClan\Model\Permission $permission)
	{
		// TODO
	}
}
 