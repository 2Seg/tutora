<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\AuthenticateUser;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class AuthenticateUserTest extends UnitTestCase
{
    private readonly StatefulGuard|Mock $guard;
    private readonly Session|Mock $session;
    private readonly AuthenticateUser $authenticateUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guard = $this->mock(StatefulGuard::class);
        $this->session = $this->spy(Session::class);
        $this->authenticateUser = new AuthenticateUser($this->guard, $this->session);
    }

    public function testItThrowsAValidationExceptionWhenAuthAttemptFails(): void
    {
        // Assert
        $this->expectException(ValidationException::class);

        // Arrange
        $this->guard->shouldReceive('attempt')
            ->once()
            ->with([], false)
            ->andReturn(false);

        // Act
        $this->authenticateUser->handle([]);
    }

    public function testItRegeneratesTheSessionWhenAuthAttemptPasses(): void
    {
        // Arrange
        $this->guard->shouldReceive('attempt')
            ->once()
            ->with([], false)
            ->andReturn(true);

        // Act
        $this->authenticateUser->handle([]);

        // Assert
        $this->session->shouldHaveReceived('regenerate')->once();
    }
}
