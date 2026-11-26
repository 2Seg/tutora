<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LessonStatus;
use App\Models\Concerns\HasFullName;
use App\Models\Concerns\HasInitials;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

final class Student extends Model
{
    use HasFactory, HasFullName, HasInitials, HasTimestamps;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'timezone',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function nextLesson(): HasOneThrough
    {
        return $this->hasOneThrough(Event::class, Lesson::class, 'student_id', 'id', 'id', 'event_id')
            ->where('events.starts_at', '>', now())
            ->whereNot('lessons.status', LessonStatus::Cancelled)
            ->oldest('events.starts_at');
    }
}
