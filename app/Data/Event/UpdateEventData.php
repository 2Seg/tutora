<?php

declare(strict_types=1);

namespace App\Data\Event;

use Carbon\CarbonImmutable;

final readonly class UpdateEventData
{
    private function __construct(
        public ?CarbonImmutable $startsAt,
        public ?CarbonImmutable $endsAt,
        public ?string $lessonTitle,
    ) {}

    public static function forTimeOff(CarbonImmutable $startsAt, CarbonImmutable $endsAt): self
    {
        return new self(startsAt: $startsAt, endsAt: $endsAt, lessonTitle: null);
    }

    public static function forLesson(string $title): self
    {
        return new self(lessonTitle: $title, startsAt: null, endsAt: null);
    }
}
