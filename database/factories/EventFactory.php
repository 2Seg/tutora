<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'start_time' => $startTime = $this->getStartTime(),
            'end_time' => $startTime->addMinutes(50),
            'type' => EventType::Lesson,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            if ($event->type !== EventType::Blocked && $event->lesson()->doesntExist()) {
                Lesson::factory()->create(['event_id' => $event->id]);
            }
        });
    }

    public function blocked(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => EventType::Blocked,
            'start_time' => $startTime = $this->getStartTime(),
            'end_time' => $startTime->addMinutes($this->faker->randomElement([60, 180])),
        ]);
    }

    private function getStartTime(): CarbonImmutable
    {
        $randomDay = CarbonImmutable::instance(
            $this->faker->dateTimeBetween('monday this week', 'sunday next week')
        );

        return $randomDay->setTime(
            $this->faker->numberBetween(7, 18),
            $this->faker->randomElement([0, 30]),
        );
    }
}
