<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Requested = 'requested';
    case Cancelled = 'cancelled';
    case Planned = 'planned';
}
