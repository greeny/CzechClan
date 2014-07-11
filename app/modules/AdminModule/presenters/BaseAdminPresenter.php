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
		if(!$this->user->isLoggedIn() || !$this->hasUserAdminAccess()) {
			$this->flashError('Nemáš právo k přístupu do administrace.');
			$this->redirect(':Public:Dashboard:default');
		}
		$this->checkSlug();
		if(!$this->checkPermissions()) {
			$this->flashError('Nemáš právo k přístupu do této části administrace.');
			$this->redirect(':Admin:Dashboard:default');
		}
	}

	public function isGameAllowed($game, $resource = NULL)
	{
		return $this->authorizator->isAdminAllowed($this->user, $game, $resource);
	}

	abstract public function isAllowed($resource = NULL);

	abstract protected function checkSlug();

	abstract protected function checkPermissions();
}
