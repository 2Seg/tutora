<?php

declare(strict_types=1);

namespace App\Data\Event;

use App\Enums\EventType;
use Carbon\CarbonImmutable;

final readonly class CreateEventData
{
    public function __construct(
        public EventType $type,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public ?CreateLessonData $createLessonData = null,
    ) {}
}
