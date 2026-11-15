<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar;

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

final class UpdateEventControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->patch('/teacher/calendar/events/1');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItForbidsUpdatingEventOfAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->hasLesson(['title' => 'Event Title'])->create(['user_id' => $otherUser->id]);
        $data = ['title' => 'Modified Event Title'];

        // Act
        $actual = $this->actingAs($user)->patch('/teacher/calendar/events/' . $event->id, $data);

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $otherUser->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'title' => 'Event Title',
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'title' => 'Modified Event Title',
        ]);
    }

    public function testItForbidsUpdatingPastLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['title' => 'Event Title'])->create([
            'user_id' => $user->id,
            'starts_at' => '2000-01-01 00:00:00',
        ]);
        $data = ['title' => 'Modified Event Title'];

        // Act
        $actual = $this->actingAs($user)->patch('/teacher/calendar/events/' . $event->id, $data);

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'title' => 'Event Title',
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'title' => 'Modified Event Title',
        ]);
    }

    public function testItUpdatesLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['title' => 'Event Title'])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::now()->addWeek(),
        ]);
        $data = ['title' => 'Modified Event Title'];

        // Act
        $actual = $this->actingAs($user)->patch('/teacher/calendar/events/' . $event->id, $data);

        // Assert
        $actual->assertRedirect('/teacher/calendar');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'title' => 'Event Title',
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'title' => 'Modified Event Title',
        ]);
    }

    public function testItUpdatesTimeOff(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-02 00:00:00',
        ]);
        $data = [
            'starts_at' => '2000-01-02 00:00:00',
            'ends_at' => '2000-01-03 00:00:00',
        ];

        // Act
        $actual = $this->actingAs($user)->patch('/teacher/calendar/events/' . $event->id, $data);

        // Assert
        $actual->assertRedirect('/teacher/calendar');
        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
            'user_id' => $user->id,
            'starts_at' => '2000-01-01 00:00:00',
            'ends_at' => '2000-01-02 00:00:00',
        ]);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
            'starts_at' => '2000-01-02 00:00:00',
            'ends_at' => '2000-01-03 00:00:00',
        ]);
    }
}
