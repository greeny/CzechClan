<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\UserModule;

class ProfilePresenter extends BaseUserPresenter
{
	public function renderDetail($id = NULL)
	{
		if($id === NULL && $this->user->isLoggedIn()) {
			$this->template->u = $this->userRepository->find($this->user->getId());
		} else if($id !== NULL) {
			$this->template->u = $this->userRepository->findByNick($id);
		} else {
			$this->redirect(':Public:Dashboard:default');
		}
	}
}
