<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\ListStudentsRequest;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class ListStudentsRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly ListStudentsRequest $listStudentsRequest;

    protected function setUp(): void
    {
        $this->listStudentsRequest = new ListStudentsRequest();
    }

    public function testItPassesWithoutParameters(): void
    {
        // Act
        $validator = $this->makeRequestValidator($this->listStudentsRequest, []);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItPassesIfStudentIsAnInteger(): void
    {
        // Arrange
        $data = ['student' => '12'];

        // Act
        $validator = $this->makeRequestValidator($this->listStudentsRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItFailsIfStudentIsNotAnInteger(): void
    {
        // Arrange
        $data = ['student' => 'abc'];

        // Act
        $validator = $this->makeRequestValidator($this->listStudentsRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Integer', $validator->failed()['student']);
    }
}
