<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Event\EloquentEventRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\Student\EloquentStudentRepository;
use App\Repositories\Student\StudentRepository;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        StudentRepository::class => EloquentStudentRepository::class,
        EventRepository::class => EloquentEventRepository::class,
    ];

    public function register()
    {
        $this->app->bind(
            StatefulGuard::class,
            fn(Application $app): StatefulGuard => $app->make(AuthManager::class)->guard('web'),
        );
    }
}
