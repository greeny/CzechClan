<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\Controls\Form;
use CzechClan\Model\User;
use CzechClan\Model\UserRepository;
use Nette\Security\AuthenticationException;
use Nette\Utils\Paginator;

class UserPresenter extends BaseGeneralAdminPresenter
{
	/** @var UserRepository @inject */
	public $userRepository;

	/** @var User */
	protected $u;

	public function renderDefault($page = 1)
	{
		$paginator = new Paginator();
		$paginator->itemCount = $this->userRepository->countAll();
		$paginator->itemsPerPage = 20;
		$paginator->page = $page;
		if($paginator->page !== $page) {
			$this->redirect('this', array('page' => $paginator->page));
		}
		$this->template->paginator = $paginator;
		$this->template->users = $this->userRepository->findOrderedByPage($paginator, '[nick] ASC');
	}

	public function actionEdit($id)
	{
		if(!$this->template->u = $this->u = $this->userRepository->findByNick($id)) {
			$this->flashError("Uživatel '$id' neexistuje.");
			$this->redirect('default');
		}
	}

	protected function createUserForm($callable = NULL)
	{
		$form = $this->createForm();
		if($callable) {
			call_user_func($callable, $form);
		}
		$form->addText('email', 'Email')
			->setType('email')
			->addRule($form::EMAIL)
			->setRequired('Prosím zadej email.');
		$form->addCheckbox('verified', 'Uživatel má potvrzený email');
		$form->addSelect('role', 'Role', array(
			'quest' => 'Neregistrovaný uživatel',
			'member' => 'Registrovaný uživatel',
			'owner' => 'Vlastník'
		))->setDefaultValue('member');
		return $form;
	}

	protected function createComponentAddUserForm()
	{
		$form = $this->createUserForm(function(Form $form) {
			$form->addText('nick', 'Nick')
				->setRequired('Prosím zadej nick.');
			$form->addPassword('password', 'Heslo')
				->setRequired('Prosím zadej heslo.');
		});
		$form->addSubmit('addUser', 'Přidat uživatele');
		$form->onSuccess[] = $this->addUserFormSuccess;
		return $form;
	}

	public function addUserFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$this->userRepository->register($v);
			$this->flashSuccess('Uživatel byl přidán.');
			$this->redirect('default');
		} catch(AuthenticationException $e) {
			$this->flashError($e->getMessage());
			$this->refresh();
		}
	}

	protected function createComponentEditUserForm()
	{
		$form = $this->createUserForm();
		$form->setDefaults($this->u->getData());
		$form->addSubmit('editUser', 'Upravit uživatele');
		$form->onSuccess[] = $this->editUserFormSuccess;
		return $form;
	}

	public function editUserFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->u->update($v);
		$this->u->fixPassword($this->u->password);
		$this->userRepository->persist($this->u);
		$this->flashSuccess('Uživatel byl upraven.');
		$this->redirect('default');
	}

	public function handleDelete($id)
	{
		if($user = $this->userRepository->findByNick($id)) {
			$nick = $user->nick;
			$this->userRepository->delete($user);
			$this->flashSuccess("Uživatel '$nick' byl smazán.");
		}
		$this->refresh();
	}
}
 