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
    public function testItLeavesOtherExceptionsUntouched(): void
    {
        // Arrange
        $exception = new Exception();

        // Act
        $actual = new ExceptionHandler()($exception);

        // Assert
        $this->assertInstanceOf(Exception::class, $actual);
    }

    public function testItConvertsInvalidCredentialsExceptionIntoValidationException(): void
    {
        // Arrange
        $expected = ['global' => ['Invalid credentials.']];
        $invalidCredentials = new InvalidCredentials();

        // Act
        $actual = new ExceptionHandler()($invalidCredentials);

        // Assert
        $this->assertInstanceOf(ValidationException::class, $actual);
        $this->assertSame($expected, $actual->errors());
    }
}
