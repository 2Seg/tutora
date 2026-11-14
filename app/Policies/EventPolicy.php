<?php

declare(strict_types=1);

namespace App\Policies;

use App\Data\EventData;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Student\StudentRepository;

final readonly class EventPolicy
{
    public function __construct(private StudentRepository $studentRepository) {}

    public function create(User $user, EventData $eventData): bool
    {
        if ($eventData->lessonData === null) {
            return true;
        }

        return $this->studentRepository->belongsToUser($eventData->lessonData->studentId, $user);
    }

    public function update(User $user, Event $event): bool
    {
        if ($event->user_id !== $user->id) {
            return false;
        }

        return $event->type === EventType::TimeOff || $event->starts_at->isFuture();
    }
}
