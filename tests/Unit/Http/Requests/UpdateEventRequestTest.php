<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Enums\EventType;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class UpdateEventRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly UpdateEventRequest $updateEventRequest;

    protected function setUp(): void
    {
        $this->updateEventRequest = new UpdateEventRequest();
    }

    public function testItFailsIfTimeOffStartsAtIsMissing(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['starts_at']);
    }

    public function testItFailsIfTimeOffStartsAtIsNotADate(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['starts_at' => 'not-a-date'];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Date', $validator->failed()['starts_at']);
    }

    public function testItFailsIfTimeOffEndsAtIsMissing(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['ends_at']);
    }

    public function testItFailsIfTimeOffEndsAtIsNotADate(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['ends_at' => 'not-a-date'];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Date', $validator->failed()['ends_at']);
    }

    public function testItFailsIfTimeOffEndsAtIsBeforeStartsAt(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = [
            'starts_at' => '2010-01-01 00:00:00',
            'ends_at' => '2000-01-01 00:00:00',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['ends_at']);
    }

    public function testItFailsIfTimeOffEndsAtEqualsStartsAt(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = [
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-01 00:00:00',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['ends_at']);
    }

    public function testItPassesIfTimeOffDataIsValid(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = [
            'starts_at' => '2000-01-01 10:00:00',
            'ends_at' => '2000-01-01 11:00:00',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItFailsIfLessonTitleIsMissing(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['title']);
    }

    public function testItFailsIfLessonTitleIsNotAString(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['title' => 123];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['title']);
    }

    public function testItFailsIfLessonTitleIsMoreThan50Characters(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['title' => str_repeat('a', 51)];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['title']);
    }

    public function testItPassesIfLessonTitleIsValid(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['title' => str_repeat('a', 50)];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItPassesIfLessonDataIsValid(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $data = ['title' => 'Event title'];

        // Act
        $validator = $this->makeRequestValidator($this->updateEventRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItBuildsTimeOffEventDataFromDatesIfEventIsTimeOff(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::TimeOff;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $this->updateEventRequest->setValidator(
            $this->makeRequestValidator($this->updateEventRequest, [
                'starts_at' => '2000-01-01 00:00:00',
                'ends_at' => '2010-01-01 00:00:00',
            ]),
        );

        // Act
        $actual = $this->updateEventRequest->toEventData();

        // Assert
        $this->assertSame('2000-01-01 00:00:00', $actual->startsAt->toDateTimeString());
        $this->assertSame('2010-01-01 00:00:00', $actual->endsAt->toDateTimeString());
        $this->assertNull($actual->lessonTitle);
    }

    public function testItBuildsLessonEventDataFromTitleIfEventIsLesson(): void
    {
        // Arrange
        $event = new Event();
        $event->type = EventType::Lesson;
        $this->bindRequestContext($this->updateEventRequest, ['event' => $event]);
        $this->updateEventRequest->setValidator(
            $this->makeRequestValidator($this->updateEventRequest, [
                'title' => 'Modified Event Title',
            ]),
        );

        // Act
        $actual = $this->updateEventRequest->toEventData();

        // Assert
        $this->assertNull($actual->startsAt);
        $this->assertNull($actual->endsAt);
        $this->assertSame('Modified Event Title', $actual->lessonTitle);
    }
}
