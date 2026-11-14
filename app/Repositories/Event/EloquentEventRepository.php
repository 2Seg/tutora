<?php

declare(strict_types=1);

namespace App\Repositories\Event;

use App\Data\Event\CreateEventData;
use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentEventRepository implements EventRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->events()->get();
    }

    public function storeForUser(User $user, CreateEventData $createEventData): void
    {
        $user->getConnection()->transaction(function () use ($user, $createEventData) {
            $event = $user->events()->create([
                'type' => $createEventData->type,
                'starts_at' => $createEventData->startsAt,
                'ends_at' => $createEventData->endsAt,
            ]);

            if ($createEventData->createLessonData !== null) {
                $event->lesson()->create([
                    'student_id' => $createEventData->createLessonData->studentId,
                    'status' => $createEventData->createLessonData->status,
                    'title' => $createEventData->createLessonData->title,
                ]);
            }
        });
    }
}
