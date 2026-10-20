<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher;

use App\Models\Student;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class StudentControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        /// Act
        $actual = $this->get('/teacher/students');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItCallsTheExpectedComponent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $expected = 'teacher/students/Index';

        // Act
        $actual = $this->actingAs($user)->get('/teacher/students');

        // Assert
        $actual->assertOk();
        $this->assertSame($expected, $actual->inertiaPage()['component']);
    }

    public function testItSendsExpectedPropsToInertia(): void
    {
        // Arrange
        $user = User::factory()->create();
        $students = Student::factory()
            ->count(2)
            ->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/students');

        // Assert
        $this->assertCount(2, $actual->inertiaProps('students'));
        $this->assertSame($students->first()->id, $actual->inertiaProps('students.0.id'));
    }
}
