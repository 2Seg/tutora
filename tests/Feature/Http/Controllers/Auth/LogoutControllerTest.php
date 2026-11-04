<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class LogoutControllerTest extends FeatureTestCase
{
    public function testItRedirectsGuestToLoginOnLogout(): void
    {
        // Act
        $actual = $this->post('/logout');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItRedirectsAuthenticatedUserToLoginOnLogout(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->post('/logout');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItLogsAuthenticatedUserOut(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->actingAs($user)->post('/logout');

        // Assert
        $this->assertGuest();
    }
}
