<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\UserModule;

use Nette\Mail\Message;
use Tempeus\Controls\Form;
use Tempeus\Mail\TempeusMailer;
use Tempeus\Model\RepositoryException;
use Tempeus\Model\User;

class ProfilePresenter extends BaseUserPresenter
{
	/** @var User */
	protected $u;

	/** @var TempeusMailer @inject */
	public $mailer;

	public function actionDetail($id = NULL)
	{
		if($id === NULL && $this->user->isLoggedIn()) {
			$this->u = $this->userRepository->find($this->user->getId());
		} else if($id !== NULL) {
			$this->u = $this->userRepository->findByNick($id);
		} else {
			$this->redirect(':Public:Dashboard:default');
		}
	}

	public function renderDetail($id = NULL)
	{
		$this->template->u = $this->u;
	}

	public function actionVerify($id, $code)
	{
		try {
			$this->userRepository->verifyUser($this->userRepository->findByNick($id), $code);
			$this->logRepository->addLog('verify_success', array('user_id' => $id, 'code' => $code));
			$this->flashSuccess('Ověření proběhlo úspěšně, nyní se můžete přihlásit.');
			$this->redirect(':Public:Dashboard:default');
		} catch(RepositoryException $e) {
			$this->logRepository->addLog('verify_fail', array('user_id' => $id, 'code' => $code));
			$this->flashError($e->getMessage());
			$this->redirect(':Public:Dashboard:default');
		}
	}

	protected function createComponentContactUserForm()
	{
		$form = $this->createForm();
		$form->addText('subject', 'Předmět')
			->setRequired('Prosím zadej předmět.');
		$form->addTextArea('body', 'Zpráva')
			->setAttribute('class', 'ckeditor');
		$form->addSubmit('contactUser', 'Odeslat email');
		$form->onSuccess[] = $this->contactUserFormSuccess;
		return $form;
	}

	public function contactUserFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$mail = new Message();
		/** @var User $user */
		$user = $this->userRepository->find($this->user->id);
		$mail->setFrom($user->allowedEmails ? $user->email : 'mailer@tempeus.eu', $user->nick . ' (z Tempeus.eu)');
		$mail->addTo($this->u->email);
		$mail->setSubject($v->subject);

		$body = "Ahoj {$this->u->nick}!<br>{$user->nick} tě právě kontaktoval pomocí formuláře na Tempeus.eu! Přečti si, co ti píše:<hr>" . $v->body;

		if($user->allowedEmails) {
			$body .= "<hr>Pokud chceš odpovědět uživateli {$user->nick}, odpověz na tento email.";
		} else {
			$body .= "<hr>Pokud chceš odpovědět uživateli {$user->nick}, kontaktuj ho na stránkách Tempeus.eu.";
		}

		$body .= '<br><br>Admin team Tempeus.eu';

		$mail->setHtmlBody($body);
		$this->mailer->send($mail);
		$this->flashSuccess('Email byl úspěšně odeslán.');
		$this->refresh();
	}
}
