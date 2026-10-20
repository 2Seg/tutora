<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Tests\Feature\FeatureTestCase;

final class AuthControllerTest extends FeatureTestCase
{
    public function testItRedirectsUserOnLogin(): void
    {
        // Act
        $actual = $this->post('/login');

        // Assert
        $actual->assertRedirect();
    }

    public function testItLogsUserIn(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
        ];
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'secret',
        ]);

        // Act
        $this->post('/login', $data);

        // Assert
        $this->assertAuthenticatedAs($user);
    }

    public function testItRemembersUserIfRememberIsTrue(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
            'remember' => 1,
        ];
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'secret',
        ]);

        // Act
        $this->post('/login', $data);

        // Assert
        $this->assertNotNull($user->refresh()->remember_token);
    }

    public function testItDoesNotRememberUserIfRememberIsFalse(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
        ];
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'secret',
        ]);

        // Act
        $this->post('/login', $data);

        // Assert
        $this->assertNull($user->refresh()->remember_token);
    }

    public function testItFailsIfEmailDoesNotMatch(): void
    {
        // Arrange
        $data = [
            'email' => 'wrong-email@test.com',
            'password' => 'secret',
        ];
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'secret',
        ]);

        // Act
        $this->post('/login', $data);

        // Assert
        $this->assertGuest();
    }

    public function testItFailsIfPasswordDoesNotMatch(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'wrong-secret',
        ];
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'secret',
        ]);

        // Act
        $this->post('/login', $data);

        // Assert
        $this->assertGuest();
    }

    public function testItRedirectsAuthenticatedUserToDashboard(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->get('/login');

        // Assert
        $actual->assertRedirect('/teacher');
    }

    public function testItRedirectsGuestToLoginOnLogout(): void
    {
        // Act
        $actual = $this->post('/logout');

        // Assert
        $actual->assertRedirect('/login');
    }

    public function testItRedirectsAuthenticatedUserToHomeOnLogout(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $actual = $this->actingAs($user)->post('/logout');

        // Assert
        $actual->assertRedirect('/');
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
