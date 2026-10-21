<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

final class StudentResourceTest extends TestCase
{
    private readonly Request $request;
    private readonly Student $student;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->student = new Student();
    }

    public function testToArraySerializesStudentId()
    {
        // Arrange
        $this->student->id = 1;

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testToArraySerializesStudentUserId(): void
    {
        // Arrange
        $this->student->user_id = 1;

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame(1, $actual['userId']);
    }

    public function testToArraySerializesStudentEmail(): void
    {
        // Arrange
        $this->student->email = 'test@test.com';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('test@test.com', $actual['email']);
    }

    public function testToArraySerializesStudentFirstName(): void
    {
        // Arrange
        $this->student->first_name = 'Test';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('Test', $actual['firstName']);
    }

    public function testToArraySerializesStudentLastName(): void
    {
        // Arrange
        $this->student->last_name = 'Test';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('Test', $actual['lastName']);
    }

    public function testToArraySerializesStudentFullName(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('Test Tested', $actual['fullName']);
    }

    public function testToArraySerializesStudentInitials(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('TT', $actual['initials']);
    }

    public function testToArraySerializesStudentPhone(): void
    {
        // Arrange
        $this->student->phone = '0123456789';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('0123456789', $actual['phone']);
    }

    public function testToArraySerializesStudentAvatarImg(): void
    {
        // Arrange
        $this->student->avatar_img = 'https://image.com';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('https://image.com', $actual['avatarImg']);
    }

    public function testToArraySerializesStudentTimezone(): void
    {
        // Arrange
        $this->student->timezone = 'Europe/London';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('Europe/London', $actual['timezone']);
    }

    public function testToArraySerializesStudentCreatedAt(): void
    {
        // Arrange
        $this->student->setDateFormat('Y-m-d H:i:s');
        $this->student->created_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['createdAt']);
    }

    public function testToArraySerializesStudentUpdatedAt(): void
    {
        // Arrange
        $this->student->setDateFormat('Y-m-d H:i:s');
        $this->student->updated_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new StudentResource($this->student))->toArray($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['updatedAt']);
    }
}
