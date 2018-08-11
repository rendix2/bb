<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Class RouterFactory
 *
 * @package App
 */
class RouterFactory
{
    use Nette\StaticClass;

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {
        $router = new RouteList;
        //$router[] = new Route('modal/<action>', 'Modal:default');        
        $router[] = new Route('<module>/<presenter>/<action>', 'Index:default');
        $router[] = new Route('<presenter>/<action>', 'Forum:Index:default');

        return $router;
    }
}
