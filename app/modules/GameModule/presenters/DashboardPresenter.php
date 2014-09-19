<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\Model\ArticleRepository;

class DashboardPresenter extends BaseGamePresenter
{
	/** @var ArticleRepository @inject */
	public $articleRepository;

	public function renderDefault()
	{
		$this->template->newestArticle = $this->articleRepository->findNewest($this->game);
		$this->template->lastForumThreads = $this->forumFacade->getLastPosts($this->game);
	}
}
