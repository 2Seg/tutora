<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\User;

use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use DateTimeZone;
use Tests\Feature\FeatureTestCase;

final class ListStudentsControllerTest extends FeatureTestCase
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
        Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()->subDay()]);
        $newestStudent = Student::factory()->create(['user_id' => $user->id, 'updated_at' => now()]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/students');

        // Assert
        $this->assertCount(2, $actual->inertiaProps('students'));
        $this->assertSame($newestStudent->id, $actual->inertiaProps('students.0.id'));
    }

    public function testItSendsTheTimezonesAcceptedByValidation(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->get('/teacher/students');

        // Assert
        $this->assertSame(DateTimeZone::listIdentifiers(), $actual->inertiaProps('timezones'));
    }

    public function testItSendsTheNextLessonOfEachStudent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $event = Event::factory()->withoutAfterCreating()->create([
            'user_id' => $user->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
        ]);
        Lesson::factory()->create(['event_id' => $event->id, 'student_id' => $student->id, 'status' => LessonStatus::Planned]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/students');

        // Assert
        $this->assertSame($event->id, $actual->inertiaProps('students.0.nextLesson.id'));
    }
}
