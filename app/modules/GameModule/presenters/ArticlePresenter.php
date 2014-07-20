<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Nette\Utils\Paginator;
use Tempeus\Model\ArticleRepository;
use Tempeus\Model\CategoryRepository;

class ArticlePresenter extends BaseGamePresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

	/** @var CategoryRepository @inject */
	public $categoryRepository;

	public function renderDefault($page = 1)
	{
		$paginator = new Paginator();
		$paginator->itemCount = $this->articleRepository->countByGame($this->game);
		$paginator->itemsPerPage = 5;
		$paginator->page = $page;
		if($paginator->page !== $page) {
			$this->redirect('this', array('page' => $paginator->page));
		}
		$this->template->paginator = $paginator;
		$this->template->articles = $this->articleRepository->findByGameOrderedByPage($this->game, $paginator, '[published] DESC');
	}

	public function actionDetail($id)
	{
		if(!$this->template->article = $this->articleRepository->findBySlug($id)) {
			$this->flashError('Článek již neexistuje.');
			$this->redirect('default');
		}
	}

	public function actionCategory($id)
	{
		if(!$this->template->category = $category = $this->categoryRepository->findBySlug($id)) {
			$this->flashError('Kategorie již neexistuje.');
			$this->redirect('default');
		}
		$this->template->articles = $this->articleRepository->findByCategory($category, $this->game);
	}
}
