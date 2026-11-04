<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Auth;

use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\Unit\UnitTestCase;

final class UserResourceTest extends UnitTestCase
{
    private readonly Request $request;
    private readonly User $user;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->user = new User();
    }

    public function testItSerializesUserId(): void
    {
        // Arrange
        $this->user->id = 1;

        // Act
        $actual = (new UserResource($this->user))->resolve($this->request);

        // Assert
        $this->assertSame(1, $actual['id']);
    }

    public function testItSerializesUserFullName(): void
    {
        // Arrange
        $this->user->first_name = 'Average';
        $this->user->last_name = 'Joe';

        // Act
        $actual = (new UserResource($this->user))->resolve($this->request);

        // Assert
        $this->assertSame('Average Joe', $actual['fullName']);
    }

    public function testItSerializesUserInitials(): void
    {
        // Arrange
        $this->user->first_name = 'Average';
        $this->user->last_name = 'Joe';

        // Act
        $actual = (new UserResource($this->user))->resolve($this->request);

        // Assert
        $this->assertSame('AJ', $actual['initials']);
    }

    public function testItSerializesOnlySpecificFields(): void
    {
        // Arrange
        $expected = ['id', 'fullName', 'initials'];

        // Act
        $actual = (new UserResource($this->user))->resolve($this->request);

        // Assert
        $this->assertSame($expected, array_keys($actual));
    }
}
