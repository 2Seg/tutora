<?php

declare(strict_types=1);

namespace App\Repositories\Event;

use App\Data\Event\CreateEventData;
use App\Models\User;
use Illuminate\Support\Collection;

interface EventRepository
{
    public function getAllFromUser(User $user): Collection;

    public function storeForUser(User $user, CreateEventData $createEventData): void;
}
