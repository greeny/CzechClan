<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

use Tempeus\Controls\Form;
use Tempeus\Model\Article;
use Tempeus\Model\ArticleRepository;
use Tempeus\Model\CategoryRepository;
use Nette\Utils\Paginator;

class ArticlePresenter extends BaseSpecificAdminPresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

	/** @var CategoryRepository @inject */
	public $categoryRepository;

	/** @var Article */
	protected $article;

	public function renderDefault($page = 1)
	{
		$paginator = new Paginator();
		$paginator->itemCount = $this->articleRepository->countByGame($this->game);
		$paginator->itemsPerPage = 20;
		$paginator->page = $page;
		if($paginator->page !== $page) {
			$this->redirect('this', array('page' => $paginator->page));
		}
		$this->template->paginator = $paginator;
		$this->template->articles = $this->articleRepository->findByGameOrderedByPage($this->game, $paginator, '[published] DESC');
	}

	public function actionEdit($id)
	{
		$this->template->article = $this->article = $this->articleRepository->findBySlug($id);
	}

	protected function createArticleForm()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired('Prosím zadej titulek.');
		$form->addTextArea('content', 'Obsah')
			->setRequired('Prosím zadej obsah.')
			->setAttribute('class', 'ckeditor');
		$form->addSelect('category', 'Kategorie', $this->categoryRepository->fetchPairsByGame($this->game))
			->setPrompt('Žádná kategorie');
		return $form;
	}

	protected function createComponentAddArticleForm()
	{
		$form = $this->createArticleForm();
		$form->addSubmit('addArticle', 'Přidat článek');
		$form->onSuccess[] = $this->addArticleFormSuccess;
		return $form;
	}

	public function addArticleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$v->author = $this->userRepository->find($this->user->id);
		$v->category = $this->categoryRepository->find($v->category);
		$v->published = time();
		$v->game = $this->game;
		$this->articleRepository->fixSlug($article = Article::from($v));
		$this->articleRepository->persist($article);
		$this->flashSuccess('Článek byl vytvořen.');
		$this->redirect('default');
	}

	protected function createComponentEditArticleForm()
	{
		$form = $this->createArticleForm();
		$form->setDefaults($this->article->getData());
		$form->addSubmit('editArticle', 'Upravit článek');
		$form->onSuccess[] = $this->editArticleFormSuccess;
		return $form;
	}

	public function editArticleFormSuccess(Form $form)
	{
		$v = $form->getValues();
		$v->category = $this->categoryRepository->find($v->category);
		$this->article->update($v);
		$this->articleRepository->fixSlug($this->article);
		$this->articleRepository->persist($this->article);
		$this->flashSuccess('Článek byl upraven.');
		$this->redirect('default');
	}

	public function handleDelete($id)
	{
		$article = $this->articleRepository->findBySlug($id);
		if($article) {
			$name = $article->title;
			$this->articleRepository->delete($article);
			$this->flashSuccess("Článek '$name' byl smazán.");
		}
		$this->refresh();
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('article');
	}
}
