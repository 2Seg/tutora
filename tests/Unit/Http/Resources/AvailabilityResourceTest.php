<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\AvailabilityResource;
use App\Models\Availability;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

final class AvailabilityResourceTest extends UnitTestCase
{
    private readonly Request $request;
    private readonly Availability $availability;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->availability = new Availability();
    }

    public function testItSerializesAvailabilityId(): void
    {
        // Arrange
        $this->availability->id = 1;

        // Act
        $actual = (new AvailabilityResource($this->availability))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testItSerializesAvailabilityDayOfWeek(): void
    {
        // Arrange
        $this->availability->day_of_week = 1;

        // Act
        $actual = (new AvailabilityResource($this->availability))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['dayOfWeek']);
    }

    public function testItSerializesAvailabilityStartTime(): void
    {
        // Arrange
        $this->availability->start_time = '08:00:00';

        // Act
        $actual = (new AvailabilityResource($this->availability))->resolve($this->request);

        // Assert
        $this->assertSame('08:00:00', $actual['startTime']);
    }

    public function testItSerializesAvailabilityEndTime(): void
    {
        // Arrange
        $this->availability->end_time = '12:00:00';

        // Act
        $actual = (new AvailabilityResource($this->availability))->resolve($this->request);

        // Assert
        $this->assertSame('12:00:00', $actual['endTime']);
    }

    public function testItSerializesOnlySpecificFields(): void
    {
        // Arrange
        $expected = ['id', 'dayOfWeek', 'startTime', 'endTime'];

        // Act
        $actual = (new AvailabilityResource($this->availability))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }
}
