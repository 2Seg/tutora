<?php

declare(strict_types=1);

use Illuminate\Routing\Router;

return function (Router $router) {
    $router->inertia('/teacher', 'teacher/Dashboard');
    $router->inertia('/teacher/students', 'teacher/students/Index');
};
