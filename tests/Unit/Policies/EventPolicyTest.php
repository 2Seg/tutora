<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Data\Event\CreateEventData;
use App\Data\Event\CreateLessonData;
use App\Enums\EventType;
use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\User;
use App\Policies\EventPolicy;
use App\Repositories\Student\StudentRepository;
use Carbon\CarbonImmutable;
use Mockery;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class EventPolicyTest extends UnitTestCase
{
    private readonly StudentRepository|Mock $studentRepository;
    private readonly EventPolicy $eventPolicy;

    protected function setUp(): void
    {
        $this->studentRepository = Mockery::spy(StudentRepository::class);
        $this->eventPolicy = new EventPolicy($this->studentRepository);
    }

    public function testItAllowsCreateIfLessonDataIsNull(): void
    {
        // Arrange
        $user = new User();
        $createEventData = new CreateEventData(
            EventType::TimeOff,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
        );

        // Act & Assert
        $this->assertTrue($this->eventPolicy->create($user, $createEventData));
        $this->studentRepository->shouldNotHaveReceived('belongsToUser');
    }

    public function testItDeniesCreateIfStudentDoesNotBelongToUser(): void
    {
        // Arrange
        $user = new User();
        $createEventData = new CreateEventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new CreateLessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('belongsToUser')
            ->once()
            ->with(2, $user)
            ->andReturn(false);

        // Act & Assert
        $this->assertFalse($this->eventPolicy->create($user, $createEventData));
    }

    public function testItAllowsCreateIfStudentBelongsToUser(): void
    {
        // Arrange
        $user = new User();
        $createEventData = new CreateEventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new CreateLessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('belongsToUser')
            ->once()
            ->with(2, $user)
            ->andReturn(true);

        // Act & Assert
        $this->assertTrue($this->eventPolicy->create($user, $createEventData));
    }

    public function testItDeniesUpdateIfEventDoesNotBelongToUser(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $otherUser = new User();
        $otherUser->id = 2;
        $event = new Event();
        $event->user_id = $otherUser->id;

        // Act & Assert
        $this->assertFalse($this->eventPolicy->update($user, $event));
    }

    public function testItAllowsUpdateIfTimeOffBelongsToUser(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = $user->id;
        $event->type = EventType::TimeOff;

        // Act & Assert
        $this->assertTrue($this->eventPolicy->update($user, $event));
    }

    public function testItDeniesUpdateIfLessonStartsInThePast(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = $user->id;
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '2000-01-01T00:00:00+00:00';

        // Act & Assert
        $this->assertFalse($this->eventPolicy->update($user, $event));
    }

    public function testItAllowsUpdateIfLessonStartsInTheFuture(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = $user->id;
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '5000-01-01T00:00:00+00:00';

        // Act & Assert
        $this->assertTrue($this->eventPolicy->update($user, $event));
    }

    public function testItDeniesCancelIfEventIsTimeOff(): void
    {
        // Arrange
        $user = new User();
        $event = new Event();
        $event->type = EventType::TimeOff;

        // Act & Assert
        $this->assertFalse($this->eventPolicy->cancel($user, $event));
    }

    public function testItDeniesCancelIfLessonDoesNotBelongToUser(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = 2;
        $event->type = EventType::Lesson;

        // Act & Assert
        $this->assertFalse($this->eventPolicy->cancel($user, $event));
    }

    public function testItDeniesCancelIfLessonIsPast(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '2000-01-01T00:00:00+00:00';

        // Act & Assert
        $this->assertFalse($this->eventPolicy->cancel($user, $event));
    }

    public function testItDeniesCancelIfLessonStatusIsCancelled(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $lesson = new Lesson();
        $lesson->status = LessonStatus::Cancelled;
        $event = new Event();
        $event->user_id = 1;
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '3000-01-01T00:00:00+00:00';
        $event->setRelation('lesson', $lesson);

        // Act & Assert
        $this->assertFalse($this->eventPolicy->cancel($user, $event));
    }

    public function testItAllowsCancelIfLessonStatusIsRequested(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $lesson = new Lesson();
        $lesson->status = LessonStatus::Requested;
        $event = new Event();
        $event->user_id = 1;
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '3000-01-01T00:00:00+00:00';
        $event->setRelation('lesson', $lesson);

        // Act & Assert
        $this->assertTrue($this->eventPolicy->cancel($user, $event));
    }

    public function testItAllowsCancelIfLessonStatusIsPlanned(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $lesson = new Lesson();
        $lesson->status = LessonStatus::Planned;
        $event = new Event();
        $event->user_id = 1;
        $event->type = EventType::Lesson;
        $event->setDateFormat('Y-m-d H:i:s');
        $event->starts_at = '3000-01-01T00:00:00+00:00';
        $event->setRelation('lesson', $lesson);

        // Act & Assert
        $this->assertTrue($this->eventPolicy->cancel($user, $event));
    }

    public function testItDeniesDeleteIfEventIsLesson(): void
    {
        // Arrange
        $user = new User();
        $event = new Event();
        $event->type = EventType::Lesson;

        // Act & Assert
        $this->assertFalse($this->eventPolicy->delete($user, $event));
    }

    public function testItDeniesDeleteIfTimeOffDoesNotBelongToUser(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = 2;
        $event->type = EventType::TimeOff;

        // Act & Assert
        $this->assertFalse($this->eventPolicy->delete($user, $event));
    }

    public function testItAllowsDeleteIfTimeOffBelongsToUser(): void
    {
        // Arrange
        $user = new User();
        $user->id = 1;
        $event = new Event();
        $event->user_id = 1;
        $event->type = EventType::TimeOff;

        // Act & Assert
        $this->assertTrue($this->eventPolicy->delete($user, $event));
    }
}
