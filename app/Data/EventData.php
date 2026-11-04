<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\EventType;
use Carbon\CarbonImmutable;

final readonly class EventData
{
    public function __construct(
        public EventType $type,
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public ?LessonData $lessonData = null,
    ) {}
}
