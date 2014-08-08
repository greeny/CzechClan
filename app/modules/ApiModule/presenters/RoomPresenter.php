<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ApiModule;

use Tempeus\Model\ChatMessageRepository;
use Tempeus\Model\ChatRoomRepository;

class RoomPresenter extends BaseApiPresenter
{
	/** @var ChatRoomRepository @inject */
	public $roomRepository;

	/** @var ChatMessageRepository @inject */
	public $messageRepository;

	public function actionList()
	{
		$this->data->rooms = $this->entityFactory->createRooms($this->roomRepository->findAll());
	}

	public function actionMessages($id)
	{
		$this->data->room = $this->entityFactory->createRoom($this->roomRepository->find($id));
		$this->data->messages = $this->entityFactory->createMessages($this->messageRepository->findAllForRoom($id));
	}

	public function actionNewest($id)
	{
		$this->data->room = $this->entityFactory->createRoom($this->roomRepository->find($id));
		$this->data->messages = $this->entityFactory->createMessages($this->messageRepository->findNewestForRoom($id));
	}

	public function actionUsers($id)
	{
		$this->data->users = $this->entityFactory->createUsersFromSessions($this->sessionRepository->findActiveOnChat());
	}
}
