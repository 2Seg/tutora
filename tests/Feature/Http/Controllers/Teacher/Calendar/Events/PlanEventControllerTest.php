<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar\Events;

use App\Enums\LessonStatus;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Tests\Feature\FeatureTestCase;

final class PlanEventControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->patch('/teacher/calendar/events/1/plan');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItForbidsPlanningTimeOff(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->timeOff()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function testItForbidsPlanningLessonOfAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Requested])->create(['user_id' => $otherUser->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $otherUser->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Requested,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
    }

    public function testItForbidsPlanningCancelledLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Cancelled])->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

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
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
    }

    public function testItForbidsPlanningPlannedLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Planned])->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

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
    }

    public function testItForbidsPlanningPastLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Requested])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::parse('2000-01-01 00:00:00'),
        ]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

        // Assert
        $actual->assertForbidden();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Requested,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
    }

    public function testItPlansLesson(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Requested])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::parse('3000-01-01 00:00:00'),
        ]);

        // Act
        $actual = $this->actingAs($user)->patch("/teacher/calendar/events/{$event->id}/plan");

        // Assert
        $actual->assertRedirect('/teacher/calendar');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Requested,
        ]);
        $this->assertDatabaseHas('lessons', [
            'event_id' => $event->id,
            'status' => LessonStatus::Planned,
        ]);
    }

    public function testItRedirectsBackToTheDisplayedWeek(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->hasLesson(['status' => LessonStatus::Requested])->create([
            'user_id' => $user->id,
            'starts_at' => CarbonImmutable::parse('3000-01-01 00:00:00'),
        ]);

        // Act
        $actual = $this->actingAs($user)
            ->from('/teacher/calendar?week=2026-11-16')
            ->patch("/teacher/calendar/events/{$event->id}/plan");

        // Assert
        $actual->assertRedirect('/teacher/calendar?week=2026-11-16');
    }
}
