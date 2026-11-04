<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\StoreEventAction;
use App\Data\EventData;
use App\Data\LessonData;
use App\Enums\EventType;
use App\Enums\LessonStatus;
use App\Models\User;
use App\Repositories\Event\EventRepository;
use App\Repositories\Student\StudentRepository;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class StoreEventActionTest extends UnitTestCase
{
    private readonly StudentRepository|Mock $studentRepository;
    private readonly EventRepository|Mock $eventRepository;
    private readonly StoreEventAction $storeEventAction;

    protected function setUp(): void
    {
        $this->studentRepository = Mockery::mock(StudentRepository::class);
        $this->eventRepository = Mockery::spy(EventRepository::class);
        $this->storeEventAction = new StoreEventAction($this->studentRepository, $this->eventRepository);
    }

    public function testItThrowsWhenTheStudentBelongsToAnotherUser(): void
    {
        // Assert
        $this->expectException(AuthorizationException::class);

        // Arrange
        $user = new User();
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new LessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('getAllFromUser')
            ->once()
            ->with($user)
            ->andReturn(new Collection([['id' => 3]]));
        $this->eventRepository->shouldNotReceive('storeForUser');

        // Act
        $this->storeEventAction->handle($user, $eventData);
    }

    public function testItStoresTheLessonWhenTheStudentBelongsToTheUser(): void
    {
        // Arrange
        $user = new User();
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new LessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('getAllFromUser')
            ->once()
            ->with($user)
            ->andReturn(new Collection([['id' => 2]]));

        // Act
        $this->storeEventAction->handle($user, $eventData);

        // Assert
        $this->eventRepository->shouldHaveReceived('storeForUser')
            ->once()
            ->with($user, $eventData);
    }

    public function testItStoresTimeOffWithoutCheckingTheStudent(): void
    {
        // Arrange
        $user = new User();
        $eventData = new EventData(
            EventType::TimeOff,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
        );
        $this->studentRepository->shouldNotReceive('getAllFromUser');

        // Act
        $this->storeEventAction->handle($user, $eventData);

        // Assert
        $this->eventRepository->shouldHaveReceived('storeForUser')
            ->once()
            ->with($user, $eventData);
    }
}
