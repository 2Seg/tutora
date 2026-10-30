<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Enums\EventType;
use App\Http\Resources\EventResource;
use App\Http\Resources\StudentResource;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

final class EventResourceTest extends UnitTestCase
{
    private readonly Request $request;
    private readonly Event $event;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->event = new Event();
    }

    public function testItSerializesEventId(): void
    {
        // Arrange
        $this->event->id = 1;

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testItSerializesStartTime(): void
    {
        // Arrange
        $this->event->setDateFormat('Y-m-d H:i:s');
        $this->event->start_time = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['startTime']);
    }

    public function testItSerializesEndTime(): void
    {
        // Arrange
        $this->event->setDateFormat('Y-m-d H:i:s');
        $this->event->end_time = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['endTime']);
    }

    public function testItSerializesType(): void
    {
        // Arrange
        $this->event->type = 'blocked';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame(EventType::Blocked, $actual['type']);
    }

    public function testItSerializesCreatedAt(): void
    {
        // Arrange
        $this->event->setDateFormat('Y-m-d H:i:s');
        $this->event->created_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['createdAt']);
    }

    public function testItSerializesUpdatedAt(): void
    {
        // Arrange
        $this->event->setDateFormat('Y-m-d H:i:s');
        $this->event->updated_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['updatedAt']);
    }

    public function testItSerializesDeletedAt(): void
    {
        // Arrange
        $this->event->setDateFormat('Y-m-d H:i:s');
        $this->event->deleted_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['deletedAt']);
    }

    public function testItSerializesLessonStatus(): void
    {
        // Arrange
        $lesson = new Lesson();
        $lesson->setRelation('student', new Student());
        $this->event->setRelation('lesson', $lesson);
        $this->event->type = 'lesson';
        $this->event->lesson->status = 'planned';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('planned', $actual['status']);
    }

    public function testItSerializesLessonTitle(): void
    {
        // Arrange
        $lesson = new Lesson();
        $lesson->setRelation('student', new Student());
        $this->event->setRelation('lesson', $lesson);
        $this->event->type = 'lesson';
        $this->event->lesson->title = 'Title';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('Title', $actual['title']);
    }

    public function testItSerializesLessonMeetingUrl(): void
    {
        // Arrange
        $lesson = new Lesson();
        $lesson->setRelation('student', new Student());
        $this->event->setRelation('lesson', $lesson);
        $this->event->type = 'lesson';
        $this->event->lesson->meeting_url = 'https://test.test';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame('https://test.test', $actual['meetingUrl']);
    }

    public function testItSerializesLessonStudent(): void
    {
        // Arrange
        $student = new Student();
        $lesson = new Lesson();
        $lesson->setRelation('student', $student);
        $this->event->setRelation('lesson', $lesson);
        $this->event->type = 'lesson';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame((new StudentResource($student))->resolve($this->request), $actual['student']);
    }

    public function testItSerializesLessonAttributesIfTypeIsLessonAndLessonExists(): void
    {
        // Arrange
        $expected = [
            'id',
            'startTime',
            'endTime',
            'type',
            'createdAt',
            'updatedAt',
            'deletedAt',
            'status',
            'title',
            'meetingUrl',
            'student',
        ];
        $lesson = new Lesson();
        $lesson->setRelation('student', new Student());
        $this->event->setRelation('lesson', $lesson);
        $this->event->type = 'lesson';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }

    public function testItDoesNotSerializeLessonAttributesIfLessonIsMissing(): void
    {
        // Arrange
        $expected = [
            'id',
            'startTime',
            'endTime',
            'type',
            'createdAt',
            'updatedAt',
            'deletedAt',
        ];
        $this->event->setRelation('lesson', null);
        $this->event->type = 'lesson';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }

    public function testItDoesNotSerializeLessonAttributesIfTypeIsBlocked(): void
    {
        // Arrange
        $expected = [
            'id',
            'startTime',
            'endTime',
            'type',
            'createdAt',
            'updatedAt',
            'deletedAt',
        ];
        $this->event->type = 'blocked';

        // Act
        $actual = (new EventResource($this->event))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }
}
