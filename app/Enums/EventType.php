<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case Lesson = 'lesson';
    case Blocked = 'blocked';
}
