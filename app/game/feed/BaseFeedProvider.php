<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Feeds;

use Nette\Object;
use Nette\Utils\ArrayHash;

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
		return isset($this->getFeed()->players_list) ? $this->getFeed()->players_list : array();
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
		if($this->feed) {
			return $this->feed;
		}
		$feed = @file_get_contents($this->feedPath); // @ - may not be available
		if(!$feed) {
			return $this->feed = ArrayHash::from(array(
				'status' => 'offline',
				'players_list' => array(),
				'players' => 0,
				'slots' => 0,
				'hostname' => 'Unknown',
				'version' => 'Unknown',
				'map' => 'Unknown',
			));
		}
		return $this->feed = json_decode($feed);
	}
}
