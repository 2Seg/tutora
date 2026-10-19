<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Student;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Student\EloquentStudentRepository;
use Tests\Feature\FeatureTestCase;

final class EloquentStudentRepositoryTest extends FeatureTestCase
{
    private EloquentStudentRepository $studentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentRepository = new EloquentStudentRepository();
    }

    function testItRetrievesStudentsFromUser() {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->studentRepository->getStudentsFromUser($user);

        // Assert
        $this->assertTrue($actual->contains($student));
    }

    function testItDoesNotRetrieveStudentsFromAnotherUser() {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->studentRepository->getStudentsFromUser($user);

        // Assert
        $this->assertFalse($actual->contains($student));
    }
}
