<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Utils\Random;

class ChatSessionRepository extends BaseRepository
{
	public function endCurrentSession(User $user)
	{
		$this->connection->query('LOCK TABLES [chat_session] WRITE');
		$this->endAllSessions($user);
		$this->connection->query('UNLOCK TABLES');
	}

	public function startNewSession(User $user)
	{
		$this->connection->query('LOCK TABLES [chat_session] WRITE');
		$this->endAllSessions($user);

		$session = new ChatSession();
		$session->user = $user;
		$session->dateStarted = $session->dateChecked = Time();
		$session->dateEnded = NULL;
		$session->key = Random::generate(20, 'a-zA-Z0-9_');
		$this->persist($session);
		$this->connection->query('UNLOCK TABLES');
		return $session->key;
	}

	public function getSession($key)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('[key] = %s', $key)
			->where('[date_ended] IS NULL')
			->fetch();
		if($row) {
			$session = $this->createEntity($row);
			$session->dateChecked = Time();
			$this->persist($session);
			return $session;
		} else {
			return NULL;
		}
	}

	protected function endAllSessions(User $user)
	{
		$sessions = $this->createEntities($this->connection->select('*')
			->from($this->getTable())
			->where('[user_id] = %i', $user->id)
			->where('[date_ended] IS NULL')
			->fetchAll()
		);

		foreach($sessions as $s) {
			$s->dateEnded = Time();
			$this->persist($s);
		}
	}

	public function findOnlineUserIds()
	{
		return array_values($this->connection->select('*')
			->from($this->getTable())
			->where('[date_ended] IS NULL')
			->where('[date_checked] >= %i', Time() - 20)
			->fetchPairs('id', 'user_id')
		);
	}
}
