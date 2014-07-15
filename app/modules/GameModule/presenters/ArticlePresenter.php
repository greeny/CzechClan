<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\GameModule;

use CzechClan\Model\ArticleRepository;

class ArticlePresenter extends BaseGamePresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

	public function actionDetail($id)
	{
		if(!$this->template->article = $this->articleRepository->findBySlug($id)) {
			$this->flashError('Článek již neexistuje.');
			$this->redirect('default');
		}
	}
}
