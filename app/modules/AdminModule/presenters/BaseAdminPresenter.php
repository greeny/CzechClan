<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\BasePresenter;

abstract class BaseAdminPresenter extends BasePresenter
{
	/** @var string @persistent */
	public $slug;

	public function startup()
	{
		parent::startup();
		if(!$this->user->isLoggedIn() || !$this->user->isAllowed('admin', 'access')) {
			$this->flashError('Nemáš právo k přístupu do administrace.');
			$this->redirect(':Public:Dashboard:default');
		}
		$this->checkSlug();
	}

	abstract protected function checkSlug();
}
