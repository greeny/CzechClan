<?php

namespace CzechClan\Routing;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('<module (admin|user)>/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		$router[] = new Route('<slug>/<presenter>/<action>[/<id>]', array(
			'slug' => 'none',
			'module' => 'Public',
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		return $router;
	}
}
