<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\BasePresenter;
use Tempeus\Model\Game;
use Tempeus\Model\GameRepository;

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
