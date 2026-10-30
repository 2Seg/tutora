<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

final class EventSeeder extends Seeder
{
    // [day of the week (0 = Monday), from, to, student, status]
    private const array THIS_WEEK_LESSONS = [
        [0, '10:00', '11:00', 'Maya Chen', EventStatus::Planned],
        [1, '14:00', '15:00', 'Oliver James', EventStatus::Planned],
        [2, '10:00', '11:00', 'Sophie Martin', EventStatus::Planned],
        [3, '11:00', '12:00', 'Lucas Rossi', EventStatus::Cancelled],
        [4, '10:00', '11:00', 'Amira Patel', EventStatus::Planned],
        [5, '10:00', '11:00', 'Maya Chen', EventStatus::Cancelled],
        [6, '09:00', '10:30', 'Amira Patel', EventStatus::Planned],
    ];

    // [day of the week (0 = Monday), from, to]
    private const array THIS_WEEK_BLOCKED = [
        [2, '12:00', '14:00'],
        [4, '13:00', '17:00'],
        [6, '13:00', '17:00'],
    ];

    private const array NEXT_WEEK_LESSONS = [
        [0, '09:00', '10:00', 'Lucas Rossi', EventStatus::Planned],
        [0, '15:00', '16:00', 'Sophie Martin', EventStatus::Requested],
        [1, '10:00', '11:00', 'Amira Patel', EventStatus::Requested],
        [2, '14:00', '15:00', 'Oliver James', EventStatus::Planned],
        [3, '10:00', '11:00', 'Maya Chen', EventStatus::Cancelled],
        [4, '16:00', '17:00', 'Sophie Martin', EventStatus::Requested],
        [5, '11:00', '12:30', 'Lucas Rossi', EventStatus::Planned],
    ];

    private const array NEXT_WEEK_BLOCKED = [
        [3, '13:00', '17:00'],
        [5, '14:00', '18:00'],
    ];

    private User $user;

    /** @var array<string, Student> */
    private array $students = [];

    public function run(): void
    {
        $this->user = User::query()->firstOrFail();

        $thisMonday = CarbonImmutable::now()->startOfWeek();
        $nextMonday = $thisMonday->addWeek();

        // Blocked every night, starting with the Sunday night that covers this Monday morning
        foreach (range(-1, 13) as $day) {
            $this->createEvent(EventType::Blocked, $this->at($thisMonday, $day, '22:00'), $this->at($thisMonday, $day + 1, '06:00'));
        }

        $this->seedWeek($thisMonday, self::THIS_WEEK_LESSONS, self::THIS_WEEK_BLOCKED);
        $this->seedWeek($nextMonday, self::NEXT_WEEK_LESSONS, self::NEXT_WEEK_BLOCKED);
    }

    private function seedWeek(CarbonImmutable $monday, array $lessons, array $blocked): void
    {
        foreach ($blocked as [$day, $from, $to]) {
            $this->createEvent(EventType::Blocked, $this->at($monday, $day, $from), $this->at($monday, $day, $to));
        }

        foreach ($lessons as [$day, $from, $to, $name, $status]) {
            $event = $this->createEvent(EventType::Lesson, $this->at($monday, $day, $from), $this->at($monday, $day, $to));

            Lesson::factory()
                ->for($event)
                ->for($this->student($name))
                ->create(['status' => $status]);
        }
    }

    private function createEvent(EventType $type, CarbonImmutable $startTime, CarbonImmutable $endTime): Event
    {
        return Event::factory()
            ->withoutAfterCreating()
            ->create([
                'user_id' => $this->user->id,
                'type' => $type,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
    }

    private function student(string $name): Student
    {
        if (isset($this->students[$name])) {
            return $this->students[$name];
        }

        [$firstName, $lastName] = explode(' ', $name, 2);
        $names = ['first_name' => $firstName, 'last_name' => $lastName];

        return $this->students[$name] = Student::query()->firstOrCreate($names, Student::factory()->raw($names));
    }

    private function at(CarbonImmutable $monday, int $day, string $time): CarbonImmutable
    {
        [$hours, $minutes] = array_map(intval(...), explode(':', $time));

        // setTime(24, 0) rolls over to midnight of the next day
        return $monday->addDays($day)->setTime($hours, $minutes);
    }
}
