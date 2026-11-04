<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\EventData;
use App\Models\User;
use App\Repositories\Event\EventRepository;
use App\Repositories\Student\StudentRepository;
use Illuminate\Auth\Access\AuthorizationException;

final readonly class StoreEventAction
{
    public function __construct(
        private StudentRepository $studentRepository,
        private EventRepository $eventRepository,
    ) {}

    public function handle(User $user, EventData $eventData): void
    {
        if ($eventData->lessonData !== null && !$this->isStudentOfUser($user, $eventData->lessonData->studentId)) {
            throw new AuthorizationException();
        }

        $this->eventRepository->storeForUser($user, $eventData);
    }

    private function isStudentOfUser(User $user, int $studentId): bool
    {
        return $this->studentRepository->getAllFromUser($user)->contains('id', $studentId);
    }
}
