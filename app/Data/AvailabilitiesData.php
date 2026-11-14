<?php

declare(strict_types=1);

namespace App\Data;

final readonly class AvailabilitiesData
{
    private array $availabilities;

    public function __construct(AvailabilityData ...$availabilities)
    {
        $this->availabilities = $availabilities;
    }

    public function toRows(): array
    {
        return array_map(
            fn(AvailabilityData $availabilityData) => [
                'day_of_week' => $availabilityData->dayOfWeek,
                'start_time' => $availabilityData->startTime,
                'end_time' => $availabilityData->endTime,
            ],
            $this->availabilities,
        );
    }
}
