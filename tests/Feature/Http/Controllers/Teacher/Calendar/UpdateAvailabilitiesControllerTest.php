<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Teacher\Calendar;

use App\Models\Availability;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class UpdateAvailabilitiesControllerTest extends FeatureTestCase
{
    public function testGuestIsRedirectedToLogin(): void
    {
        // Act
        $actual = $this->put('/teacher/calendar/availabilities');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItDeletesUserAvailabilities(): void
    {
        // Arrange
        $user = User::factory()->create();
        $availability = Availability::factory()->create(['user_id' => $user->id]);
        $data = ['availabilities' => []];

        // Act
        $actual = $this->actingAs($user)->put('/teacher/calendar/availabilities', $data);

        // Assert
        $this->assertModelMissing($availability);
        $actual->assertRedirect('/teacher/calendar');
    }

    public function testItUpdatesUserAvailabilities(): void
    {
        // Arrange
        $user = User::factory()->create();
        $availability = Availability::factory()->create(['user_id' => $user->id]);
        $data = ['availabilities' => [
            [
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '09:50',
            ],
            [
                'day_of_week' => 2,
                'start_time' => '09:00',
                'end_time' => '09:50',
            ],
        ]];

        // Act
        $actual = $this->actingAs($user)->put('/teacher/calendar/availabilities', $data);

        // Assert
        $this->assertModelMissing($availability);
        $this->assertCount(2, $user->refresh()->availabilities);
        $actual->assertRedirect('/teacher/calendar');
    }
}
