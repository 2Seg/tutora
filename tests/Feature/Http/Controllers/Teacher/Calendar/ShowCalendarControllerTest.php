<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar;

use App\Models\Availability;
use App\Models\Event;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia;
use Tests\Feature\FeatureTestCase;

final class ShowCalendarControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        /// Act
        $actual = $this->get('/teacher/calendar');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItCallsTheExpectedComponent(): void
    {
        // Arrange
        $user = User::factory()->create();
        $expected = 'teacher/Calendar';

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $actual->assertOk();
        $this->assertSame($expected, $actual->inertiaPage()['component']);
    }

    public function testItSendsEventsOfTheCurrentWeekToInertia(): void
    {
        // Arrange
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00:00'));
        $user = User::factory()->create(['timezone' => 'UTC']);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-24 10:00:00',
            'ends_at' => '2026-11-24 11:00:00',
        ]);
        Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-12-01 10:00:00',
            'ends_at' => '2026-12-01 11:00:00',
        ]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertCount(1, $actual->inertiaProps('events'));
        $this->assertSame($event->id, $actual->inertiaProps('events.0.id'));
    }

    public function testItSendsEventsOverflowingOnTheWeek(): void
    {
        // Arrange
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00:00'));
        $user = User::factory()->create(['timezone' => 'UTC']);
        $startingBefore = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-22 22:00:00',
            'ends_at' => '2026-11-23 02:00:00',
        ]);
        $endingAfter = Event::factory()->timeOff()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-29 22:00:00',
            'ends_at' => '2026-11-30 02:00:00',
        ]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertEqualsCanonicalizing(
            [$startingBefore->id, $endingAfter->id],
            array_column($actual->inertiaProps('events'), 'id'),
        );
    }

    public function testItSendsCurrentWeekStartInUserTimezone(): void
    {
        // Arrange
        $this->travelTo(CarbonImmutable::parse('2026-11-22 20:00:00'));
        $user = User::factory()->create(['timezone' => 'Pacific/Auckland']);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertSame('2026-11-23', $actual->inertiaProps('weekStart'));
    }

    public function testItSendsEventsOfTheRequestedWeek(): void
    {
        // Arrange
        $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00:00'));
        $user = User::factory()->create(['timezone' => 'UTC']);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-17 10:00:00',
            'ends_at' => '2026-11-17 11:00:00',
        ]);
        Event::factory()->create([
            'user_id' => $user->id,
            'starts_at' => '2026-11-24 10:00:00',
            'ends_at' => '2026-11-24 11:00:00',
        ]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar?week=2026-11-16');

        // Assert
        $this->assertSame('2026-11-16', $actual->inertiaProps('weekStart'));
        $this->assertCount(1, $actual->inertiaProps('events'));
        $this->assertSame($event->id, $actual->inertiaProps('events.0.id'));
    }

    public function testItNormalisesRequestedWeekToItsMonday(): void
    {
        // Arrange
        $user = User::factory()->create(['timezone' => 'UTC']);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar?week=2026-11-19');

        // Assert
        $this->assertSame('2026-11-16', $actual->inertiaProps('weekStart'));
    }

    public function testItRejectsInvalidWeek(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar?week=foo');

        // Assert
        $actual->assertSessionHasErrors('week');
    }

    public function testItSendsAvailabilitiesToInertia(): void
    {
        // Arrange
        $user = User::factory()->create();
        $availabilities = Availability::factory()
            ->count(2)
            ->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertCount(2, $actual->inertiaProps('availabilities'));
        $this->assertSame($availabilities->first()->id, $actual->inertiaProps('availabilities.0.id'));
    }

    public function testItSendsSelectedEventIdFromQuery(): void
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get("/teacher/calendar?event={$event->id}");

        // Assert
        $this->assertSame($event->id, $actual->inertiaProps('selectedEventId'));
    }

    public function testSelectedEventIdIsNullWithoutQuery(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertNull($actual->inertiaProps('selectedEventId'));
    }

    public function testItDoesNotSendStudentsOnInitialLoad(): void
    {
        // Arrange
        $user = User::factory()->create();
        Student::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $actual->assertInertia(fn(AssertableInertia $page) => $page->missing('students'));
    }

    public function testItSendsStudentsAsDeferredProp(): void
    {
        // Arrange
        $user = User::factory()->create();
        $students = Student::factory()
            ->count(2)
            ->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $actual->assertInertia(
            fn(AssertableInertia $page) => $page->loadDeferredProps(
                fn(AssertableInertia $deferred) => $deferred
                    ->has('students', 2)
                    ->where('students.0.id', $students->first()->id)
            )
        );
    }
}
