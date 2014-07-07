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
}
