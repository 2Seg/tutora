<?php

declare(strict_types=1);

namespace App\Data;

final readonly class AvailabilityData
{
    public function __construct(
        public int $dayOfWeek,
        public string $startTime,
        public string $endTime,
    ) {}
}
