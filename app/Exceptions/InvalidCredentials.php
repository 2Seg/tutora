<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InvalidCredentials extends RuntimeException
{
    public function __construct(public readonly string $translationKey = 'auth.failed')
    {
        parent::__construct($translationKey);
    }
}
