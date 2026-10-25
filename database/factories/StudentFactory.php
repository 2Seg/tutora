<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
final class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::first()->id,
            'email' => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'avatar_img' => $this->faker->imageUrl(),
            'timezone' => $this->faker->timezone(),
        ];
    }
}
