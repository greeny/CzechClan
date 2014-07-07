<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

class GameRepository extends BaseRepository
{
	public function findForDashboard()
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[active] = ?', TRUE)
				->orderBy('[order] ASC')
				->fetchAll()
		);
	}

	public function findBySlug($slug)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[slug] = %s', $slug)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}
}
