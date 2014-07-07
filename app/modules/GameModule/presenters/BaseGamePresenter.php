<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\GameModule;

use CzechClan\BasePresenter;
use CzechClan\Model\Game;
use CzechClan\Model\GameRepository;

class BaseGamePresenter extends BasePresenter
{
	/** @var string @persistent */
	public $slug;

	/** @var GameRepository @inject */
	public $gameRepository;

	/** @var Game */
	protected $game;

	public function startup()
	{
		parent::startup();
		$this->template->game = $this->game = $this->gameRepository->findBySlug($this->slug);
	}
}
