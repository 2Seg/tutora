<?php

declare(strict_types=1);

namespace Tests\Unit\Data;

use App\Data\AvailabilitiesData;
use App\Data\AvailabilityData;
use Tests\Unit\UnitTestCase;

final class AvailabilitiesDataTest extends UnitTestCase
{
    public function testItReturnsAnEmptyArrayIfNoAvailabilityDataIsSent(): void
    {
        // Act
        $actual = (new AvailabilitiesData())->toRows();

        // Assert
        $this->assertEmpty($actual);
        $this->assertIsArray($actual);
    }

    public function testItReturnsAnArrayWithSpecificFields(): void
    {
        // Arrange
        $availabilitiesData = new AvailabilitiesData(
            new AvailabilityData(1, '09:00', '09:50'),
            new AvailabilityData(2, '09:00', '09:50')
        );

        // Act
        $actual = $availabilitiesData->toRows();

        // Assert
        $this->assertNotEmpty($actual);
        $this->assertIsArray($actual);
        $this->assertSame(['day_of_week', 'start_time', 'end_time'], array_keys($actual[0]));
    }
}
