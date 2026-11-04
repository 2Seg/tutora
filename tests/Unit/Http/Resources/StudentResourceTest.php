<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

final class StudentResourceTest extends UnitTestCase
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
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testItSerializesStudentUserId(): void
    {
        // Arrange
        $this->student->user_id = 1;

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['userId']);
    }

    public function testItSerializesStudentEmail(): void
    {
        // Arrange
        $this->student->email = 'test@test.com';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('test@test.com', $actual['email']);
    }

    public function testItSerializesStudentFirstName(): void
    {
        // Arrange
        $this->student->first_name = 'Test';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Test', $actual['firstName']);
    }

    public function testItSerializesStudentLastName(): void
    {
        // Arrange
        $this->student->last_name = 'Test';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Test', $actual['lastName']);
    }

    public function testItSerializesStudentFullName(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Test Tested', $actual['fullName']);
    }

    public function testItSerializesStudentInitials(): void
    {
        // Arrange
        $this->student->first_name = 'Test';
        $this->student->last_name = 'Tested';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('TT', $actual['initials']);
    }

    public function testItSerializesStudentPhone(): void
    {
        // Arrange
        $this->student->phone = '0123456789';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('0123456789', $actual['phone']);
    }

    public function testItSerializesStudentAvatarImg(): void
    {
        // Arrange
        $this->student->avatar_img = 'https://image.com';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('https://image.com', $actual['avatarImg']);
    }

    public function testItSerializesStudentTimezone(): void
    {
        // Arrange
        $this->student->timezone = 'Europe/London';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('Europe/London', $actual['timezone']);
    }

    public function testItSerializesStudentCreatedAt(): void
    {
        // Arrange
        $this->student->setDateFormat('Y-m-d H:i:s');
        $this->student->created_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['createdAt']);
    }

    public function testItSerializesStudentUpdatedAt(): void
    {
        // Arrange
        $this->student->setDateFormat('Y-m-d H:i:s');
        $this->student->updated_at = '2000-01-01T00:00:00+00:00';

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame('2000-01-01T00:00:00+00:00', $actual['updatedAt']);
    }

    public function testItSerializesOnlySpecificFields(): void
    {
        // Arrange
        $expected = [
            'id',
            'userId',
            'email',
            'firstName',
            'lastName',
            'fullName',
            'initials',
            'phone',
            'avatarImg',
            'timezone',
            'createdAt',
            'updatedAt',
        ];

        // Act
        $actual = (new StudentResource($this->student))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }
}
