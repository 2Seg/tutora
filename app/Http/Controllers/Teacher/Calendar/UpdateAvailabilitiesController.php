<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Calendar;

use App\Http\Requests\UpdateAvailabilitiesRequest;
use App\Repositories\Availability\AvailabilityRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class UpdateAvailabilitiesController
{
    public function __construct(
        private Redirector $redirector,
        private AvailabilityRepository $availabilityRepository,
    ) {}

    public function __invoke(UpdateAvailabilitiesRequest $updateAvailabilitiesRequest): RedirectResponse
    {
        $this->availabilityRepository->updateForUser(
            $updateAvailabilitiesRequest->user(),
            $updateAvailabilitiesRequest->toAvailabilitiesData()
        );

        return $this->redirector->route('teacher.calendar.show');
    }
}
