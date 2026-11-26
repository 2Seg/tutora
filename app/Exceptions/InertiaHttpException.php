<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class InertiaHttpException extends InertiaException
{
    protected function shouldBeLogged(): bool
    {
        return $this->statusCode === Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
