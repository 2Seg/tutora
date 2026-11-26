<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Inertia\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final readonly class ExceptionHandler
{
    public function __construct(
        private Translator $translator,
        private Repository $config,
        private ResponseFactory $inertia,
    ) {}

    /** Order in match is essential */
    public function __invoke(Throwable $exception): Throwable
    {
        return match (true) {
            $exception instanceof InvalidCredentials => ValidationException::withMessages([
                'failed' => $this->translator->get($exception->translationKey),
            ]),
            $this->config->get('app.debug') => $exception,
            $this->isNotFound($exception) => new InertiaNotFoundException($this->inertia, $exception),
            $this->isHandledByLaravel($exception) => $exception,
            $exception instanceof HttpExceptionInterface => new InertiaHttpException(
                $this->inertia,
                $exception->getStatusCode(),
                $exception
            ),
            $exception instanceof AuthorizationException => new InertiaHttpException(
                $this->inertia,
                Response::HTTP_FORBIDDEN,
                $exception
            ),
            default => new InertiaHttpException($this->inertia, Response::HTTP_INTERNAL_SERVER_ERROR, $exception),
        };
    }

    private function isNotFound(Throwable $exception): bool
    {
        return $exception instanceof NotFoundHttpException
            || $exception instanceof ModelNotFoundException;
    }

    private function isHandledByLaravel(Throwable $exception): bool
    {
        return $exception instanceof ValidationException
            || $exception instanceof AuthenticationException
            || $exception instanceof HttpResponseException
            || $exception instanceof TokenMismatchException;
    }
}
