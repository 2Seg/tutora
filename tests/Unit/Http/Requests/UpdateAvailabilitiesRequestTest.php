<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateAvailabilitiesRequest;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class UpdateAvailabilitiesRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly UpdateAvailabilitiesRequest $updateAvailabilitiesRequest;

    protected function setUp(): void
    {
        $this->updateAvailabilitiesRequest = new UpdateAvailabilitiesRequest();
    }

    public function testItFailsIfAvailabilitiesIsMissing(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, []);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Present', $validator->failed()['availabilities']);
    }

    public function testItFailsIfAvailabilitiesIsNotAnArray(): void
    {
        $data = ['availabilities' => 123];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Array', $validator->failed()['availabilities']);
    }

    public function testItFailsIfDayOfWeekIsNotPresent(): void
    {
        $data = ['availabilities' => [[]]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['availabilities.0.day_of_week']);
    }

    public function testItFailsIfDayOfWeekIsNotAnInteger(): void
    {
        $data = ['availabilities' => [['day_of_week' => 'not an integer']]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Integer', $validator->failed()['availabilities.0.day_of_week']);
    }

    public function testItFailsIfDayOfWeekIsNotBetween1And7(): void
    {
        $data = ['availabilities' => [['day_of_week' => 0]]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Between', $validator->failed()['availabilities.0.day_of_week']);
    }

    public function testItFailsIfStartTimeIsNotPresent(): void
    {
        $data = ['availabilities' => [[]]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['availabilities.0.start_time']);
    }

    public function testItFailsIfStartTimeIsNotATime(): void
    {
        $data = ['availabilities' => [['start_time' => '2000-01-01']]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('DateFormat', $validator->failed()['availabilities.0.start_time']);
    }

    public function testItFailsIfEndTimeIsNotPresent(): void
    {
        $data = ['availabilities' => [[]]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['availabilities.0.end_time']);
    }

    public function testItFailsIfEndTimeIsNotATime(): void
    {
        $data = ['availabilities' => [['end_time' => '2000-01-01']]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('DateFormat', $validator->failed()['availabilities.0.end_time']);
    }

    public function testItFailsIfEndTimeIsBeforeStartTime(): void
    {
        $data = ['availabilities' => [
            [
                'start_time' => '09:00',
                'end_time' => '08:50',
            ],
        ]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['availabilities.0.end_time']);
    }

    public function testItFailsIfEndTimeEqualsStartTime(): void
    {
        $data = ['availabilities' => [
            [
                'start_time' => '09:00',
                'end_time' => '09:00',
            ],
        ]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('After', $validator->failed()['availabilities.0.end_time']);
    }

    public function testItPassesIfDataIsValid(): void
    {
        $data = ['availabilities' => [
            [
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '09:50',
            ],
            [
                'day_of_week' => 2,
                'start_time' => '09:00',
                'end_time' => '09:50',
            ],
        ]];

        // Act
        $validator = $this->makeRequestValidator($this->updateAvailabilitiesRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }
}
