<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\Model\ArticleRepository;
use Nette\Utils\Paginator;

class ArticlePresenter extends BaseSpecificAdminPresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

	public function renderDefault($page = 1)
	{
		$paginator = new Paginator();
		$paginator->itemCount = $this->articleRepository->countAll();
		$paginator->itemsPerPage = 20;
		$paginator->page = $page;
		if($paginator->page !== $page) {
			$this->redirect('this', array('page' => $paginator->page));
		}
		$this->template->paginator = $paginator;
		$this->template->articles = $this->articleRepository->findOrderedByPage($paginator, '[published] DESC');
	}

	protected function checkPermissions()
	{
		return $this->isAllowed('article');
	}
}
 