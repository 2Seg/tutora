<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar\Events;

use App\Models\Event;
use App\Repositories\Event\EventRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class CancelEventController
{
    public function __construct(
        private Gate $gate,
        private Redirector $redirector,
        private EventRepository $eventRepository,
    ) {}

    public function __invoke(Event $event): RedirectResponse
    {
        $this->gate->authorize('cancel', $event);

        $this->eventRepository->cancel($event);

        return $this->redirector->route('teacher.calendar.show');
    }
}
