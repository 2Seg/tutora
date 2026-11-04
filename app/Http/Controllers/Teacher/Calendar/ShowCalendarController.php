<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar;

use App\Http\Resources\StudentSummaryResource;
use App\Repositories\Event\EventRepository;
use App\Repositories\Student\StudentRepository;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class ShowCalendarController
{
    public function __construct(
        private ResponseFactory $inertia,
        private EventRepository $eventRepository,
        private StudentRepository $studentRepository,
    ) {}

    public function __invoke(Request $request): Response
    {
        return $this->inertia->render('teacher/Calendar', [
            'events' => $this->eventRepository
                ->getAllFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
            'students' => $this->inertia->defer(
                fn() => $this->studentRepository->getAllFromUser($request->user())
                    ->toResourceCollection(StudentSummaryResource::class)
                    ->resolve(),
            ),
        ]);
    }
}
