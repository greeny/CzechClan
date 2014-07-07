<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\UserModule;

use CzechClan\Controls\Form;
use CzechClan\Model\UserRepository;
use Nette\Mail\Message;
use Nette\Security\AuthenticationException;

class SignPresenter extends BaseUserPresenter
{
	/** @var UserRepository @inject */
	public $userRepository;

	protected function createComponentSignUpForm()
	{
		$form = $this->createForm();
		$form->addText('nick', 'Nick')
			->setRequired('Prosím zadej svůj nick.');
		$form->addPassword('password', 'Heslo')
			->setRequired('Prosím zadej svoje heslo.');
		$form->addPassword('password_check', 'Kontrola hesla')
			->addRule($form::EQUAL, 'Hesla se neshodují', $form['password']);
		$form->addText('email', 'Email')
			->setRequired('Prosím zadej svůj email.')
			->addRule($form::EMAIL, 'Zadej prosím platný email.');
		$form->addSubmit('signUp', 'Registrovat se');
		$form->onSuccess[] = $this->signUpFormSuccess;
		return $form;
	}

	public function signUpFormSuccess(Form $form)
	{
		$v = $form->getValues();
		unset($v->password_check);
		try {
			$user = $this->userRepository->register($v);
			$mail = new Message();
			$mail->addTo($v->email);
			$mail->setHtmlBody("Ahoj $user->nick!<br><br>Tvoje registrace na webu CzechClanu proběhla úspěšně.
			Pro ověření emailu klikni na tento odkaz: ".$this->link('\\\\:User:Profile:verify', array('id' => $user->nick,
					'code' => $user->salt)).'.<br><br>Hodně štěstí ve hře ti přeje CzechClan Admin Team.');
			$this->mailer->send($mail);
			$this->flashSuccess('Registrace proběhla úspěšně, zkontrolujte si svůj email.');
		} catch(AuthenticationException $e) {
			$this->flashError($e->getMessage());
			$this->refresh();
		}
	}
}