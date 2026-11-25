<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Event;

use App\Data\Event\CreateEventData;
use App\Data\Event\CreateLessonData;
use App\Data\Event\UpdateEventData;
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
        $event = Event::factory()->create([
            'user_id' => $user2->id,
            'starts_at' => '2026-11-24 10:00:00',
            'ends_at' => '2026-11-24 11:00:00',
        ]);

        // Act
        $actual = $this->eventRepository->getFromUserBetween(
            $user,
            CarbonImmutable::parse('2026-11-23 00:00:00'),
            CarbonImmutable::parse('2026-11-30 00:00:00'),
        );

        // Assert
        $this->assertCount(0, $actual);
        $this->assertFalse($actual->contains($event));
    }

    public function testItRetrievesEventsFromUserOverlappingTheRange(): void
    {
        // Arrange
        $user = User::factory()->create();
        $inside = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-24 10:00:00',
            'ends_at' => '2026-11-24 11:00:00',
        ]);
        $startingBefore = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-22 22:00:00',
            'ends_at' => '2026-11-23 02:00:00',
        ]);
        $endingAfter = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-29 22:00:00',
            'ends_at' => '2026-11-30 02:00:00',
        ]);

        // Act
        $actual = $this->eventRepository->getFromUserBetween(
            $user,
            CarbonImmutable::parse('2026-11-23 00:00:00'),
            CarbonImmutable::parse('2026-11-30 00:00:00'),
        );

        // Assert
        $this->assertEqualsCanonicalizing(
            [$inside->id, $startingBefore->id, $endingAfter->id],
            $actual->pluck('id')->all(),
        );
    }

    public function testItDoesNotRetrieveEventsTouchingTheRangeBounds(): void
    {
        // Arrange
        $user = User::factory()->create();
        Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-22 22:00:00',
            'ends_at' => '2026-11-23 00:00:00',
        ]);
        Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-30 00:00:00',
            'ends_at' => '2026-11-30 02:00:00',
        ]);

        // Act
        $actual = $this->eventRepository->getFromUserBetween(
            $user,
            CarbonImmutable::parse('2026-11-23 00:00:00'),
            CarbonImmutable::parse('2026-11-30 00:00:00'),
        );

        // Assert
        $this->assertCount(0, $actual);
    }

    public function testItComparesRangeInUtc(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-22 12:00:00',
            'ends_at' => '2026-11-22 13:00:00',
        ]);

        // Act
        $actual = $this->eventRepository->getFromUserBetween(
            $user,
            CarbonImmutable::parse('2026-11-23 00:00:00', 'Pacific/Auckland'),
            CarbonImmutable::parse('2026-11-30 00:00:00', 'Pacific/Auckland'),
        );

        // Assert
        $this->assertTrue($actual->contains($event));
    }

    public function testItStoresTimeOffEventWithoutALesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $createEventData = new CreateEventData(
            EventType::TimeOff,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00')
        );

        // Act
        $this->eventRepository->storeForUser($user, $createEventData);

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
        $createEventData = new CreateEventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00'),
            new CreateLessonData($nonExistingStudentId, LessonStatus::Planned, 'Orphan lesson'),
        );

        // Act & Assert
        $this->assertThrows(
            fn() => $this->eventRepository->storeForUser($user, $createEventData),
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
        $createEventData = new CreateEventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00'),
            new CreateLessonData($student->id, LessonStatus::Planned, $student->full_name),
        );

        // Act
        $this->eventRepository->storeForUser($user, $createEventData);

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

    public function testItUpdatesTimeOffEventWithoutTouchingLessons(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create(['user_id' => $user->id]);
        $updateEventData = UpdateEventData::forTimeOff(
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00')
        );

        // Act
        $this->eventRepository->update($event, $updateEventData);

        // Assert
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'type' => EventType::TimeOff,
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-02 00:00:00',
        ]);
        $this->assertDatabaseCount('lessons', 0);
    }

    public function testItUpdatesALessonEventWithItsLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['title' => 'Event Title'])
            ->create([
                'user_id' => $user->id,
                'type' => EventType::Lesson,
                'starts_at' => '2000-01-01 00:00:00',
                'ends_at' => '2000-01-02 00:00:00',
            ]);
        $updateEventData = UpdateEventData::forLesson('Modified Event Title');

        // Act
        $this->eventRepository->update($event, $updateEventData);

        // Assert
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'type' => EventType::Lesson,
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-02 00:00:00',
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'title' => 'Event Title',
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'title' => 'Modified Event Title',
        ]);
    }

    public function testItPlansAnEvent(): void
    {
        // Arrange
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Requested])->create();

        // Act
        $this->eventRepository->plan($event);

        // Assert
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Requested,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
    }

    public function testItCancelsAnEvent(): void
    {
        // Arrange
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Planned])->create();

        // Act
        $this->eventRepository->cancel($event);

        // Assert
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Cancelled,
        ]);
    }

    public function testItDeletesAnEvent(): void
    {
        // Arrange
        $event = Event::factory()->create();

        // Act
        $this->eventRepository->delete($event);

        // Assert
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
