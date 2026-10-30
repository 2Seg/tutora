<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Event extends Model
{
    use HasTimestamps, HasFactory;

    protected $with = ['lesson'];

    protected $casts = [
        'type' => EventType::class,
        'status' => LessonStatus::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function lesson(): HasOne
    {
        return $this->hasOne(Lesson::class);
    }
}
