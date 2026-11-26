<?php

declare(strict_types=1);

namespace App\Exceptions;

use Inertia\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class InertiaNotFoundException extends InertiaException
{
    public function __construct(ResponseFactory $inertia, Throwable $original)
    {
        parent::__construct($inertia, Response::HTTP_NOT_FOUND, $original);
    }

    protected function shouldBeLogged(): bool
    {
        return false;
    }
}
