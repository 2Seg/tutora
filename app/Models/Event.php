<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Event extends Model
{
    use HasTimestamps, HasFactory;

    protected $casts = [
        'type' => EventType::class,
        'status' => EventStatus::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function lesson(): HasOne
    {
        return $this->hasOne(Lesson::class);
    }
}
