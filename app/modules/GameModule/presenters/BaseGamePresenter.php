<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\BasePresenter;
use Tempeus\Games\Feeds\GtaFeedProvider;
use Tempeus\Games\Feeds\MinecraftFeedProvider;
use Tempeus\Games\Minecraft\MinecraftController;
use Tempeus\Model\Game;
use Tempeus\Model\GameRepository;
use Tempeus\Model\InformationRepository;

abstract class BaseGamePresenter extends BasePresenter
{
	/** @var string @persistent */
	public $slug;

	/** @var MinecraftFeedProvider @inject */
	public $minecraftFeedProvider;

	/** @var GtaFeedProvider @inject */
	public $gtaFeedProvider;

	/** @var GameRepository @inject */
	public $gameRepository;

	/** @var InformationRepository @inject */
	public $informationRepository;

	/** @var Game */
	protected $game;

	public function startup()
	{
		parent::startup();
		$this->template->feeds = array(
			'minecraft' => $this->minecraftFeedProvider,
			'gta' => $this->gtaFeedProvider
		);
		$this->template->currentGame = $this->game = $this->gameRepository->findBySlug($this->slug);
		$this->template->informations = $this->informationRepository->findAllByGame($this->game);
	}
}
