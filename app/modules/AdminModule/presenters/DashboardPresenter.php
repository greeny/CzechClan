<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

class DashboardPresenter extends BaseAdminPresenter
{
	protected function checkSlug()
	{
		if($this->slug !== NULL) {
			$this->redirect('this', array('slug' => NULL));
		}
	}

	protected function checkPermissions()
	{
		return TRUE;
	}

	public function isAllowed($resource = NULL)
	{
		return $this->isGameAllowed('general', $resource);
	}
}
