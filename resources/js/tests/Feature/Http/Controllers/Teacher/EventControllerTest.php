<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\Teacher;

use App\Models\Event;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class EventControllerTest extends FeatureTestCase
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

    public function testItSendsExpectedPropsToInertia(): void
    {
        // Arrange
        $user = User::factory()->create();
        $events = Event::factory()
            ->count(2)
            ->create(['user_id' => $user->id]);

        // Act
        $actual = $this->actingAs($user)->get('/teacher/calendar');

        // Assert
        $this->assertCount(2, $actual->inertiaProps('events'));
        $this->assertSame($events->first()->id, $actual->inertiaProps('events.0.id'));
    }
}
