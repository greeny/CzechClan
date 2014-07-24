<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Feeds;

use Nette\Object;

abstract class BaseFeedProvider extends Object implements IFeedProvider
{
	private $feedPath;

	private $feed = NULL;

	public function __construct($feedPath)
	{
		$this->feedPath = $feedPath;
	}

	public function isOnline()
	{
		return trim(strtolower($this->getStatus())) === 'online';
	}

	public function getStatus()
	{
		return $this->getFeed()->status;
	}

	public function getPlayers()
	{
		return $this->getFeed()->players_list;
	}

	public function getPlayerCount()
	{
		return (int) $this->getFeed()->players;
	}

	public function getMaxPlayers()
	{
		return (int) $this->getFeed()->slots;
	}

	public function getName()
	{
		return $this->getFeed()->hostname;
	}

	public function getFeed()
	{
		return $this->feed ? $this->feed : $this->feed = json_decode(file_get_contents($this->feedPath));
	}
}
