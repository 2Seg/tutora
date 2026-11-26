<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\ShowCalendarRequest;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class ShowCalendarRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly ShowCalendarRequest $showCalendarRequest;

    protected function setUp(): void
    {
        $this->showCalendarRequest = new ShowCalendarRequest();
    }

    public function testItPassesWithoutParameters(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->showCalendarRequest, []);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItPassesIfParametersAreValid(): void
    {
        // Arrange
        $data = ['week' => '2026-11-16', 'event' => '12'];

        // Act
        $validator = $this->makeRequestValidator($this->showCalendarRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItFailsIfWeekIsNotADate(): void
    {
        // Arrange
        $data = ['week' => 'not-a-date'];

        // Act
        $validator = $this->makeRequestValidator($this->showCalendarRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('DateFormat', $validator->failed()['week']);
    }

    public function testItFailsIfWeekIsNotInYearMonthDayFormat(): void
    {
        // Arrange
        $data = ['week' => '16/11/2026'];

        // Act
        $validator = $this->makeRequestValidator($this->showCalendarRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('DateFormat', $validator->failed()['week']);
    }

    public function testItFailsIfEventIsNotAnInteger(): void
    {
        // Arrange
        $data = ['event' => 'abc'];

        // Act
        $validator = $this->makeRequestValidator($this->showCalendarRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Integer', $validator->failed()['event']);
    }
}
