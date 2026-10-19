<?php

declare(strict_types=1);

use App\Http\Controllers\Teacher\StudentController;
use Illuminate\Routing\Router;

return function (Router $router) {
    $router->inertia('/teacher', 'teacher/Dashboard');
    $router->get('/teacher/students', [StudentController::class, 'index']);
    $router->inertia('/teacher/calendar', 'teacher/Calendar');
    $router->inertia('/teacher/messages', 'teacher/Messages');
    $router->inertia('/teacher/earnings', 'teacher/Earnings');
};
