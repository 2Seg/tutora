<?php

declare(strict_types=1);

namespace Tests\Unit\Data\Event;

use App\Data\Event\UpdateEventData;
use Carbon\CarbonImmutable;
use Tests\Unit\UnitTestCase;

final class UpdateEventDataTest extends UnitTestCase
{
    public function testItSetsOnlyScheduleForTimeOff(): void
    {
        // Arrange
        $updateEventData = UpdateEventData::forTimeOff(
            CarbonImmutable::parse('2000-01-01 00:00:00'),
            CarbonImmutable::parse('2000-01-02 00:00:00'),
        );

        // Act & Assert
        $this->assertTrue($updateEventData->startsAt->equalTo('2000-01-01 00:00:00'));
        $this->assertTrue($updateEventData->endsAt->equalTo('2000-01-02 00:00:00'));
        $this->assertNull($updateEventData->lessonTitle);
    }

    public function testItSetsOnlyLessonTitleForLesson(): void
    {
        // Arrange
        $updateEventData = UpdateEventData::forLesson('Event Title');

        // Act & Assert
        $this->assertNull($updateEventData->startsAt);
        $this->assertNull($updateEventData->endsAt);
        $this->assertSame('Event Title', $updateEventData->lessonTitle);
    }
}
