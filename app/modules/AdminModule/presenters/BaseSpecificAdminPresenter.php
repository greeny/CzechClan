<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\AdminModule;

use CzechClan\Model\Game;

abstract class BaseSpecificAdminPresenter extends BaseAdminPresenter
{
	/** @var Game */
	protected $game;

	public function startup()
	{
		parent::startup();
		if(!$this->checkGamePermissions()) {
			$this->flashError('Nemáš přístup k administraci této hry.');
			$this->redirect(':Admin:Dashboard:default');
		}
	}

	protected function checkSlug()
	{
		if(!$this->template->currentGame = $this->game = $this->gameRepository->findBySlug($this->slug)) {
			$this->redirect(':Admin:Dashboard:default', array('slug' => NULL));
		}
	}

	public function isAllowed($resource = NULL)
	{
		return $this->isGameAllowed($this->game->slug, $resource);
	}

	protected function checkGamePermissions()
	{
		return $this->isGameAllowed($this->game->slug);
	}
}
