<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

use Tempeus\Controls\Form;
use Tempeus\Model\Team;
use Tempeus\Model\TeamRepository;

class TeamPresenter extends BaseSpecificAdminPresenter
{
	/** @var TeamRepository @inject */
	public $teamRepository;

	/** @var Team */
	protected $team;

	public function actionEdit($id)
	{
		if(!$this->team = $this->teamRepository->find($id)) {
			$this->flashError('Team nebyl nalezen.');
			$this->redirect('default');
		}
	}

	public function renderEdit()
	{
		$this->template->team = $this->team;
	}

	public function renderDefault()
	{
		$this->template->teams = $this->teamRepository->findByGame($this->game);
	}

	protected function createTeamForm()
	{
		$form = $this->createForm();
		$form->addText('name', 'Jméno')
			->setRequired('Prosím zadej jméno.');
		$form->addText('description', 'Popis')
			->setAttribute('autocomplete', 'off');
		$form->addText('order', 'Pořadí')
			->setType('number')
			->addRule($form::NUMERIC, 'Prosím zadej číslo')
			->setDefaultValue(1);
		return $form;
	}

	protected function createComponentAddTeamForm()
	{
		$form = $this->createTeamForm();
		$form->addSubmit('addTeam', 'Přidat team');
		$form->onSuccess[] = $this->addTeamFormSuccess;
		return $form;
	}

	public function addTeamFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$team = new Team;
		$team->game = $this->game;
		$team->name = $v->name;
		$team->description = $v->description;
		$team->order = $v->order;
		$this->teamRepository->persist($team);
		$this->flashSuccess("Team $team->name byl přidán.");
		$this->redirect('edit', ['id' => $team->id]);
	}

	protected function createComponentEditTeamForm()
	{
		$form = $this->createTeamForm();
		$form->setDefaults($this->team->getData());
		$form->addSubmit('editTeam', 'Upravit team');
		$form->onSuccess[] = $this->editTeamFormSuccess;
		return $form;
	}

	public function editTeamFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->team->update($v);
		$this->teamRepository->persist($this->team);
		$this->flashSuccess('Team byl upraven');
		$this->refresh();
	}

	protected function createComponentAddUserToTeamForm()
	{
		$form = $this->createForm();
		$form->addText('nick', 'Uživatel')
			->setRequired('Prosím zadej nick uživatele');
		$form->addSubmit('addUserToTeam', 'Přidat uživatele do teamu');
		$form->onSuccess[] = $this->addUserToTeamFormSuccess;
		return $form;
	}

	public function addUserToTeamFormSuccess(Form $form)
	{
		$v = $form->getValues();
		if(!$user = $this->userRepository->findByNick($v->nick)) {
			$this->flashError("Uživatel '$v->nick' neexistuje");
			$this->refresh();
		}
		$this->team->addToUsers($user);
		$this->teamRepository->persist($this->team);
		$this->flashSuccess("Uživatel '$user->nick' byl přidán do teamu.");
		$this->refresh();
	}

	public function handleRemoveUserFromTeam($userId)
	{
		if(!$user = $this->userRepository->find($userId)) {
			$this->refresh();
		}
		$this->team->removeFromUsers($user);
		$this->teamRepository->persist($this->team);
		$this->flashSuccess("Uživatel '$user->nick' byl odstraněn z teamu.");
		$this->refresh();
	}

	public function handleDelete($id)
	{
		if($team = $this->teamRepository->find($id)) {
			$this->teamRepository->delete($team);
			$this->flashSuccess('Team byl smazán.');
		}
		$this->refresh();
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('team');
	}
}
