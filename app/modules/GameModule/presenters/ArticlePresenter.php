<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Nette\Utils\Paginator;
use Tempeus\Model\ArticleRepository;

class ArticlePresenter extends BaseGamePresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

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
}
