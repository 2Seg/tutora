<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar\Events;

use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

final class CancelEventControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->patch('/teacher/calendar/events/1/cancel');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItForbidsCancellingOfTimeOff(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/cancel");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function testItForbidsCancellingLessonOfAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Planned])->create(['user_id' => $otherUser->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/cancel");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $otherUser->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Cancelled,
        ]);
    }

    public function testItForbidsCancellingCancelledLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Cancelled])->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/cancel");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Cancelled,
        ]);
    }

    public function testItForbidsCancellingPastLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Planned])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::parse('2000-01-01 00:00:00'),
        ]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/cancel");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Cancelled,
        ]);
    }

    public function testItCancelsLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Planned])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::parse('3000-01-01 00:00:00'),
        ]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/cancel");

        // Assert
        $actual->assertRedirect('/teacher/calendar');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Cancelled,
        ]);
    }
}
