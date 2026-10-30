<?php

declare(strict_types=1);

namespace App\Repositories\Event;

use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentEventRepository implements EventRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->events()->get();
    }
}
