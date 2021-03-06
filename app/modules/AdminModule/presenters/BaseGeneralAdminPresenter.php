<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\AdminModule;

abstract class BaseGeneralAdminPresenter extends BaseAdminPresenter
{
	protected function checkSlug()
	{
		if($this->slug !== 'general') {
			$this->redirect(':Admin:Dashboard:default', array('slug' => NULL));
		}
	}

	public function isAllowed($resource = NULL)
	{
		return $this->isGameAllowed('general', $resource);
	}
}
