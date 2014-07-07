<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

abstract class BaseGeneralAdminPresenter extends BaseAdminPresenter
{
	protected function checkSlug()
	{
		if($this->slug !== 'general') {
			$this->redirect(':Admin:Dashboard:default', array('slug' => NULL));
		}
	}
}
