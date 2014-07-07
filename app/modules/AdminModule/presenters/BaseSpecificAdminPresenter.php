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

	protected function checkSlug()
	{
		if(!$this->template->game = $this->game = $this->gameRepository->findBySlug($this->slug)) {
			$this->redirect(':Admin:Dashboard:default', array('slug' => NULL));
		}
	}
}
