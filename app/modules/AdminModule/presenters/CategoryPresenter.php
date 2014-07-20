<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

use Tempeus\Controls\Form;
use Tempeus\Model\Category;
use Tempeus\Model\CategoryRepository;
use Nette\Utils\Paginator;

class CategoryPresenter extends BaseSpecificAdminPresenter
{
    /** @var CategoryRepository @inject */
    public $categoryRepository;

	/** @var Category */
	protected $category;

    public function renderDefault($page = 1)
    {
        $paginator = new Paginator();
        $paginator->itemCount = $this->categoryRepository->countByGame($this->game);
        $paginator->itemsPerPage = 20;
        $paginator->page = $page;
        if($paginator->page !== $page) {
            $this->redirect('this', array('page' => $paginator->page));
        }
        $this->template->paginator = $paginator;
        $this->template->categories = $this->categoryRepository->findByGameOrderedByPage($this->game, $paginator, '[name] ASC');
    }

	public function actionEdit($id)
	{
		if(!$this->category = $this->template->category = $this->categoryRepository->findBySlug($id)) {
			$this->flashError('Tato kategorie neexistuje.');
			$this->redirect('default');
		}
	}

	protected function createCategoryForm()
    {
        $form = $this->createForm();
        $form->addText('name', 'Jméno')
	        ->setRequired('Prosím zadej jméno.');
        return $form;
    }

	protected function createComponentAddCategoryForm()
	{
		$form = $this->createCategoryForm();
		$form->addSubmit('addCategory', 'Přidat kategorii');
		$form->onSuccess[] = $this->addCategoryFormSuccess;
		return $form;
	}

	public function addCategoryFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->categoryRepository->fixSlug($category = Category::from($v));
		$category->game = $this->game;
		$this->categoryRepository->persist($category);
		$this->flashSuccess('Kategorie byla vytvořena.');
		$this->redirect('default');
	}

	protected function createComponentEditCategoryForm()
	{
		$form = $this->createCategoryForm();
		$form->setDefaults($this->category->getData());
		$form->addSubmit('editCategory', 'Upravit kategorii');
		$form->onSuccess[] = $this->editCategoryFormSuccess;
		return $form;
	}

	public function editCategoryFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$this->category->update($v);
		$this->categoryRepository->fixSlug($this->category);
		$this->categoryRepository->persist($this->category);
		$this->flashSuccess('Kategorie byla upravena.');
		$this->redirect('default');
	}

	public function handleDelete($id)
	{
		if($category = $this->categoryRepository->findBySlug($id)) {
			$name = $category->name;
			$this->categoryRepository->delete($category);
			$this->flashSuccess("Kategorie '$name' byla smazána.");
		}
		$this->refresh();
	}

	protected function checkPermissions()
    {
        return $this->isAllowed('article');
    }
}
