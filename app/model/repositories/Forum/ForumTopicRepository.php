<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class ForumTopicRepository extends BaseRepository
{
	public function findLast(Game $game)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable() . '_view')
				->where('[game_id] = %i', $game->id)
				->limit(5)
				->orderBy('[last_post_date] DESC')
				->fetchAll()
		);
	}

	/**
	 * @param Game       $game
	 * @param ForumTopic $parent
	 * @return ForumTopic[]
	 */
	public function findByGame(Game $game, ForumTopic $parent = NULL)
	{
		$selection = $this->connection->select('*')
			->from($this->getTable() . '_view')
			->where('[game_id] = %i', $game->id)
			->orderBy('[left] ASC');

		if($parent) {
			$selection->where('[left] > %i', $parent->left)
				->where('[right] < %i', $parent->right);
		}

		return $this->createEntities($selection->fetchAll());
	}

	public function getBreadcrumbs(ForumTopic $topic)
	{
		return $this->createEntities(
			$selection = $this->connection->select('*')
				->from($this->getTable() . '_view')
				->where('[game_id] = %i', $topic->game->id)
				->where('[left] <= %i', $topic->left)
				->where('[right] >= %i', $topic->right)
				->orderBy('[left] ASC')
				->fetchAll()
		);
	}

	public function getNextLeft(Game $game)
	{
		$right = (int) $this->connection->select('MAX([right]) as [right]')
			->from($this->getTable())
			->where('[game_id] = %i', $game->id)
			->fetch()->right;
		return $right + 1;
	}

	public function increaseLeftAndRight(Game $game, $min, $increase)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [left] = [left] + ? WHERE [left] >= ? AND [game_id] = ?',
			$increase, $min, $game->id);
		$this->connection->query(
			'UPDATE [forum_topic] SET [right] = [right] + ? WHERE [right] >= ? AND [game_id] = ?',
			$increase, $min, $game->id);
	}

	public function decreaseLeftAndRight(Game $game, $min, $decrease)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [left] = [left] - ? WHERE [left] >= ? AND [game_id] = ?',
			$decrease, $min, $game->id);
		$this->connection->query(
			'UPDATE [forum_topic] SET [right] = [right] - ? WHERE [right] >= ? AND [game_id] = ?',
			$decrease, $min, $game->id);
	}

	public function increaseLeft(array $ids, $delta)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [left] = [left] + ? WHERE [id] IN %in',
			$delta, $ids);
	}


	public function increaseRight(array $ids, $delta)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [right] = [right] + ? WHERE [id] IN %in',
			$delta, $ids);
	}

	public function decreaseLeft(array $ids, $delta)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [left] = [left] - ? WHERE [id] IN %in',
			$delta, $ids);
	}


	public function decreaseRight(array $ids, $delta)
	{
		$this->connection->query(
			'UPDATE [forum_topic] SET [right] = [right] - ? WHERE [id] IN %in',
			$delta, $ids);
	}

	public function getByLeft(Game $game, $left)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[game_id] = %i', $game->id)
			->where('[left] = %i', $left)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function getByRight(Game $game, $right)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[game_id] = %i', $game->id)
			->where('[right] = %i', $right)
			->fetch();
		return $row ? $this->createEntity($row) : NULL;
	}

	public function getTreeIds(Game $game, ForumTopic $root = NULL)
	{
		$selection = $this->connection->select('id')
			->from($this->getTable() . '_view')
			->where('[game_id] = %i', $game->id)
			->orderBy('[left] ASC');

		if($root) {
			$selection->where('[left] >= %i', $root->left)
				->where('[right] <= %i', $root->right);
		}

		return $selection->fetchAll();
	}

	public function moveTopicDown(ForumTopic $topic)
	{
		if(!$other = $this->getByLeft($topic->game, $topic->right + 1)) {
			return;
		}
		$deltaUp = $topic->right - $topic->left + 1;
		$deltaDown = $other->right - $other->left + 1;
		$upTreeIds = $this->getTreeIds($topic->game, $other);
		$downTreeIds = $this->getTreeIds($topic->game, $topic);
		$this->increaseLeft($downTreeIds, $deltaDown);
		$this->increaseRight($downTreeIds, $deltaDown);
		$this->decreaseLeft($upTreeIds, $deltaUp);
		$this->decreaseRight($upTreeIds, $deltaUp);
	}

	public function moveTopicUp(ForumTopic $topic)
	{
		if(!$other = $this->getByRight($topic->game, $topic->left - 1)) {
			return;
		}
		$deltaUp = $other->right - $other->left + 1;
		$deltaDown = $topic->right - $topic->left + 1;
		$upTreeIds = $this->getTreeIds($topic->game, $topic);
		$downTreeIds = $this->getTreeIds($topic->game, $other);
		$this->increaseLeft($downTreeIds, $deltaDown);
		$this->increaseRight($downTreeIds, $deltaDown);
		$this->decreaseLeft($upTreeIds, $deltaUp);
		$this->decreaseRight($upTreeIds, $deltaUp);
	}
}
