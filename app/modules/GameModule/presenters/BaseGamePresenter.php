<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\BasePresenter;
use Tempeus\Model\Game;
use Tempeus\Model\GameRepository;
use Tempeus\Model\InformationRepository;

abstract class BaseGamePresenter extends BasePresenter
{
	/** @var string @persistent */
	public $slug;

	/** @var GameRepository @inject */
	public $gameRepository;

	/** @var InformationRepository @inject */
	public $informationRepository;

	/** @var Game */
	protected $game;

	public function startup()
	{
		parent::startup();
		$this->template->currentGame = $this->game = $this->gameRepository->findBySlug($this->slug);
		$this->template->informations = $this->informationRepository->findAllByGame($this->game);
	}
}
