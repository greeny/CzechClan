<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class ForumPostRepository extends BaseRepository
{
	public function findByThread(ForumThread $thread)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[thread_id] = %i', $thread->id)
				->orderBy('[order] ASC')
				->fetchAll()
		);
	}
}
