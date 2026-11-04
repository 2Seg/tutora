<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Availability;

use App\Models\Availability;
use App\Models\User;
use App\Repositories\Availability\EloquentAvailabilityRepository;
use Tests\Feature\FeatureTestCase;

final class EloquentAvailabilityRepositoryTest extends FeatureTestCase
{
    private readonly EloquentAvailabilityRepository $availabilityRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityRepository = new EloquentAvailabilityRepository();
    }

    public function testItDoesNotRetrieveAvailabilitiesFromAnotherUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $availability = Availability::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->availabilityRepository->getAllFromUser($user);

        // Assert
        $this->assertCount(0, $actual);
        $this->assertFalse($actual->contains($availability));
    }

    public function testItRetrievesEventsFromUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $availability = Availability::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->availabilityRepository->getAllFromUser($user);

        // Assert
        $this->assertCount(1, $actual);
        $this->assertTrue($actual->contains($availability));
    }
}
