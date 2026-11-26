<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar\Events;

use App\Models\Event;
use App\Repositories\Event\EventRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class PlanEventController
{
    public function __construct(
        private Gate $gate,
        private Redirector $redirector,
        private UrlGenerator $urlGenerator,
        private EventRepository $eventRepository,
    ) {}

    public function __invoke(Event $event): RedirectResponse
    {
        $this->gate->authorize('plan', $event);

        $this->eventRepository->plan($event);

        return $this->redirector->back(fallback: $this->urlGenerator->route('teacher.calendar.show'));
    }
}
