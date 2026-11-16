<?php

declare(strict_types=1);

namespace App\Repositories\Event;

use App\Data\Event\CreateEventData;
use App\Data\Event\UpdateEventData;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;

interface EventRepository
{
    public function getAllFromUser(User $user): Collection;

    public function storeForUser(User $user, CreateEventData $createEventData): void;

    public function update(Event $event, UpdateEventData $updateEventData): void;

    public function cancel(Event $event): void;

    public function delete(Event $event): void;
}
