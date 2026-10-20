<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\AuthenticateUser;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class AuthenticateUserTest extends UnitTestCase
{
    private readonly AuthManager|Mock $authManager;
    private readonly Session|Mock $session;
    private readonly AuthenticateUser $authenticateUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManager = $this->mock(AuthManager::class);
        $this->session = $this->spy(Session::class);
        $this->authenticateUser = new AuthenticateUser($this->authManager, $this->session);
    }

    public function testItThrowsAValidationExceptionWhenAuthAttemptFails(): void
    {
        // Assert
        $this->expectException(ValidationException::class);

        // Arrange
        $this->authManager->shouldReceive('attempt')
            ->once()
            ->with([], false)
            ->andReturn(false);

        // Act
        $this->authenticateUser->attempt([]);
    }

    public function testItRegeneratesTheSessionWhenAuthAttemptPasses(): void
    {
        // Arrange
        $this->authManager->shouldReceive('attempt')
            ->once()
            ->with([], false)
            ->andReturn(true);

        // Act
        $this->authenticateUser->attempt([]);

        // Assert
        $this->session->shouldHaveReceived('regenerate')->once();
    }
}
