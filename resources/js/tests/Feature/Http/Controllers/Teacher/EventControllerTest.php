<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\Teacher;

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
}
