<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Tempeus\Security\PasswordHasher;

/**
 * @property-read int $id
 * @property string $nick
 * @property string $password
 * @property string $email
 * @property string $salt
 * @property string $role
 * @property bool $verified = FALSE
 * @property bool $allowedEmails = TRUE (allowed_emails)
 * @property Role[] $roles m:hasMany
 * @property Article[] $articles m:belongsToMany
 * @property ChatRoom[] $allowedRooms m:hasMany(:chat_room_user)
 * @property ForumPost[] $forumPosts m:belongsToMany
 * @property ForumThread[] $forumThreads m:belongsToMany
 * @property ChatMessage[] $chatMessages m:belongsToMany
 * @property ChatSession[] $chatSessions m:belongsToMany
 */
class User extends BaseEntity
{
	public function fixPassword($password)
	{
		$this->password = PasswordHasher::hashPassword($this->nick, $password, $this->salt);
	}

	/**
	 * @return int[]
	 */
	public function getRoomIds()
	{
		if(!count($this->allowedRooms)) return array();
		return array_map(function(ChatRoom $room) {
			return $room->id;
		}, $this->allowedRooms);
	}

	public function getChatTime()
	{
		$time = 0;
		foreach($this->chatSessions as $session) {
			$time += $session->dateChecked - $session->dateStarted;
		}
		return $time;
	}
}
