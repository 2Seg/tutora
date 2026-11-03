<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Teacher\EventController;
use App\Http\Controllers\Teacher\StudentController;
use Illuminate\Routing\Router;

/** @var Router $router */

$router->middleware('guest')->name('auth.')->group(function (Router $router) {
    $router->inertia('/login', 'auth/Login')->name('show');
    $router->post('/login', [AuthController::class, 'login'])->name('login');
});

$router->middleware('auth')->group(function (Router $router) {
    $router->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    $router->prefix('teacher')->name('teacher.')->group(function (Router $router) {
        $router->inertia('/', 'teacher/Dashboard')->name('dashboard');
        $router->get('/students', [StudentController::class, 'index'])->name('students');
        $router->get('/calendar', [EventController::class, 'index'])->name('calendar');
    });
});
