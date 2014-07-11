<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

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

	public function findOneBy(Role $role, Game $game, $resource)
	{
		$g = $game === NULL ? $game : $game->id;
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[game_id] = %i', $g)
			->where('[role_id] = %i', $role->id)
			->where('[resource] = %s', $resource)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}
}
 