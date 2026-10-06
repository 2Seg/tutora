<?php

declare(strict_types=1);

use Illuminate\Routing\Router;

return function (Router $router) {
    $router->view('/', 'welcome');
};
