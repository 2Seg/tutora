<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Data\EventData;
use App\Data\LessonData;
use App\Enums\EventType;
use App\Enums\LessonStatus;
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
        $eventData = new EventData(
            EventType::TimeOff,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
        );

        // Act
        $actual = $this->eventPolicy->create($user, $eventData);

        // Assert
        $this->assertTrue($actual);
        $this->studentRepository->shouldNotHaveReceived('belongsToUser');
    }

    public function testItDeniesCreateIfStudentDoesNotBelongToUser(): void
    {
        // Arrange
        $user = new User();
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new LessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('belongsToUser')
            ->once()
            ->with(2, $user)
            ->andReturn(false);

        // Act
        $actual = $this->eventPolicy->create($user, $eventData);

        // Assert
        $this->assertFalse($actual);
    }

    public function testItAllowsCreateIfStudentBelongsToUser(): void
    {
        // Arrange
        $user = new User();
        $eventData = new EventData(
            EventType::Lesson,
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2010-01-01 00:00:00'),
            new LessonData(2, LessonStatus::Planned, 'Event Title')
        );
        $this->studentRepository->shouldReceive('belongsToUser')
            ->once()
            ->with(2, $user)
            ->andReturn(true);

        // Act
        $actual = $this->eventPolicy->create($user, $eventData);

        // Assert
        $this->assertTrue($actual);
    }
}
