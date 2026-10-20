<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\LogoutUser;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Mockery\Mock;
use Tests\Unit\UnitTestCase;

final class LogoutUserTest extends UnitTestCase
{
    private readonly StatefulGuard|Mock $guard;
    private readonly Session|Mock $session;
    private readonly LogoutUser $logoutUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guard = $this->spy(StatefulGuard::class);
        $this->session = $this->spy(Session::class);
        $this->logoutUser = new LogoutUser($this->guard, $this->session);
    }

    public function testItLogsUserOut(): void
    {
        // Arrange
        $this->session->shouldReceive('invalidate')->once();
        $this->session->shouldReceive('regenerateToken')->once();

        // Act
        $this->logoutUser->handle();

        // Assert
        $this->guard->shouldHaveReceived('logout')->once();
    }

    public function testItInvalidatesSession(): void
    {
        // Arrange
        $this->guard->shouldReceive('logout')->once();
        $this->session->shouldReceive('regenerateToken')->once();

        // Act
        $this->logoutUser->handle();

        // Assert
        $this->session->shouldHaveReceived('invalidate')->once();
    }

    public function testItRegeneratesToken(): void
    {
        // Arrange
        $this->guard->shouldReceive('logout')->once();
        $this->session->shouldReceive('invalidate')->once();

        // Act
        $this->logoutUser->handle();

        // Assert
        $this->session->shouldHaveReceived('regenerateToken')->once();
    }
}
