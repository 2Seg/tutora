<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Event;

use App\Data\EventData;
use App\Data\LessonData;
use App\Enums\EventType;
use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\Student;
use App\Models\User;
use App\Repositories\Event\EloquentEventRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Tests\Feature\FeatureTestCase;

final class EloquentEventRepositoryTest extends FeatureTestCase
{
    private readonly EloquentEventRepository $eventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = new EloquentEventRepository();
    }

    public function testItDoesNotRetrieveEventsFromAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->eventRepository->getAllFromUser($user);

        // Assert
        $this->assertCount(0, $actual);
        $this->assertFalse($actual->contains($event));
    }

    public function testItRetrievesEventsFromUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->eventRepository->getAllFromUser($user);

        // Assert
        $this->assertCount(1, $actual);
        $this->assertTrue($actual->contains($event));
    }

    public function testItStoresTimeOffEventWithoutALesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $eventData = new EventData(
            EventType::TimeOff,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00')
        );

        // Act
        $this->eventRepository->storeForUser($user, $eventData);

        // Assert
        $storedEvent = $user->events()->sole();

        $this->assertSame(EventType::TimeOff, $storedEvent->type);
        $this->assertTrue($storedEvent->starts_at->equalTo('2000-01-01 00:00:00'));
        $this->assertTrue($storedEvent->ends_at->equalTo('2000-01-02 00:00:00'));
        $this->assertDatabaseCount('lessons', 0);
    }

    public function testItDoesNotStoreALessonEventIfStoringItsLessonFails(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistingStudentId = 999999999;
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00'),
            new LessonData($nonExistingStudentId, LessonStatus::Planned, 'Orphan lesson'),
        );

        // Act & Assert
        $this->assertThrows(
            fn() => $this->eventRepository->storeForUser($user, $eventData),
            QueryException::class
        );

        // Assert
        $this->assertDatabaseCount('events', 0);
        $this->assertDatabaseCount('lessons', 0);
    }

    public function testItStoresALessonEventWithItsLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create();
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00'),
            new LessonData($student->id, LessonStatus::Planned, $student->full_name),
        );

        // Act
        $this->eventRepository->storeForUser($user, $eventData);

        // Assert
        $storedEvent = $user->events()->sole();

        $this->assertSame(EventType::Lesson, $storedEvent->type);
        $this->assertTrue($storedEvent->starts_at->equalTo('2000-01-01 00:00:00'));
        $this->assertTrue($storedEvent->ends_at->equalTo('2000-01-02 00:00:00'));
        $this->assertDatabaseHas('lessons', [
            'event_id' => $storedEvent->id,
            'student_id' => $student->id,
            'status' => LessonStatus::Planned,
            'title' => $student->full_name,
        ]);
    }
}
