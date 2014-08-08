<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ApiModule;

/*
 * * /chat/login - get access token
 * * /chat/state - get all rooms and users
 * /chat/room-open/<id> - opens room <id>, returns room state
 * /chat/room-close/<id> - closes room <id>
 * /chat/room-send/<id>?<message> - sends <message> to room <id>
 * /chat/private-open/<id> - opens private chat with user <id>, returns chat state
 * /chat/private-close/<id> - closes private chat with user <id>
 * /chat/private-send/<id>?<message> - sends <message> to chat with user <id>
 * /chat/search?<q> - searches for <q> and returns results
 * /chat - returns online users and new messages from last time
 */

use Tempeus\Model\ChatMessageRepository;
use Tempeus\Model\ChatRoomRepository;
use Tempeus\Model\ChatSession;
use Tempeus\Model\ChatSessionRepository;

class ChatPresenter extends BaseApiPresenter
{
	/** @var ChatSessionRepository @inject */
	public $sessionRepository;

	/** @var ChatRoomRepository @inject */
	public $roomRepository;

	/** @var ChatMessageRepository @inject */
	public $messageRepository;

	/** @var ChatSession */
	protected $session;

	public function startup()
	{
		parent::startup();
		$this->checkKey();
	}

	public function actionRoomSend($id = NULL, $message = NULL)
	{
		$this->messageRepository->addMessage($id, $this->session, $message);
		$this->actionDefault();
	}

	public function actionState()
	{
		$data = &$this->getData();
		//$user = $this->session->user;
		$data['rooms'] = array(); //$this->entityFactory->createRooms($this->roomRepository->findForUser($user));
		$data['users'] = $this->entityFactory->createUsers($this->userRepository->findAll());
		$data['online_users'] = $this->sessionRepository->findOnlineUserIds();
		$data['messages'] = $this->entityFactory->createMessages($this->messageRepository->findAllForRoom(NULL));
	}

	public function actionDefault()
	{
		$data = &$this->getData();
		$data['messages'] = $this->entityFactory->createMessages($this->messageRepository->findNewestForRoom(NULL));
		$data['online_users'] = $this->sessionRepository->findOnlineUserIds();
	}

	public function actionLogin()
	{
		$data = &$this->getData();
		$user = $this->userRepository->find($this->user->id);
		if($user) {
			$data['session']['user'] = $this->entityFactory->createUser($user);
			$data['session']['key'] = $this->sessionRepository->startNewSession($user);
		} else {
			$this->addError('Musíš být přihlášený pro používání chatu.');
		}
	}

	protected function checkKey()
	{
		$data = &$this->getData();
		$data['links']['chat']['ping'] = $this->link('//:Api:Chat:default');
		$data['links']['chat']['state'] = $this->link('//:Api:Chat:state');
		$data['links']['chat']['new_message'] = $this->link('//:Api:Chat:roomSend', array('message' => '%s'));
		if(!($this->getView() === 'login')) {
			$key = isset($this->params['key']) ? $this->params['key'] : NULL;
			$this->session = $this->sessionRepository->getSession($key);
			if(!$this->session) {
				$this->addError('Tato relace byla ukončena, protože jsi se přihlásil z jiného místa.');
			}
		}
	}
}
