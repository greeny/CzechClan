<?php

namespace Tempeus\Routing;

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
		$router[] = new Route('<module (admin)>/<slug>/<presenter>/<action>[/<id>]', array(
			'slug' => NULL,
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		$router[] = new Route('<module (user)>/<presenter>/<action>[/<id>]', array(
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		$router[] = new Route('<slug>/<presenter>/<action>[/<id>]', array(
			'module' => 'Game',
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		$router[] = new Route('[<id [0-9]{1,}>]', array(
			'module' => 'Public',
			'presenter' => 'Dashboard',
			'action' => 'default',
		));
		return $router;
	}
}
