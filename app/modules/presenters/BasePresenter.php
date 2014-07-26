<?php

namespace Tempeus;

use Tempeus\Controls\Category;
use Tempeus\Controls\User;
use Tempeus\Mail\TempeusMailer;
use Tempeus\Model\GameRepository;
use Tempeus\Model\LogRepository;
use Tempeus\Model\UserRepository;
use Tempeus\Security\Authorizator;
use Tempeus\Templating\Helpers;
use Tempeus\Controls\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends Presenter
{
	/** @var TempeusMailer @inject */
	public $mailer;

	/** @var GameRepository @inject */
	public $gameRepository;

	/** @var UserRepository @inject */
	public $userRepository;

	/** @var LogRepository @inject */
	public $logRepository;

	/** @var Authorizator @inject */
	public $authorizator;

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
			$this->logRepository->addLog('logout', array('user_id' => $this->user->id));
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
			$this->logRepository->addLog('login_success', array('user_id' => $this->user->id));
			$this->flashSuccess('Přihlášení proběhlo úspěšně.');
		} catch(AuthenticationException $e) {
			$this->logRepository->addLog('login_fail', array('user_name' => $v->nick, 'password' => str_repeat('*', strlen($v->password))));
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

	public function hasUserAdminAccess()
	{
		return $this->authorizator->hasAdminAccess($this->user);
	}

	public function isAdminAllowed($game, $resource)
	{
		return $this->authorizator->isAdminAllowed($this->user, $game, $resource);
	}

	protected function createComponentUser()
	{
		return new User();
	}

	protected function createComponentCategory()
	{
		return new Category();
	}
}
