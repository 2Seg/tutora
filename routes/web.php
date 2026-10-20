<?php

declare(strict_types=1);

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Teacher\StudentController;
use Illuminate\Routing\Router;

return function (Router $router) {
    $router->middleware('web')->group(function ($router) {
        $router->inertia('/login', 'auth/Login');
        $router->post('/login', [LoginController::class, 'login']);

        $router->inertia('/teacher', 'teacher/Dashboard');
        $router->get('/teacher/students', [StudentController::class, 'index']);
        $router->inertia('/teacher/calendar', 'teacher/Calendar');
        $router->inertia('/teacher/messages', 'teacher/Messages');
        $router->inertia('/teacher/earnings', 'teacher/Earnings');
    });
};
