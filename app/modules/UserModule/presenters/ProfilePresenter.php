<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\UserModule;

use Tempeus\Model\RepositoryException;

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

	public function actionVerify($id, $code)
	{
		try {
			$this->userRepository->verifyUser($this->userRepository->findByNick($id), $code);
			$this->flashSuccess('Ověření proběhlo úspěšně, nyní se můžete přihlásit.');
			$this->redirect(':Public:Dashboard:default');
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->redirect(':Public:Dashboard:default');
		}
	}
}
