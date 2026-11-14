<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Student;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Student\EloquentStudentRepository;
use Tests\Feature\FeatureTestCase;

final class EloquentStudentRepositoryTest extends FeatureTestCase
{
    private readonly EloquentStudentRepository $studentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentRepository = new EloquentStudentRepository();
    }

    public function testItRetrievesStudentsFromUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertTrue($actual->contains($student));
    }

    public function testItDoesNotRetrieveStudentsFromAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->studentRepository->getAllFromUser($user);

        // Assert
        $this->assertFalse($actual->contains($student));
    }

    public function testItReturnsFalseIfStudentDoesNotBelongToUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $anotherUser->id]);


        // Act
        $actual = $this->studentRepository->belongsToUser($student->id, $user);

        // Assert
        $this->assertFalse($actual);
    }

    public function testItReturnsTrueIfStudentBelongsToUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);


        // Act
        $actual = $this->studentRepository->belongsToUser($student->id, $user);

        // Assert
        $this->assertTrue($actual);
    }
}
