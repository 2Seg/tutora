<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\Feature\FeatureTestCase;

final class HandleInertiaRequestsTest extends FeatureTestCase
{
    private readonly Request $request;
    private readonly HandleInertiaRequests $handleInertiaRequests;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new Request();
        $this->handleInertiaRequests = new HandleInertiaRequests();
    }

    public function testItSharesUserDataIfAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->makeOne([
            'id' => 1,
            'first_name' => 'Average',
            'last_name' => 'Joe',
        ]);
        $this->request->setUserResolver(fn() => $user);

        // Act
        $actual = $this->handleInertiaRequests->share($this->request);

        // Assert
        $excepted = [
            'id' => 1,
            'fullName' => 'Average Joe',
            'initials' => 'AJ',
        ];
        $this->assertSame($excepted, $actual['auth']['user']());
    }

    public function testItDoesNotShareAnyDataIfGuest(): void
    {
        // Act
        $actual = $this->handleInertiaRequests->share($this->request);

        // Assert
        $this->assertNull($actual['auth']['user']());
    }
}
