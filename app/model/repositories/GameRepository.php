<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Utils\ArrayHash;

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

	public function findPairs()
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
	}

	public function addGame(ArrayHash $data)
	{
		if(in_array($data->slug, array('general', 'user', 'admin', 'api'))) {
			throw new RepositoryException("Zkratka '$data->slug' nemůže být použita.");
		}
		if($this->findBySlug($data->slug)) {
			throw new RepositoryException("Hra se zkratkou '$data->slug' již existuje.");
		}
		$game = Game::from($data);
		$this->persist($game);
		return $game;
	}

	public function updateGame(Game $game, ArrayHash $data)
	{
		$game->update($data);
		$g = $this->findBySlug($game->slug);
		if($g && $g->id !== $game->id) {
			throw new RepositoryException("Hra se zkratkou '$game->slug' již existuje.");
		}
		$this->persist($game);
		return $game;
	}
}
