<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\AuthenticateUserAction;
use App\Exceptions\InvalidCredentials;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Mockery;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class AuthenticateUserActionTest extends UnitTestCase
{
    private readonly StatefulGuard|Mock $guard;
    private readonly Session|Mock $session;
    private readonly AuthenticateUserAction $authenticateUser;

    protected function setUp(): void
    {
        $this->guard = Mockery::mock(StatefulGuard::class);
        $this->session = Mockery::spy(Session::class);
        $this->authenticateUser = new AuthenticateUserAction($this->guard, $this->session);
    }

    public function testItThrowsAValidationExceptionWhenAuthAttemptFails(): void
    {
        // Assert
        $this->expectException(InvalidCredentials::class);

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
