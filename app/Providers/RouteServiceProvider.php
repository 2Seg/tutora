<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseRouteServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public function boot()
    {
        $this->routes(function (Router $router) {
            $register = require base_path('routes/web.php');
            $register($router);
        });
    }
}
