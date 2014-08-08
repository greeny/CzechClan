<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Utils\Paginator;

class ForumPostRepository extends BaseRepository
{
	public function countByThread(ForumThread $thread)
	{
		return (int) $this->connection->select('COUNT(*) AS count')
			->from($this->getTable())
			->where('[thread_id] = %i', $thread->id)
			->fetch()
			->count;
	}

	public function findByThread(ForumThread $thread, Paginator $paginator)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[thread_id] = %i', $thread->id)
				->limit($paginator->itemsPerPage)
				->offset($paginator->offset)
				->orderBy('[order] ASC')
				->fetchAll()
		);
	}

	public function getNextOrder(ForumThread $thread)
	{
		$max = (int) $this->connection->select('MAX([order]) as [max]')
			->from($this->getTable())
			->where('[thread_id] = %i', $thread->id)
			->fetch()
			->max;
		return $max + 1;
	}
}
