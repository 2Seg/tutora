<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar\Events;

use App\Models\Event;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class DeleteEventControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->delete('/teacher/calendar/events/1');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItForbidsDeletingTimeOffOfAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->timeOff()->create(['user_id' => $otherUser->id]);

        // Act
        $actual = $this->actingAs($user)->delete('/teacher/calendar/events/' . $event->id);

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function testItForbidsDeletingLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->delete('/teacher/calendar/events/' . $event->id);

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function testItAllowsDeletingLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->delete('/teacher/calendar/events/' . $event->id);

        // Assert
        $actual->assertRedirect('/teacher/calendar');
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
