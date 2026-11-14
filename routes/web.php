<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Teacher\Calendar\CreateEventController;
use App\Http\Controllers\Teacher\Calendar\ShowCalendarController;
use App\Http\Controllers\Teacher\Calendar\UpdateAvailabilitiesController;
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
        $router->get('/students', ListStudentsController::class)->name('students.index');

        $router->prefix('calendar')->name('calendar.')->group(function (Router $router) {
            $router->get('/', ShowCalendarController::class)->name('show');
            $router->post('/', CreateEventController::class)->name('store');

            $router->put('/availabilities', UpdateAvailabilitiesController::class)->name('availabilities.update');
        });
    });
});
