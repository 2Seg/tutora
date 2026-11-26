<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
final class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 7),
            'start_time' => '08:00',
            'end_time' => '17:00',
        ];
    }
}
