<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar;

use App\Http\Requests\ShowCalendarRequest;
use App\Http\Resources\StudentSummaryResource;
use App\Repositories\Availability\AvailabilityRepository;
use App\Repositories\Event\EventRepository;
use App\Repositories\Student\StudentRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class ShowCalendarController
{
    public function __construct(
        private ResponseFactory $inertia,
        private EventRepository $eventRepository,
        private StudentRepository $studentRepository,
        private AvailabilityRepository $availabilityRepository,
    ) {}

    public function __invoke(ShowCalendarRequest $request): Response
    {
        $user = $request->user();
        $weekStart = CarbonImmutable::parse($request->validated('week') ?? 'now', $user->timezone)
            ->startOfWeek(CarbonInterface::MONDAY);

        return $this->inertia->render('teacher/Calendar', [
            'events' => $this->eventRepository
                ->getFromUserBetween($user, $weekStart, $weekStart->addWeek())
                ->toResourceCollection()
                ->resolve(),
            'weekStart' => $weekStart->toDateString(),
            'availabilities' => $this->availabilityRepository
                ->getAllFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
            'students' => $this->inertia->defer(
                fn() => $this->studentRepository->getAllFromUser($request->user())
                    ->toResourceCollection(StudentSummaryResource::class)
                    ->resolve(),
            ),
            'selectedEventId' => $request->integer('event') ?: null,
        ]);
    }
}
