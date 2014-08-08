<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ApiModule;

use Tempeus\Model\ChatSessionRepository;
use Tempeus\Model\UserRepository;

class UserPresenter extends BaseApiPresenter
{
	/** @var UserRepository @inject */
	public $userRepository;

	/** @var ChatSessionRepository @inject */
	public $sessionRepository;

	public function actionLogin()
	{
		$this->allowSession();
		$user = $this->userRepository->find($this->user->id);
		if($user) {
			$this->data->user = $this->entityFactory->createUser($user);
			$this->data->session->key = $this->sessionRepository->startNewSession($user);
		} else {
			$this->data->session = NULL;
		}
	}
}
