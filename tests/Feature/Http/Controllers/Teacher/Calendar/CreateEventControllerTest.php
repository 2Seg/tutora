<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar;

use App\Models\Student;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class CreateEventControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->post('/teacher/calendar');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItStoresATimeOffEvent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = [
            'type' => 'time_off',
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2010-01-01 00:00:00',
        ];

        // Act
        $actual = $this->actingAs($user)->post('/teacher/calendar', $data);

        // Assert
        $user->refresh();
        $actual->assertRedirect('/teacher/calendar');
        $this->assertCount(1, $user->events);
        $this->assertNull($user->events->first()->lesson);
    }

    public function testItStoresALessonEvent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $data = [
            'type' => 'lesson',
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2010-01-01 00:00:00',
            'status' => 'planned',
            'student_id' => $student->id,
            'title' => $student->full_name,
        ];

        // Act
        $actual = $this->actingAs($user)->post('/teacher/calendar', $data);

        // Assert
        $user->refresh();
        $actual->assertRedirect('/teacher/calendar');
        $this->assertCount(1, $user->events);
        $this->assertNotNull($user->events->first()->lesson);
    }

    public function testItForbidsALessonForAnotherUsersStudent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $otherUser->id]);
        $data = [
            'type' => 'lesson',
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2010-01-01 00:00:00',
            'status' => 'planned',
            'student_id' => $student->id,
            'title' => $student->full_name,
        ];

        // Act
        $actual = $this->actingAs($user)->post('/teacher/calendar', $data);

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseCount('events', 0);
    }
}
