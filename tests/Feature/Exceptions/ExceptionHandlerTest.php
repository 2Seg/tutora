<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidCredentials;
use Exception;
use Illuminate\Validation\ValidationException;
use Tests\Feature\FeatureTestCase;

final class ExceptionHandlerTest extends FeatureTestCase
{
    private ExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->app->make(ExceptionHandler::class);
    }

    public function testItLeavesOtherExceptionsUntouched(): void
    {
        // Arrange
        $exception = new Exception();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertSame($exception, $actual);
    }

    public function testItConvertsInvalidCredentialsExceptionIntoValidationException(): void
    {
        // Arrange
        $expected = ['failed' => ['These credentials do not match our records.']];

        // Act
        $actual = ($this->handler)(new InvalidCredentials());

        // Assert
        $this->assertInstanceOf(ValidationException::class, $actual);
        $this->assertSame($expected, $actual->errors());
    }
}
