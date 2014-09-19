<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class ForumThreadRepository extends BaseRepository
{
	public function findInTopic(ForumTopic $topic)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable() . '_view')
				->where('[topic_id] = %i', $topic->id)
				->orderBy('[pinned] DESC, [date_created] DESC')
				->fetchAll()
		);
	}

	public function findNewestInTopics(array $ids)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable() . '_view')
				->where('[topic_id] IN %in', $ids)
				->orderBy('[last_post_date] DESC')
				->limit(5)
				->fetchAll()
		);
	}
}
