<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

class ArticlePresenter extends BaseSpecificAdminPresenter
{
	protected function checkPermissions()
	{
		return $this->isAllowed('article');
	}
}
 