<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar;

use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Repositories\Event\EventRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class UpdateEventController
{
    public function __construct(
        private Gate $gate,
        private Redirector $redirector,
        private EventRepository $eventRepository,
    ) {}

    public function __invoke(UpdateEventRequest $updateEventRequest, Event $event): RedirectResponse
    {
        $this->gate->authorize('update', $event);

        $this->eventRepository->update($event, $updateEventRequest->toEventData());

        return $this->redirector->route('teacher.calendar.show');
    }
}
