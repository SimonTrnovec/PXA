<?php

declare(strict_types=1);

namespace App\Router;

use App;
use Bluesome\Parameters\Provider;
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
     * @var IDomainRouteFactory
     */
    private $domainRouteFactory;

    public function __construct(Provider $p)
    {
        $this->parameters = $p;

    }

    /**
     * @return  \Nette\Application\IRouter
     */

	public function createRouter()
	{
		$router = new RouteList();

		$routeFlags = !$this->parameters->isDevelopment() ? Route::SECURED : 0;
		$backendAuthRoute = $this->domainRouteFactory->create('//<system>/<presenter backend-auth>/<action>', [
		    'action' => 'default',
        ], $routeFlags);
		$router[] = $backendAuthRoute;

		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
