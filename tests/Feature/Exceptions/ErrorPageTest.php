<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\FeatureTestCase;

final class ErrorPageTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
    }

    public function testUnknownUrlRendersErrorPageWith404(): void
    {
        // Act
        $actual = $this->get('/does-not-exist');

        // Assert
        $actual->assertNotFound();
        $this->assertSame('Error', $actual->inertiaPage()['component']);
        $this->assertSame(Response::HTTP_NOT_FOUND, $actual->inertiaProps('statusCode'));
    }

    public function testUnhandledExceptionRendersErrorPageWith500AndIsLogged(): void
    {
        // Arrange
        Log::spy();
        Route::middleware('web')->get('/failing-route', fn() => throw new RuntimeException('Unexpected failure'));

        // Act
        $actual = $this->get('/failing-route');

        // Assert
        $actual->assertInternalServerError();
        $this->assertSame('Error', $actual->inertiaPage()['component']);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $actual->inertiaProps('statusCode'));
        Log::shouldHaveReceived('error')->once();
    }

    public function testNotFoundIsNotLogged(): void
    {
        // Arrange
        Log::spy();

        // Act
        $this->get('/does-not-exist');

        // Assert
        Log::shouldNotHaveReceived('error');
    }

    public function testGuestIsStillRedirectedToLogin(): void
    {
        // Act
        $actual = $this->get('/teacher/calendar');

        // Assert
        $actual->assertRedirect('/login');
    }
}
