<?php

declare(strict_types=1);

namespace App\Repositories\Availability;

use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentAvailabilityRepository implements AvailabilityRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->availabilities()->get();
    }
}
