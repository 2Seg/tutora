<?php

declare(strict_types=1);

namespace App\Enums;

enum LessonStatus: string
{
    case Requested = 'requested';
    case Cancelled = 'cancelled';
    case Planned = 'planned';
}
