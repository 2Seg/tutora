<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\LessonStatus;
use Tests\Unit\UnitTestCase;

final class LessonStatusTest extends UnitTestCase
{
    public function testCancelledIsNotCancellable(): void
    {
        // Act & Assert
        $this->assertFalse(LessonStatus::Cancelled->isCancellable());
    }

    public function testRequestedIsCancellable(): void
    {
        // Act & Assert
        $this->assertTrue(LessonStatus::Requested->isCancellable());
    }

    public function testPlannedIsCancellable(): void
    {
        // Act & Assert
        $this->assertTrue(LessonStatus::Planned->isCancellable());
    }

    public function testCancelledIsNotPlannable(): void
    {
        // Act & Assert
        $this->assertFalse(LessonStatus::Cancelled->isPlannable());
    }

    public function testPlannedIsNotPlannable(): void
    {
        // Act & Assert
        $this->assertFalse(LessonStatus::Planned->isPlannable());
    }

    public function testRequestedIsPlannable(): void
    {
        // Act & Assert
        $this->assertTrue(LessonStatus::Requested->isPlannable());
    }
}
