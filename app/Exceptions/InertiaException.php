<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Inertia\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class InertiaException extends Exception implements ShouldRenderAndReport
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        public readonly int $statusCode,
        Throwable $original,
    ) {
        parent::__construct($original->getMessage(), 0, $original);
    }

    public function render(Request $request): Response
    {
        return $this->inertia->render('Error', ['statusCode' => $this->statusCode])
            ->toResponse($request)
            ->setStatusCode($this->statusCode);
    }

    /**
     * Returning true skips logging; false falls back to Laravel's default logging.
     * Wrapping bypasses Laravel's "don't report" list, so this prevents logging
     * validation, authorization, etc. exceptions: only 500s are logged.
     */
    public function report(): bool
    {
        return !$this->shouldBeLogged();
    }

    abstract protected function shouldBeLogged(): bool;
}
