<?php

declare(strict_types=1);

namespace App\Repositories\Availability;

use App\Data\AvailabilitiesData;
use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentAvailabilityRepository implements AvailabilityRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->availabilities()->get();
    }

    public function updateForUser(User $user, AvailabilitiesData $availabilitiesData): void
    {
        $user->getConnection()->transaction(function () use ($user, $availabilitiesData) {
            $user->availabilities()->delete();

            $user->availabilities()->createMany($availabilitiesData->toRows());
        });
    }
}
