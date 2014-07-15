<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\Model;

use Nette\Utils\Paginator;
use Nette\Utils\Strings;

class CategoryRepository extends BaseRepository
{
    public function findBySlug($slug)
    {
        $row = $this->connection->select('*')
            ->from($this->getTable())
            ->where('[slug] = %s', $slug)
            ->fetch();
        return $row ? $this->createEntity($row) : NULL;
    }

    public function fixSlug(Category $category)
    {
        $slug = $category->slug = Strings::webalize($category->name);
        $i = 1;
        while($c = $this->findBySlug($category->slug)) {
	        if(!$category->isDetached() && $c->id === $category->id) {
		        break;
	        }
            $category->slug = $slug . '-' . $i++;
        }
    }
    
	public function fetchPairs()
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
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

	public function fetchPairsByGame(Game $game)
	{
		return $this->connection->select('*')
			->from($this->getTable())
			->where('[game_id] = %i', $game->id)
			->orderBy('[name] ASC')
			->fetchPairs('id', 'name');
	}
}
