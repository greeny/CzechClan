<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\Controls\Form;
use CzechClan\Model\Game;
use CzechClan\Model\RepositoryException;

class GamePresenter extends BaseGeneralAdminPresenter
{
	/** @var Game */
	protected $game;

	public function actionEdit($id)
	{
		if(!$this->template->game = $this->game = $this->gameRepository->findBySlug($id)) {
			$this->flashError('Tato hra neexistuje.');
			$this->redirect('default');
		}
	}

	protected function createGameForm()
	{
		$form = $this->createForm();
		$form->addText('name', 'Jméno hry')
			->setRequired('Prosím zadej jméno hry.');
		$form->addText('slug', 'Zkratka')
			->setRequired('Prosím zadej zkratku');
		$form->addText('order', 'Pořadí')
			->setType('number')
			->addRule($form::MIN, 'Pořadí musí být větší nebo rovno nule.', 0)
			->setDefaultValue(0);
		$form->addCheckbox('active', 'Aktivní')
			->setDefaultValue(TRUE);
		return $form;
	}

	protected function createComponentAddGameForm()
	{
		$form = $this->createGameForm();
		$form->addSubmit('addGame', 'Přidat hru');
		$form->onSuccess[] = $this->addGameFormSuccess;
		return $form;
	}

	public function addGameFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$game = $this->gameRepository->addGame($v);
			$this->flashSuccess("Hra '$game->name' byla úspěšně přidána.");
			$this->redirect('default');
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->refresh();
		}
	}

	protected function createComponentEditGameForm()
	{
		$form = $this->createGameForm();
		$form->setDefaults($this->game->getData());
		$form->addSubmit('editGame', 'Upravit hru');
		$form->onSuccess[] = $this->editGameFormSuccess;
		return $form;
	}

	public function editGameFormSuccess(Form $form)
	{
		$v = $form->getValues();
		try {
			$game = $this->gameRepository->updateGame($this->game, $v);
			$this->flashSuccess("Hra '$game->name' byla upravena.");
			$this->redirect('default');
		} catch(RepositoryException $e) {
			$this->flashError($e->getMessage());
			$this->refresh();
		}
	}

	public function handleDelete($id)
	{
		$game = $this->gameRepository->findBySlug($id);
		if($game) {
			$name = $game->name;
			$this->gameRepository->delete($game);
			$this->flashSuccess("Hra '$name' byla smazána.");
		}
		$this->refresh();
	}
}
