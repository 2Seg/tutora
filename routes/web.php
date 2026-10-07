<?php

declare(strict_types=1);

use App\Http\Controllers\TestController;
use Illuminate\Routing\Router;

return function (Router $router) {
    $router->view('/', 'app');

    $router->get('/test', [TestController::class, 'test']);
};
