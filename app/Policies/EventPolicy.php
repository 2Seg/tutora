<?php

declare(strict_types=1);

namespace App\Policies;

use App\Data\EventData;
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
}
