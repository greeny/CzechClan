<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class ChatRoomRepository extends BaseRepository
{
	public function findForUser(User $user)
	{
		$roomIds = $user->getRoomIds();
		$selection = $this->connection->select('*')
			->from($this->getTable())
			->orderBy('[name] ASC');

		if(count($roomIds)) {
			$selection->where('[public] = %b', TRUE, ' OR [id] IN %in', $user->getRoomIds());
		} else {
			$selection->where('[public] = %b', TRUE);
		}

		$rooms = $selection->fetchAll();

		return $this->createEntities($rooms);
	}
}
