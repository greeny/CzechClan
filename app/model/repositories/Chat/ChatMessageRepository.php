<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

class ChatMessageRepository extends BaseRepository
{
	public function findAllForRoom($id)
	{
		return $this->findForRoom($id, 24 * 60 * 60);
	}

	public function findNewestForRoom($id)
	{
		return $this->findForRoom($id, 15);
	}

	public function findForRoom($id, $delta)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->where('[chat_room_id] ' . ($id === NULL ? 'IS ' : '= %i'), $id)
				->where('[time] >= ', Time() - $delta)
				->orderBy('[time] DESC')
				->fetchAll()
		);
	}

	public function addMessage($id, ChatSession $session, $text)
	{
		$message = new ChatMessage();
		$message->user = $session->user;
		$message->room = $id;
		$message->time = Time();
		$message->message = $text;
		$this->persist($message);
		return $message;
	}
}
