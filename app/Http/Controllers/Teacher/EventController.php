<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Repositories\Event\EventRepository;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class EventController
{
    public function __construct(
        private ResponseFactory $inertia,
        private EventRepository $eventRepository,
    ) {}

    public function index(Request $request): Response
    {
        return $this->inertia->render('teacher/Calendar', [
            'events' => $this->eventRepository
                ->getAllFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
        ]);
    }
}
