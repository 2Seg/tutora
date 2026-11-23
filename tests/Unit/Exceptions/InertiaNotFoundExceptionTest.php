<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InertiaNotFoundException;
use Exception;
use Inertia\ResponseFactory;
use Mockery;
use Tests\Unit\UnitTestCase;

final class InertiaNotFoundExceptionTest extends UnitTestCase
{
    private readonly Exception $original;
    private readonly InertiaNotFoundException $inertiaNotFoundException;

    protected function setUp(): void
    {
        $this->original = new Exception();
        $this->inertiaNotFoundException = new InertiaNotFoundException(
            Mockery::mock(ResponseFactory::class),
            $this->original,
        );
    }

    public function testItIsNeverLogged(): void
    {
        // Act & Assert
        $this->assertTrue($this->inertiaNotFoundException->report());
    }

    public function testStatusCodeIs404(): void
    {
        // Act & Assert
        $this->assertSame(404, $this->inertiaNotFoundException->statusCode);
    }

    public function testItKeepsTheOriginalExceptionAsPrevious(): void
    {
        // Act & Assert
        $this->assertSame($this->original, $this->inertiaNotFoundException->getPrevious());
    }
}
