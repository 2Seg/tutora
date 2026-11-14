<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar;

use App\Http\Requests\CreateEventRequest;
use App\Models\Event;
use App\Repositories\Event\EventRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class CreateEventController
{
    public function __construct(
        private Gate $gate,
        private Redirector $redirector,
        private EventRepository $eventRepository,
    ) {}

    public function __invoke(CreateEventRequest $createEventRequest): RedirectResponse
    {
        $this->gate->authorize('create', [Event::class, $createEventRequest->toEventData()]);

        $this->eventRepository->storeForUser($createEventRequest->user(), $createEventRequest->toEventData());

        return $this->redirector->route('teacher.calendar.show');
    }
}
