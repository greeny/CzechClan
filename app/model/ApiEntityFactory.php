<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Model;

use Nette\Object;
use Tempeus\ApiModule\BaseApiPresenter;
use Latte\Runtime\Filters;

class ApiEntityFactory extends Object
{
	protected $presenter;

	public $maxDepth = 6;

	public function __construct(BaseApiPresenter $presenter)
	{
		$this->presenter = $presenter;
	}

	public function createUsersFromSessions(array $sessions)
	{
		$users = array();
		foreach($sessions as $session) {
			$users[] = $session->user;
		}
		return $this->createUsers($users);
	}

	public function createUsers(array $users)
	{
		$return = array();
		foreach($users as $user) {
			$return[$user->id] = $this->createUser($user);
		}
		return $return;
	}

	public function createUser(User $user)
	{
		return array(
			'id' => $user->id,
			'nick' => $user->nick,
			'links' => array(
				'profile' => $this->presenter->link('//:User:Profile:detail', array('id' => $user->nick)),
				'chat' => $this->presenter->link('//:Api:Chat:privateOpen', array('id' => $user->nick)),
			),
			'roles' => $this->createRoles($user->roles),
		);
	}

	public function createRoles(array $roles)
	{
		$return = array();
		foreach($roles as $role) {
			$return[] = $this->createRole($role);
		}
		return $return;
	}

	public function createRole(Role $role)
	{
		return array(
			'id' => $role->id,
			'name' => $role->name,
			'label' => $role->displayLabel ? NULL : array(
				'text' => $role->labelText,
				'class' => $role->labelClass,
			),
		);
	}

	public function createRooms(array $rooms)
	{
		$return = array();
		foreach($rooms as $room) {
			$return[$room->id] = $this->createRoom($room);
		}
		return $return;
	}

	public function createRoom(ChatRoom $room)
	{
		return array(
			'id' => $room->id,
			'name' => $room->name,
			'public' => $room->public,
			'links' => array(
			)
		);
	}

	public function createMessages(array $messages)
	{
		$return = array();
		foreach($messages as $message) {
			$return[$message->id] = $this->createMessage($message);
		}
		return $return;
	}

	public function createMessage(ChatMessage $message)
	{
		return array(
			'id' => $message->id,
			'message' => Filters::escapeHtml($message->message),
			'user' => $message->user->id,
			'time' => $message->time * 1000,
		);
	}
}
