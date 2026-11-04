<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AvailabilitySeeder extends Seeder
{
    private const array AVAILABILITIES = [
        [1, '07:00', '12:00'], [1, '14:00', '18:00'],
        [2, '07:00', '12:00'], [2, '14:00', '18:00'],
        [3, '07:00', '13:00'],
        [4, '07:00', '12:00'], [4, '14:00', '18:00'],
        [5, '07:00', '13:00'],
        [6, '08:00', '12:00'],
    ];

    private User $user;

    public function run(): void
    {
        $this->user = User::query()->firstOrFail();

        foreach (self::AVAILABILITIES as $availability) {
            Availability::factory()->create([
                'user_id' => $this->user->id,
                'day_of_week' => $availability[0],
                'start_time' => $availability[1],
                'end_time' => $availability[2],
            ]);
        }
    }
}
