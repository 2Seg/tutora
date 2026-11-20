<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

final class EventSeeder extends Seeder
{
    private const array THIS_WEEK_LESSONS = [
        [0, '10:00', '10:50', 'Maya Chen', LessonStatus::Planned],
        [1, '14:00', '14:50', 'Oliver James', LessonStatus::Planned],
        [2, '10:00', '10:50', 'Sophie Martin', LessonStatus::Planned],
        [3, '11:30', '12:20', 'Lucas Rossi', LessonStatus::Cancelled],
        [4, '10:00', '10:50', 'Amira Patel', LessonStatus::Planned],
    ];

    private const array THIS_WEEK_TIME_OFF = [
        [2, '12:00', '14:00'],
        [4, '13:00', '17:00'],
    ];

    private const array NEXT_WEEK_LESSONS = [
        [0, '09:00', '09:50', 'Lucas Rossi', LessonStatus::Planned],
        [0, '15:00', '15:50', 'Sophie Martin', LessonStatus::Requested],
        [1, '10:00', '10:50', 'Amira Patel', LessonStatus::Requested],
        [2, '14:30', '15:20', 'Oliver James', LessonStatus::Planned],
        [3, '10:15', '11:05', 'Maya Chen', LessonStatus::Cancelled],
        [4, '16:00', '16:50', 'Sophie Martin', LessonStatus::Requested],
    ];

    private const array NEXT_WEEK_TIME_OFF = [
        [3, '13:00', '17:00'],
    ];

    private readonly User $user;

    /** @var array<string, Student> */
    private array $students = [];

    public function run(): void
    {
        $this->user = User::query()->firstOrFail();

        $thisMonday = CarbonImmutable::now()->startOfWeek();
        $nextMonday = $thisMonday->addWeek();

        $this->seedWeek($thisMonday, self::THIS_WEEK_LESSONS, self::THIS_WEEK_TIME_OFF);
        $this->seedWeek($nextMonday, self::NEXT_WEEK_LESSONS, self::NEXT_WEEK_TIME_OFF);
    }

    private function seedWeek(CarbonImmutable $monday, array $lessons, array $timeOff): void
    {
        foreach ($timeOff as [$day, $from, $to]) {
            $this->createEvent(EventType::TimeOff, $this->at($monday, $day, $from), $this->at($monday, $day, $to));
        }

        foreach ($lessons as [$day, $from, $to, $name, $status]) {
            $event = $this->createEvent(EventType::Lesson, $this->at($monday, $day, $from), $this->at($monday, $day, $to));

            Lesson::factory()
                ->for($event)
                ->for($this->student($name))
                ->create(['status' => $status]);
        }
    }

    private function createEvent(EventType $type, CarbonImmutable $startsAt, CarbonImmutable $endsAt): Event
    {
        return Event::factory()
            ->withoutAfterCreating()
            ->create([
                'user_id' => $this->user->id,
                'type' => $type,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
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

        return $monday->addDays($day)->setTime($hours, $minutes);
    }
}
