<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Exceptions\ExceptionHandler;
use App\Exceptions\InertiaHttpException;
use App\Exceptions\InertiaNotFoundException;
use App\Exceptions\InvalidCredentials;
use Closure;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\FeatureTestCase;
use Throwable;

final class ExceptionHandlerTest extends FeatureTestCase
{
    private readonly ExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);

        $this->handler = $this->app->make(ExceptionHandler::class);
    }

    public static function notFoundExceptions(): iterable
    {
        yield 'not found http' => [fn(): Throwable => new NotFoundHttpException()];
        yield 'model not found' => [fn(): Throwable => new ModelNotFoundException()];
    }

    public static function exceptionsHandledByLaravel(): iterable
    {
        yield 'validation' => [fn(): Throwable => ValidationException::withMessages(['email' => 'invalid'])];
        yield 'authentication' => [fn(): Throwable => new AuthenticationException()];
        yield 'http response' => [fn(): Throwable => new HttpResponseException(new Response())];
        yield 'token mismatch' => [fn(): Throwable => new TokenMismatchException()];
    }

    public static function httpStatusCodes(): iterable
    {
        yield 'forbidden' => [Response::HTTP_FORBIDDEN];
        yield 'method not allowed' => [Response::HTTP_METHOD_NOT_ALLOWED];
        yield 'too many requests' => [Response::HTTP_TOO_MANY_REQUESTS];
        yield 'internal server error' => [Response::HTTP_INTERNAL_SERVER_ERROR];
        yield 'service unavailable' => [Response::HTTP_SERVICE_UNAVAILABLE];
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

    public function testItStillConvertsInvalidCredentialsWhenDebug(): void
    {
        // Arrange
        config(['app.debug' => true]);

        // Act
        $actual = ($this->handler)(new InvalidCredentials());

        // Assert
        $this->assertInstanceOf(ValidationException::class, $actual);
    }

    public function testItLeavesOtherExceptionsUntouchedIfDebug(): void
    {
        // Arrange
        config(['app.debug' => true]);
        $exception = new NotFoundHttpException();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertSame($exception, $actual);
    }

    #[DataProvider('notFoundExceptions')]
    public function testItConvertsNotFoundExceptionsToInertiaNotFoundExceptions(Closure $makeException): void
    {
        // Arrange
        $exception = $makeException();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertInstanceOf(InertiaNotFoundException::class, $actual);
        $this->assertSame($exception, $actual->getPrevious());
        $this->assertSame(Response::HTTP_NOT_FOUND, $actual->statusCode);
    }

    #[DataProvider('exceptionsHandledByLaravel')]
    public function testItLeavesExceptionsHandledByLaravelUntouched(Closure $makeException): void
    {
        // Arrange
        $exception = $makeException();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertSame($exception, $actual);
    }

    #[DataProvider('httpStatusCodes')]
    public function testItConvertsHttpExceptionsKeepingTheirStatusCode(int $statusCode): void
    {
        // Arrange
        $exception = new HttpException($statusCode);

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertInstanceOf(InertiaHttpException::class, $actual);
        $this->assertSame($exception, $actual->getPrevious());
        $this->assertSame($statusCode, $actual->statusCode);
    }

    public function testItConvertsAuthorizationExceptionsToForbiddenInertiaHttpExceptions(): void
    {
        // Arrange
        $exception = new AuthorizationException();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertInstanceOf(InertiaHttpException::class, $actual);
        $this->assertSame($exception, $actual->getPrevious());
        $this->assertSame(Response::HTTP_FORBIDDEN, $actual->statusCode);
    }

    public function testItConvertsOtherExceptionsToInternalServerErrorInertiaHttpExceptions(): void
    {
        // Arrange
        $exception = new Exception();

        // Act
        $actual = ($this->handler)($exception);

        // Assert
        $this->assertInstanceOf(InertiaHttpException::class, $actual);
        $this->assertSame($exception, $actual->getPrevious());
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $actual->statusCode);
    }
}
