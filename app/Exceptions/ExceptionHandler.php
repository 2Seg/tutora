<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;
use Throwable;

final class ExceptionHandler
{
    public function __invoke(Throwable $exception): Throwable
    {
        return match (true) {
            $exception instanceof InvalidCredentials
                => ValidationException::withMessages(['global' => $exception->getMessage(),]),
            default => $exception,
        };
    }
}
