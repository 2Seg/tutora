<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Student;

use App\Models\Student;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class CreateStudentControllerTest extends FeatureTestCase
{
    private const array VALID_DATA = [
        'email' => 'john@doe.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '+33612345678',
        'timezone' => 'Europe/Paris',
    ];

    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->post('/teacher/students');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItStoresAStudentForTheAuthenticatedUser(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->post('/teacher/students', self::VALID_DATA);

        // Assert
        $user->refresh();
        $actual->assertRedirect('/teacher/students');
        $this->assertCount(1, $user->students);
        $student = $user->students->first();
        $this->assertSame('john@doe.com', $student->email);
        $this->assertSame('John', $student->first_name);
        $this->assertSame('Doe', $student->last_name);
        $this->assertSame('+33612345678', $student->phone);
        $this->assertSame('Europe/Paris', $student->timezone);
    }

    public function testItRejectsADuplicateEmail(): void
    {
        // Arrange
        $user = User::factory()->create();
        Student::factory()->create(['email' => self::VALID_DATA['email']]);

        // Act
        $actual = $this->actingAs($user)->post('/teacher/students', self::VALID_DATA);

        // Assert
        $actual->assertSessionHasErrors('email');
        $this->assertDatabaseCount('students', 1);
    }

    public function testItRejectsMissingFields(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->post('/teacher/students');

        // Assert
        $actual->assertSessionHasErrors(['email', 'first_name', 'last_name', 'phone', 'timezone']);
        $this->assertDatabaseCount('students', 0);
    }
}
