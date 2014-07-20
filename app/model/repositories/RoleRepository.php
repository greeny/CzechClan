<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class RoleRepository extends BaseRepository
{
	public function findPairsWithBlacklist(array $ids)
	{

		$sel = $this->connection->select('*')
			->from($this->getTable());
		if($ids !== array()) {
			$sel->where('[id] NOT IN %in', $ids);
		}
		return $sel->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
	}
}
