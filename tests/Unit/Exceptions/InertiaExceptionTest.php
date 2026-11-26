<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InertiaException;
use Exception;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;
use Inertia\ResponseFactory;
use Mockery;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\Response;
use Tests\Unit\UnitTestCase;

final class InertiaExceptionTest extends UnitTestCase
{
    private const int STATUS_CODE = Response::HTTP_I_AM_A_TEAPOT;

    private readonly ResponseFactory|Mock $inertia;

    protected function setUp(): void
    {
        $this->inertia = Mockery::mock(ResponseFactory::class);
    }

    public function testRenderReturnsAnInertiaResponseWithStatusCode(): void
    {
        // Arrange
        $inertiaException = $this->makeInertiaException(shouldBeLogged: true);
        $inertiaResponse = Mockery::mock(InertiaResponse::class);
        $httpResponse = Mockery::spy(Response::class);
        $httpResponse->shouldReceive('setStatusCode')->andReturnSelf();
        $request = new Request();
        $this->inertia->shouldReceive('render')
            ->once()
            ->with('Error', ['statusCode' => self::STATUS_CODE])
            ->andReturn($inertiaResponse);
        $inertiaResponse->shouldReceive('toResponse')
            ->once()
            ->with($request)
            ->andReturn($httpResponse);

        // Act
        $actual = $inertiaException->render($request);

        // Assert
        $this->assertSame($httpResponse, $actual);
        $httpResponse->shouldHaveReceived('setStatusCode')
            ->once()
            ->with(self::STATUS_CODE);
    }

    public function testReportFallsBackToDefaultLoggingWhenItShouldBeLogged(): void
    {
        // Arrange
        $inertiaException = $this->makeInertiaException(shouldBeLogged: true);

        // Act & Assert
        $this->assertFalse($inertiaException->report());
    }

    public function testReportSkipsLoggingWhenItShouldNotBeLogged(): void
    {
        // Arrange
        $inertiaException = $this->makeInertiaException(shouldBeLogged: false);

        // Act & Assert
        $this->assertTrue($inertiaException->report());
    }

    private function makeInertiaException(bool $shouldBeLogged): InertiaException
    {
        return new class ($this->inertia, self::STATUS_CODE, new Exception(), $shouldBeLogged) extends InertiaException
        {
            public function __construct(
                ResponseFactory $inertia,
                int $statusCode,
                Exception $original,
                private readonly bool $isLoggable,
            ) {
                parent::__construct($inertia, $statusCode, $original);
            }

            protected function shouldBeLogged(): bool
            {
                return $this->isLoggable;
            }
        };
    }
}
