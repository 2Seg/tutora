<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\StudentSummaryResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

final class StudentSummaryResourceTest extends UnitTestCase
{
    private readonly Request $request;
    private readonly Student $student;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->student = new Student();
    }

    public function testItSerializesStudentId()
    {
        // Arrange
        $this->student->id = 1;

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testItSerializesStudentFullName(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Test Tested', $actual['fullName']);
    }

    public function testItSerializesStudentInitials(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('TT', $actual['initials']);
    }

    public function testItSerializesStudentAvatarImg(): void
    {
        // Arrange
        $this->student->avatar_img = 'https://image.com';

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('https://image.com', $actual['avatarImg']);
    }

    public function testItSerializesStudentTimezone(): void
    {
        // Arrange
        $this->student->timezone = 'Europe/Paris';

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Europe/Paris', $actual['timezone']);
    }

    public function testItSerializesOnlySpecificFields(): void
    {
        // Arrange
        $expected = [
            'id',
            'fullName',
            'initials',
            'avatarImg',
            'timezone',
        ];

        // Act
        $actual = (new StudentSummaryResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }
}
