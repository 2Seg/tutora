<?php

declare(strict_types=1);

namespace App\Policies;

use App\Data\Event\CreateEventData;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use App\Repositories\Student\StudentRepository;

final readonly class EventPolicy
{
    public function __construct(private StudentRepository $studentRepository) {}

    public function create(User $user, CreateEventData $createEventData): bool
    {
        if ($createEventData->createLessonData === null) {
            return true;
        }

        return $this->studentRepository->belongsToUser($createEventData->createLessonData->studentId, $user);
    }

    public function update(User $user, Event $event): bool
    {
        if ($event->user_id !== $user->id) {
            return false;
        }

        return $event->type === EventType::TimeOff || $event->starts_at->isFuture();
    }
}
