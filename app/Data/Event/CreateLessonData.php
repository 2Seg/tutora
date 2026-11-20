<?php

declare(strict_types=1);

namespace App\Data\Event;

use App\Enums\LessonStatus;

final readonly class CreateLessonData
{
    public function __construct(
        public int $studentId,
        public LessonStatus $status,
        public string $title,
    ) {}
}
