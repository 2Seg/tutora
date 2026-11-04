<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Event;

use App\Actions\StoreEventAction;
use App\Http\Requests\CreateEventRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class CreateEventController
{
    public function __construct(
        private Redirector $redirector,
        private StoreEventAction $storeEventAction,
    ) {}

    public function __invoke(CreateEventRequest $createEventRequest): RedirectResponse
    {
        $this->storeEventAction->handle($createEventRequest->user(), $createEventRequest->toEventData());

        return $this->redirector->route('teacher.calendar.index');
    }
}
