<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use LeanMapper\Connection;
use Nette\Security\User as NUser;
use Nette\Utils\ArrayHash;

class ForumFacade extends BaseFacade
{
	/** @var \Tempeus\Model\ForumPostRepository */
	protected $postRepository;

	/** @var \Tempeus\Model\ForumThreadContentRepository */
	protected $threadContentRepository;

	/** @var \Tempeus\Model\ForumThreadRepository */
	protected $threadRepository;

	/** @var \Tempeus\Model\ForumTopicRepository */
	protected $topicRepository;

	/** @var \LeanMapper\Connection */
	protected $connection;

	public function __construct(
		ForumPostRepository $postRepository,
		ForumThreadContentRepository $threadContentRepository,
		ForumThreadRepository $threadRepository,
		ForumTopicRepository $topicRepository,
		Connection $connection)
	{
		$this->postRepository = $postRepository;
		$this->threadContentRepository = $threadContentRepository;
		$this->threadRepository = $threadRepository;
		$this->topicRepository = $topicRepository;
		$this->connection = $connection;
	}

	public function getTopic($topicId)
	{
		if(!$topic = $this->topicRepository->find($topicId)) {
			throw new RepositoryException("Toto téma neexistuje.");
		}
		return $topic;
	}

	public function getThread($threadId)
	{
		return $this->threadRepository->find($threadId);
	}

	public function getPostsInThread(ForumThread $thread)
	{
		return $this->postRepository->findByThread($thread);
	}

	public function getTopics(Game $game, NUser $user, ForumTopic $parent = NULL)
	{
		$return = array();
		if($parent !== NULL) {
			if(!self::canUserSeeTopic($user, $parent)) {
				throw new RepositoryException("Nemáte přístup k tomuto tématu.");
			}
		}
		$topics = $this->topicRepository->findByGame($game, $parent);
		$right = 0;
		foreach($topics as $topic) {
			if($topic->left > $right) {
				$right = $topic->right;
				if(self::canUserSeeTopic($user, $topic)) {
					$return[] = $topic;
				}
			}
		}
		return $return;
	}

	public function moveTopic(ForumTopic $topic, $direction)
	{
		$this->startTransaction();
		$this->lockTables(array('forum_topic', 'forum_topic_role', 'forum_topic_user', 'game', 'forum_topic_view'));

		$direction === 'up' ? $this->topicRepository->moveTopicUp($topic) : $this->topicRepository->moveTopicDown($topic);

		$this->commit();
		$this->unlockTables();
	}

	public function getThreads(ForumTopic $topic)
	{
		return $this->threadRepository->findInTopic($topic);
	}

	public function getBreadcrumbsForTopic(ForumTopic $topic)
	{
		return $this->topicRepository->getBreadcrumbs($topic);
	}

	public function getBreadcrumbsForThread(ForumThread $thread)
	{
		return $this->getBreadcrumbsForTopic($thread->topic);
	}

	/**
	 * @param Game      $game
	 * @param ArrayHash $values
	 * @param NULL|int  $parentId
	 * @throws RepositoryException
	 * @return ForumTopic
	 */
	public function createTopic(Game $game, ArrayHash $values, $parentId = NULL)
	{
		if($parentId !== NULL) {
			/** @var ForumTopic $parent */
			if(!$parent = $this->topicRepository->find($parentId)) {
				throw new RepositoryException("Toto téma bylo smazáno.");
			}
		} else {
			$parent = NULL;
		}
		$this->startTransaction();
		$this->lockTables(array('forum_topic', 'forum_topic_role', 'forum_topic_user'));

		if($parent) {
			$left = $parent->left + 1;
			$this->topicRepository->increaseLeftAndRight($game, $left, 2);
		} else {
			$left = $this->topicRepository->getNextLeft($game);
		}

		$topic = ForumTopic::from($values);
		$topic->left = $left;
		$topic->right = $left + 1;
		$topic->game = $game;
		$this->topicRepository->persist($topic);

		$this->commit();
		$this->unlockTables();
		return $topic;
	}

	public function updateTopic(ForumTopic $topic, ArrayHash $values)
	{
		$topic->update($values);
		$this->topicRepository->persist($topic);
		return $topic;
	}

	protected static function canUserSeeTopic(NUser $user, ForumTopic $topic)
	{
		if($topic->public || in_array('owner', $user->getRoles())) {
			return TRUE;
		}

		if($user->isLoggedIn()) {
			$allowedUsers = array();

			foreach($topic->allowedUsers as $u) {
				$allowedUsers[] = $u->nick;
			}

			if(in_array($user->identity->nick, $allowedUsers)) {
				return TRUE;
			}
		}

		$allowedRoles = array();

		foreach($topic->allowedRoles as $r) {
			$allowedRoles[] = $r->name;
		}

		foreach($user->getRoles() as $role) {
			if(in_array($role, $allowedRoles)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	protected function lockTables(array $tables)
	{
		$query = array();
		foreach($tables as $table) {
			$query[] = '[' . $table . '] WRITE';
		}
		$this->connection->query('LOCK TABLES ' . implode(', ', $query));
	}

	protected function unlockTables()
	{
		$this->connection->query('UNLOCK TABLES');
	}

	protected function startTransaction()
	{
		$this->connection->query('SET autocommit=0');
	}

	protected function commit()
	{
		$this->connection->query('COMMIT');
	}
}
