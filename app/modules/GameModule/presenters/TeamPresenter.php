<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\GameModule;

use Tempeus\Model\TeamRepository;

class TeamPresenter extends BaseGamePresenter
{
	/** @var TeamRepository @inject */
	public $teamRepository;

	public function renderDefault()
	{
		$this->template->teams = $this->teamRepository->findByGame($this->game);
	}

}
