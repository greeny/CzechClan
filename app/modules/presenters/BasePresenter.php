<?php

namespace CzechClan;

use CzechClan\Mail\CzechClanMailer;
use CzechClan\Model\GameRepository;
use CzechClan\Templating\Helpers;
use CzechClan\Controls\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;
use Nette\Utils\Html;

abstract class BasePresenter extends Presenter
{
	/** @var CzechClanMailer @inject */
	public $mailer;

	/** @var GameRepository @inject */
	public $gameRepository;

	public function createForm()
	{
		$form = new Form();
		return $form;
	}

	public function beforeRender()
	{
		parent::beforeRender();
		Helpers::prepareTemplate($this->template);
		$this->template->games = $this->gameRepository->findForDashboard();
	}

	public function getParamByName($name)
	{
		return $this->params[$name];
	}

	public function handleLogout()
	{
		if($this->user->isLoggedIn()) {
			$this->user->logout(TRUE);
			$this->flashSuccess('Byl jsi odhlášen.');
		}
		$this->redirect(":Public:Dashboard:default");
	}

	protected function createComponentSignInForm()
	{
		$form = $this->createForm();
		$form->elementPrototype->addAttributes(array('class' => 'form-inline'));
		$form->addText('nick', 'Nick')
			->setRequired('Prosím zadej svůj nick.')
			->setAttribute('placeholder', 'Nick');
		$form->addPassword('password', 'Heslo')
			->setRequired('Prosím zadej svoje heslo.')
			->setAttribute('placeholder', 'Heslo');
		$form->addSubmit('signIn');
		$form->onSuccess[] = $this->signInFormSuccess;
		return $form;
	}

	public function signInFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$this->user->login($v->nick, $v->password);
			$this->user->setExpiration('+14 days', FALSE, TRUE);
			$this->flashSuccess('Přihlášení proběhlo úspěšně.');
		} catch(AuthenticationException $e) {
			$this->flashError($e->getMessage());
		}
		$this->refresh();
	}

	public function flashError($message)
	{
		return $this->flashMessage($message, 'danger');
	}

	public function flashSuccess($message)
	{
		return $this->flashMessage($message, 'success');
	}

	public function refresh() {
		$this->redirect('this');
	}
}
