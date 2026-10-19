<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Student\EloquentStudentRepository;
use App\Repositories\Student\StudentRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        StudentRepository::class => EloquentStudentRepository::class,
    ];
}
