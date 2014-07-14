<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

class CategoryRepository extends BaseRepository
{
	public function fetchPairs()
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
	}
}
 