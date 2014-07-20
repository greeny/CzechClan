<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

use Tempeus\Controls\Form;
use Tempeus\Model\InformationRepository;
use Tempeus\Model\Information;

class InformationPresenter extends BaseSpecificAdminPresenter
{
	/** @var InformationRepository @inject */
	public $informationRepository;

	/** @var Information */
	protected $information;

	public function renderDefault()
	{
		$this->template->informations = $this->informationRepository->findAllByGame($this->game);
	}

	public function actionEdit($id)
	{
		if(!$this->template->information = $this->information = $this->informationRepository->findBySlug($id, $this->game)) {
			$this->flashError('Informace nebyla nalezena.');
			$this->redirect('default');
		}
	}

	protected function createInformationForm()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired('Prosím zadej titulek.');
		$form->addTextArea('content', 'Obsah')
			->setRequired('Prosím zadej obsah')
			->setAttribute('class', 'ckeditor');
		$form->addText('order', 'Pořadí')
			->setType('number')
			->addRule($form::NUMERIC, 'Pořadí musí být číslo.')
			->addRule($form::MIN, 'Pořadí musí být větší nebo rovno nule.', 0)
			->setDefaultValue(0);
		$form->addCheckbox('active', 'Aktivní')
			->setDefaultValue(TRUE);
		return $form;
	}

	protected function createComponentAddInformationForm()
	{
		$form = $this->createInformationForm();
		$form->addSubmit('addInformation', 'Přidat');
		$form->onSuccess[] = $this->addInformationFormSuccess;
		return $form;
	}

	public function addInformationFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$v->game = $this->game;
		$this->informationRepository->fixSlug($article = Information::from($v));
		$this->informationRepository->persist($article);
		$this->flashSuccess('Informace byla vytvořena.');
		$this->redirect('default');
	}

	protected function createComponentEditInformationForm()
	{
		$form = $this->createInformationForm();
		$form->setDefaults($this->information->getData());
		$form->addSubmit('editInformation', 'Upravit');
		$form->onSuccess[] = $this->editInformationFormSuccess;
		return $form;
	}

	public function editInformationFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->information->update($v);
		$this->informationRepository->fixSlug($this->information);
		$this->informationRepository->persist($this->information);
		$this->flashSuccess('Informace byla upravena.');
		$this->redirect('default');
	}

	public function handleDelete($id)
	{
		if($information = $this->informationRepository->findBySlug($id, $this->game)) {
			$name = $information->title;
			$this->informationRepository->delete($information);
			$this->flashSuccess("Informace '$name' byla smazána.");
		}
		$this->refresh();
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('information');
	}
}
