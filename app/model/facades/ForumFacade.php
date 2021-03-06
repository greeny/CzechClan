<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use LeanMapper\Connection;
use Nette\InvalidStateException;
use Nette\Security\User as NUser;
use Nette\Utils\ArrayHash;
use Nette\Utils\Paginator;

class ForumFacade extends BaseFacade
{
	/** @var \Tempeus\Model\ForumPostRepository */
	protected $postRepository;

	/** @var \Tempeus\Model\ForumPostContentRepository */
	protected $postContentRepository;

	/** @var \Tempeus\Model\ForumThreadRepository */
	protected $threadRepository;

	/** @var \Tempeus\Model\ForumTopicRepository */
	protected $topicRepository;

	/** @var \LeanMapper\Connection */
	protected $connection;

	public function __construct(
		ForumPostRepository $postRepository,
		ForumPostContentRepository $postContentRepository,
		ForumThreadRepository $threadRepository,
		ForumTopicRepository $topicRepository,
		Connection $connection)
	{
		$this->postRepository = $postRepository;
		$this->postContentRepository = $postContentRepository;
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
		if(!$thread = $this->threadRepository->find($threadId)) {
			throw new RepositoryException("Toto vlákno neexistuje.");
		}
		return $thread;
	}

	public function getPost($postId)
	{
		if(!$post = $this->postRepository->find($postId)) {
			throw new RepositoryException("Tento příspěvek neexistuje.");
		}
		return $post;
	}

	public function getPostsInThread(ForumThread $thread, Paginator $paginator)
	{
		return $this->postRepository->findByThread($thread, $paginator);
	}

	public function countPostsInThread(ForumThread $thread)
	{
		return $this->postRepository->countByThread($thread);
	}

	public function getLastPosts(Game $game)
	{
		$topicIds = $this->topicRepository->getTreeIds($game, NULL);
		$ids = [];
		foreach($topicIds as $topicId) {
			$ids[] = $topicId->id;
		}
		return $this->threadRepository->findNewestInTopics($ids);
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

	public function deleteTopic(ForumTopic $topic, $isRecursive = FALSE)
	{
		if(count($this->getThreads($topic))) {
			throw new InvalidStateException("Téma '$topic->title'" . ($isRecursive ? ', které je v tomto tématu,' : '') ." stále obsahuje vlákna.");
		}
		foreach($this->topicRepository->findByGame($topic->game, $topic) as $child) {
			$this->deleteTopic($child, TRUE);
		}
		if(!$isRecursive) {
			$left = $topic->left;
			$right = $topic->right;
			$this->topicRepository->decreaseLeftAndRight($topic->game, $right + 1, $right - $left + 1);
		}
		$this->topicRepository->delete($topic);
	}


	/**
	 * @param User       $user
	 * @param ForumTopic $topic
	 * @param ArrayHash  $values
	 * @return ForumThread
	 */
	public function createThread(User $user, ForumTopic $topic, ArrayHash $values)
	{
		$this->startTransaction();
		$text = $values->text;
		unset($values->text);
		$thread = ForumThread::from($values);
		$thread->topic = $topic;
		$thread->dateCreated = Time();
		$thread->user = $user;
		$this->threadRepository->persist($thread);

		$postContent = new ForumPostContent();
		$postContent->post = NULL;
		$postContent->text = $text;
		$this->postContentRepository->persist($postContent);

		$post = new ForumPost();
		$post->datePosted = Time();
		$post->order = 1;
		$post->thread = $thread;
		$post->title = $thread->title;
		$post->user = $user;
		$this->postRepository->persist($post);

		$postContent->post = $post;
		$this->postContentRepository->persist($postContent);
		$this->commit();
		return $thread;
	}

	public function updateThread(ForumThread $thread, ArrayHash $values)
	{
		$thread->update($values);
		$this->threadRepository->persist($thread);
		return $thread;
	}

	/**
	 * @param ForumThread[] $threads
	 */
	public function deleteThreads(array $threads)
	{
		$posts = [];
		foreach($threads as $thread) {
			$count = $this->postRepository->countByThread($thread);
			$paginator = new Paginator;
			$paginator->itemCount = $paginator->itemsPerPage = $count;
			foreach($this->getPostsInThread($thread, $paginator) as $post) {
				$posts[] = $post;
			}
		}
		$this->deletePosts($posts);
		foreach($threads as $thread) {
			$this->threadRepository->delete($thread);
		}
	}

	public function createPost(User $user, ForumThread $thread, ArrayHash $values)
	{
		$this->startTransaction();
		$this->lockTables(array('forum_post', 'forum_post_content'));

		$postContent = new ForumPostContent();
		$postContent->post = NULL;
		$postContent->text = $values->text;
		$this->postContentRepository->persist($postContent);

		$post = new ForumPost();
		$post->datePosted = Time();
		$post->order = $this->postRepository->getNextOrder($thread);
		$post->thread = $thread;
		$post->title = $values->title;
		$post->user = $user;
		$this->postRepository->persist($post);

		$postContent->post = $post;
		$this->postContentRepository->persist($postContent);

		$this->unlockTables();
		$this->commit();

		return $post;
	}

	public function updatePost(ForumPost $post, ArrayHash $values)
	{
		$post->title = $values->title;
		$post->content->text = $values->text;
		$post->timesEdited++;
		$post->dateLastEdit = Time();
		$this->postRepository->persist($post);
		$this->postContentRepository->persist($post->content);
	}

	/**
	 * @param ForumPost[] $posts
	 */
	public function deletePosts(array $posts)
	{
		$ids = [];
		$contentIds = [];
		foreach($posts as $post) {
			$ids[] = $post->id;
			$contentIds[] = $post->content->id;
		}
		$this->connection->delete('forum_post_content')->where('[id] IN ?', $contentIds);
		$this->connection->delete('forum_post')->where('[id] IN ?', $ids);
	}

	public function pinThread(ForumThread $thread)
	{
		$thread->pinned = !$thread->pinned;
		$this->threadRepository->persist($thread);
	}

	public function lockThread(ForumThread $thread)
	{
		$thread->locked = !$thread->locked;
		$this->threadRepository->persist($thread);
	}

	public static function canUserSeeTopic(NUser $user, ForumTopic $topic)
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

	public function addUserToTopic(User $user, ForumTopic $topic)
	{
		foreach($topic->allowedUsers as $u) {
			if($u->id === $user->id) return;
		}
		$topic->addToAllowedUsers($user);
		$this->topicRepository->persist($topic);
	}

	public function addRoleToTopic(Role $role, ForumTopic $topic)
	{
		foreach($topic->allowedRoles as $r) {
			if($r->id === $role->id) return;
		}
		$topic->addToAllowedRoles($role);
		$this->topicRepository->persist($topic);
	}

	public function removeUserFromTopic(User $user, ForumTopic $topic)
	{
		$topic->removeFromAllowedUsers($user);
		$this->topicRepository->persist($topic);
	}

	public function removeRoleFromTopic(Role $role, ForumTopic $topic)
	{
		$topic->removeFromAllowedRoles($role);
		$this->topicRepository->persist($topic);
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
