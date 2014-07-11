<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

class RoleRepository extends BaseRepository
{
	public function findPairsWithBlacklist(array $ids)
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->where('[id] NOT IN %in', $ids)
			->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
	}
}
 