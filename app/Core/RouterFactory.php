<?php declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\StaticClass;

final class RouterFactory
{

    use StaticClass;

    public static function createRouter() : RouteList
    {
        $router = new RouteList;

        // thumbnails of contributte/imagist outside www/ (config/ext/imagist.neon), the address it generates for n:img
        $router->addRoute('cache/<scope avatars|ranks>/_<filter avatar128|avatar48|rank>/<name [a-zA-Z0-9]{32}\.webp>', 'Web:Image:default');

        // the default action addressed by uuid without "/default/" (/forum.topic.view/<uuid>); first, so links are generated short
        $router->addRoute('<presenter>/<uuid>', [
                'action' => 'default',
                'uuid' => [
                    Route::Pattern => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
                ],
            ]
        );

        $router->addRoute('<presenter>/<action>/<uuid>', [
                'presenter' => 'Web:Home',
                'action' => 'edit',
                'uuid' => [
                    Route::Pattern => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
                ],
            ]
        );

        $router->addRoute('<presenter>/<action>/<id>', [
                'presenter' => 'Web:Home',
                'action' => 'edit',
                'id' => [
                    Route::Pattern => '\d+',
                ],
            ]
        );

        $router->addRoute('<presenter>/<action>', 'Web:Home:default');

        return $router;
    }

}