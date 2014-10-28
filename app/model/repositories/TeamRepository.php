<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class TeamRepository extends BaseRepository
{
	public function findByGame($game)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = ?', $game->id)
				->orderBy('[order] ASC')
				->orderBy('[name] ASC')
				->fetchAll()
		);
	}
}
