<?php

declare(strict_types=1);

namespace App\Router;

use App;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @var Provider
     */
    private $parameters;

    /**
     * @return  \Nette\Application\IRouter
     */

	public static function createRouter()
	{
        $router = new RouteList;

        $router->addRoute('<presenter backend-auth>/<action>',[
            'presenter' => 'BackendAuth',
            'action'    => 'login',
        ]);

        $router->addRoute('<presenter>/<action>', [
            'presenter' => 'Homepage',
            'action'    => 'default',
        ]);

        return $router;
	}
}
