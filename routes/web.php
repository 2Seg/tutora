<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Teacher\Event\CreateEventController;
use App\Http\Controllers\Teacher\Event\ListEventsController;
use App\Http\Controllers\Teacher\Student\ListStudentsController;
use Illuminate\Routing\Router;

/** @var Router $router */

$router->middleware('guest')->name('auth.')->group(function (Router $router) {
    $router->inertia('/login', 'auth/Login')->name('show');
    $router->post('/login', LoginController::class)->name('login');
});

$router->middleware('auth')->group(function (Router $router) {
    $router->post('/logout', LogoutController::class)->name('auth.logout');

    $router->prefix('teacher')->name('teacher.')->group(function (Router $router) {
        $router->inertia('/', 'teacher/Dashboard')->name('dashboard');
        $router->get('/students', ListStudentsController::class)->name('index');

        $router->prefix('calendar')->name('calendar.')->group(function (Router $router) {
            $router->get('/', ListEventsController::class)->name('index');
            $router->post('/', CreateEventController::class)->name('store');
        });
    });
});
