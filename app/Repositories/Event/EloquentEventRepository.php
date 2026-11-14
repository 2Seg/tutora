<?php

declare(strict_types=1);

namespace App\Repositories\Event;

use App\Data\EventData;
use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentEventRepository implements EventRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->events()->get();
    }

    public function storeForUser(User $user, EventData $eventData): void
    {
        $user->getConnection()->transaction(function () use ($user, $eventData) {
            $event = $user->events()->create([
                'type' => $eventData->type,
                'starts_at' => $eventData->startsAt,
                'ends_at' => $eventData->endsAt,
            ]);

            if ($eventData->lessonData !== null) {
                $event->lesson()->create([
                    'student_id' => $eventData->lessonData->studentId,
                    'status' => $eventData->lessonData->status,
                    'title' => $eventData->lessonData->title,
                ]);
            }
        });
    }
}
