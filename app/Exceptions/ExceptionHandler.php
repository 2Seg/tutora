<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ExceptionHandler
{
    public function __construct(private readonly Translator $translator) {}

    public function __invoke(Throwable $exception): Throwable
    {
        return match (true) {
            $exception instanceof InvalidCredentials => ValidationException::withMessages([
                'failed' => $this->translator->get($exception->translationKey),
            ]),
            default => $exception,
        };
    }
}
