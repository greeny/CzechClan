<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\Games\Feeds;

interface IFeedProvider
{
	function getPlayerCount();
	function getMaxPlayers();
	function getPlayers();
	function getName();
	function getInfo();
	function getStatus();
} 
