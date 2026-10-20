<?php

declare(strict_types=1);

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Teacher\StudentController;
use Illuminate\Routing\Router;

return function (Router $router) {
    $router->middleware('web')->group(function ($router) {
        $router->middleware('guest')->group(function ($router) {
            $router->inertia('/login', 'auth/Login')->name('login');
            $router->post('/login', [LoginController::class, 'login'])->name('login.submit');
        });

        $router->middleware('auth')->group(function ($router) {
            $router->inertia('/teacher', 'teacher/Dashboard')->name('teacher.dashboard');
            $router->get('/teacher/students', [StudentController::class, 'index'])->name('teacher.students');
            $router->inertia('/teacher/calendar', 'teacher/Calendar')->name('teacher.calendar');
            $router->inertia('/teacher/messages', 'teacher/Messages')->name('teacher.messages');
            $router->inertia('/teacher/earnings', 'teacher/Earnings')->name('teacher.earnings');
        });
    });
};
