<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ShouldRenderAndReport extends Throwable
{
    public function render(Request $request): Response;

    public function report(): bool;
}
