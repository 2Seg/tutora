<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
final class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->withoutAfterCreating(),
            'student_id' => Student::factory(),
            'title' => fn(array $attributes) => Student::find($attributes['student_id'])->full_name,
            'status' => $this->faker->randomElement(EventStatus::cases()),
            'meeting_url' => $this->faker->url,
        ];
    }
}
