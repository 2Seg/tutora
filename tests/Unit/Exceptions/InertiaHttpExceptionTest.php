<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InertiaHttpException;
use Exception;
use Inertia\ResponseFactory;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\Unit\UnitTestCase;

final class InertiaHttpExceptionTest extends UnitTestCase
{
    private readonly ResponseFactory|Mock $inertia;

    protected function setUp(): void
    {
        $this->inertia = Mockery::mock(ResponseFactory::class);
    }

    public static function statusCodeProvider(): iterable
    {
        yield '403' => [403];
        yield '404' => [404];
        yield '419' => [419];
        yield '422' => [422];
        yield '429' => [429];
        yield '503' => [503];
    }

    public function testItIsLoggedWhenStatusCodeIs500(): void
    {
        // Arrange
        $inertiaHttpException = new InertiaHttpException($this->inertia, 500, new Exception());

        // Act & Assert
        $this->assertFalse($inertiaHttpException->report());
    }

    #[DataProvider('statusCodeProvider')]
    public function testItIsNotLoggedWhenStatusCodeIsNot500(int $statusCode): void
    {
        // Arrange
        $inertiaHttpException = new InertiaHttpException($this->inertia, $statusCode, new Exception());

        // Act & Assert
        $this->assertTrue($inertiaHttpException->report());
    }

    public function testItExposesTheGivenStatusCode(): void
    {
        // Arrange
        $inertiaHttpException = new InertiaHttpException($this->inertia, Response::HTTP_FORBIDDEN, new Exception());

        // Act & Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $inertiaHttpException->statusCode);
    }

    public function testItKeepsTheOriginalExceptionAsPrevious(): void
    {
        // Arrange
        $original = new Exception('original message');

        // Act
        $inertiaHttpException = new InertiaHttpException($this->inertia, Response::HTTP_FORBIDDEN, $original);

        // Assert
        $this->assertSame($original, $inertiaHttpException->getPrevious());
        $this->assertSame('original message', $inertiaHttpException->getMessage());
    }
}
