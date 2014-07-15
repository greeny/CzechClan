<?php
/**
 * @author Tomáš Blatný
 */

namespace CzechClan\GameModule;

use CzechClan\BasePresenter;
use CzechClan\Model\Game;
use CzechClan\Model\GameRepository;

abstract class BaseGamePresenter extends BasePresenter
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
		$this->template->currentGame = $this->game = $this->gameRepository->findBySlug($this->slug);
	}
}
