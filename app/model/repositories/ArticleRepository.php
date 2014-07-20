<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Utils\Paginator;
use Nette\Utils\Strings;

class ArticleRepository extends BaseRepository
{
	public function findBySlug($slug)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[slug] = %s', $slug)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function fixSlug(Article $article)
	{
		$slug = $article->slug = Strings::webalize($article->title);
		$i = 1;
		while($a = $this->findBySlug($article->slug)) {
			if(!$article->isDetached() && $a->id === $article->id) {
				break;
			}
			$article->slug = $slug . '-' . $i++;
		}
	}

	public function countByGame(Game $game)
	{
		return $this->connection->select('COUNT(*) AS count')
			->from($this->getTable())
			->where('[game_id] = %i', $game->id)
			->fetch()
			->count;
	}

	public function findAllByGame(Game $game)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = %i', $game->id)
				->fetchAll()
		);
	}

	public function findByCategory(Category $category, Game $game)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = %i', $game->id)
				->where('[category_id] = %i', $category->id)
				->orderBy('[published] DESC')
				->fetchAll()
		);
	}

	public function findNewest(Game $game)
	{
		$row = $this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = %i', $game->id)
				->orderBy('[published] DESC')
				->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function findByGameOrderedByPage(Game $game, Paginator $paginator, $orderBy)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = %i', $game->id)
				->limit($paginator->itemsPerPage)
				->offset($paginator->offset)
				->orderBy($orderBy)
				->fetchAll()
		);
	}
}
