<?php

declare(strict_types=1);

namespace App\Repositories\Availability;

use App\Data\AvailabilitiesData;
use App\Models\User;
use Illuminate\Support\Collection;

interface AvailabilityRepository
{
    public function getAllFromUser(User $user): Collection;

    public function updateForUser(User $user, AvailabilitiesData $availabilitiesData): void;
}
