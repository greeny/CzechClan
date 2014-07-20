<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Utils\Strings;

class InformationRepository extends BaseRepository
{
	public function findBySlug($slug, Game $game)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[slug] = %s', $slug)
			->where('[game_id] = %i', $game->id)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function fixSlug(Information $information)
	{
		$slug = $information->slug = Strings::webalize($information->title);
		$i = 1;
		while($a = $this->findBySlug($information->slug, $information->game)) {
			if(!$information->isDetached() && $a->id === $information->id) {
				break;
			}
			$information->slug = $slug . '-' . $i++;
		}
	}

	public function findAllByGame(Game $game)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[game_id] = %i', $game->id)
				->where('[active] = ?', TRUE)
				->orderBy('[order] ASC, [title] ASC')
				->fetchAll()
		);
	}
}
