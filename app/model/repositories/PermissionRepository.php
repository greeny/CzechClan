<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class PermissionRepository extends BaseRepository
{
	public function getGeneralResources(array $blacklist = array())
	{
		$ret = array();
		foreach(Permission::$generalResources as $key => $value) {
			if(!in_array($key, $blacklist)) {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	public function getSpecificResources()
	{
		return Permission::$specificResources;
	}

	public function findOneBy(Role $role, $game, $resource)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[game_id] = %i', $game)
			->where('[role_id] = %i', $role->id)
			->where('[resource] = %s', $resource)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}
}
