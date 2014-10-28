<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class TicketRepository extends BaseRepository
{
	public function findPublicOpen()
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[public] = ?', TRUE)
				->where('[status] = ?', Ticket::STATUS_WAITING)
				->orderBy('[date_created] DESC')
				->fetchAll()
		);
	}

	public function findByOwner(User $user)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[user_id] = ?', $user->id)
				->orderBy('[date_created] DESC')
				->fetchAll()
		);
	}

	public function findByAssignedUser(User $user)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[assigned_user_id] = ?', $user->id)
				->orderBy('[date_created] DESC')
				->fetchAll()
		);
	}

	public function findUnassigned()
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[assigned_user_id] IS NULL')
				->orderBy('[date_created] DESC')
				->fetchAll()
		);
	}
}
