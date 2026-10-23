<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\InvalidCredentials;
use PHPUnit\Framework\TestCase;

final class InvalidCredentialsTest extends TestCase
{
    public function testItHasTheRightTranslationKey(): void
    {
        // Act
        $actual = (new InvalidCredentials())->translationKey;

        // Assert
        $this->assertSame('auth.failed', $actual);
    }
}
