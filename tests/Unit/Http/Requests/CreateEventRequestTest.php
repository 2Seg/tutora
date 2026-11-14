<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Enums\EventType;
use App\Enums\LessonStatus;
use App\Http\Requests\CreateEventRequest;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class CreateEventRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly CreateEventRequest $createEventRequest;

    protected function setUp(): void
    {
        $this->createEventRequest = new CreateEventRequest();
    }

    public function testItFailsIfTypeIsMissing(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['type']);
    }

    public function testItFailsIfTypeIsNotAString(): void
    {
        // Arrange
        $data = ['type' => 123];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['type']);
    }

    public function testItFailsIfTypeIsNotAnEventType(): void
    {
        // Arrange
        $data = ['type' => 'another-type'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Illuminate\Validation\Rules\Enum', $validator->failed()['type']);
    }

    public function testItFailsIfStartsAtIsMissing(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['starts_at']);
    }

    public function testItFailsIfStartsAtIsNotADate(): void
    {
        // Arrange
        $data = ['starts_at' => 'not-a-date'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Date', $validator->failed()['starts_at']);
    }

    public function testItFailsIfEndsAtIsMissing(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['ends_at']);
    }

    public function testItFailsIfEndsAtIsNotADate(): void
    {
        // Arrange
        $data = ['ends_at' => 'not-a-date'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Date', $validator->failed()['ends_at']);
    }

    public function testItFailsIfEndsAtIsBeforeStartsAt(): void
    {
        // Arrange
        $data = [
            'starts_at' => '2010-01-01 00:00:00',
            'ends_at' => '2000-01-01 00:00:00',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['ends_at']);
    }

    public function testItFailsIfEndsAtEqualsStartsAt(): void
    {
        $data = [
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-01 00:00:00',
        ];

        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['ends_at']);
    }

    public function testItPassesIfTimeOffDataIsValid(): void
    {
        // Arrange
        $data = [
            'type' => 'time_off',
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2010-01-01 00:00:00',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItFailsIfTypeIsLessonAndStatusIsMissing(): void
    {
        // Arrange
        $data = ['type' => 'lesson'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['status']);
    }

    public function testItFailsIfTypeIsLessonAndStatusIsNotAString(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'status' => 123,
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['status']);
    }

    public function testItFailsIfTypeIsLessonAndStatusIsNotALessonType(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'status' => 'not-a-lesson-type',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Illuminate\Validation\Rules\Enum', $validator->failed()['status']);
    }

    public function testItFailsIfTypeIsLessonAndStudentIdIsMissing(): void
    {
        // Arrange
        $data = ['type' => 'lesson'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['student_id']);
    }

    public function testItFailsIfTypeIsLessonAndStudentIdIsNotAnInteger(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'student_id' => 'not-an-integer',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Integer', $validator->failed()['student_id']);
    }

    public function testItFailsIfTypeIsLessonAndTitleIsMissing(): void
    {
        // Arrange
        $data = ['type' => 'lesson'];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['title']);
    }

    public function testItFailsIfTypeIsLessonAndTitleIsNotAString(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'title' => 123,
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['title']);
    }

    public function testItFailsIfTypeIsLessonAndTitleIsMoreThan50Characters(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'title' => str_repeat('a', 51),
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['title']);
    }

    public function testItPassesIfLessonDataIsValid(): void
    {
        // Arrange
        $data = [
            'type' => 'lesson',
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2010-01-01 00:00:00',
            'status' => 'planned',
            'student_id' => 1,
            'title' => 'Event Title',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->createEventRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItBuildsAnEventDataLesson(): void
    {
        // Arrange
        $this->createEventRequest->setValidator(
            $this->makeRequestValidator($this->createEventRequest, [
                'type' => 'lesson',
                'starts_at' => '2000-01-01 00:00:00',
                'ends_at' => '2010-01-01 00:00:00',
                'status' => 'planned',
                'student_id' => 1,
                'title' => 'Event Title',
            ]),
        );

        // Act
        $actual = $this->createEventRequest->toEventData();

        // Assert
        $this->assertSame(EventType::Lesson, $actual->type);
        $this->assertSame('2000-01-01 00:00:00', $actual->startsAt->toDateTimeString());
        $this->assertSame('2010-01-01 00:00:00', $actual->endsAt->toDateTimeString());
        $this->assertSame(LessonStatus::Planned, $actual->lessonData->status);
        $this->assertSame(1, $actual->lessonData->studentId);
        $this->assertSame('Event Title', $actual->lessonData->title);
    }

    public function testItBuildsEventDataWithoutALessonForTimeOff(): void
    {
        // Arrange
        $this->createEventRequest->setValidator(
            $this->makeRequestValidator($this->createEventRequest, [
                'type' => 'time_off',
                'starts_at' => '2000-01-01 00:00:00',
                'ends_at' => '2010-01-01 00:00:00',
            ]),
        );

        // Act
        $actual = $this->createEventRequest->toEventData();

        // Assert
        $this->assertSame(EventType::TimeOff, $actual->type);
        $this->assertSame('2000-01-01 00:00:00', $actual->startsAt->toDateTimeString());
        $this->assertSame('2010-01-01 00:00:00', $actual->endsAt->toDateTimeString());
        $this->assertNull($actual->lessonData);
    }

    public function testItIgnoresLessonFieldsWhenBuildingEventDataForTimeOff(): void
    {
        // Arrange
        $this->createEventRequest->setValidator(
            $this->makeRequestValidator($this->createEventRequest, [
                'type' => 'time_off',
                'starts_at' => '2000-01-01 00:00:00',
                'ends_at' => '2010-01-01 00:00:00',
                'status' => 'planned',
                'student_id' => 1,
                'title' => 'Event Title',
            ]),
        );

        // Act
        $actual = $this->createEventRequest->toEventData();

        // Assert
        $this->assertSame(EventType::TimeOff, $actual->type);
        $this->assertSame('2000-01-01 00:00:00', $actual->startsAt->toDateTimeString());
        $this->assertSame('2010-01-01 00:00:00', $actual->endsAt->toDateTimeString());
        $this->assertNull($actual->lessonData);
    }
}
