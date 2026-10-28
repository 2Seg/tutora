<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class EventController
{
    public function __construct(private ResponseFactory $inertia) {}

    public function index(Request $request): Response
    {
        return $this->inertia->render('teacher/Calendar');
    }
}
