<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUpTraits(): array
    {
        $database = $this->app->make(ConnectionInterface::class)->getDatabaseName();

        if (! str_starts_with($database, 'testing')) {
            $this->fail("Refusing to run tests against non-test database [{$database}].");
        }

        return parent::setUpTraits();
    }
}
