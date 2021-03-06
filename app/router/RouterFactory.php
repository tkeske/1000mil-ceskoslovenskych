<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('sitemap.xml', 'Homepage:sitemap');
		$router[] = new Route('rss.xml', 'Homepage:rss');
		$router[] = new Route('<presenter>/<action>[/<id>]/', 'Result:default');
		return $router;
	}
}
