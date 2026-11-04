<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\LessonStatus;

final readonly class LessonData
{
    public function __construct(
        public int $studentId,
        public LessonStatus $status,
        public string $title,
    ) {}
}
