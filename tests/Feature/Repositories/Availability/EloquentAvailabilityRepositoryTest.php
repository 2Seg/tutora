<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Availability;

use App\Data\AvailabilitiesData;
use App\Data\AvailabilityData;
use App\Models\Availability;
use App\Models\User;
use App\Repositories\Availability\EloquentAvailabilityRepository;
use RuntimeException;
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

    public function testItDeletesAllAvailabilitiesWhenAvailabilitiesDataIsEmpty(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->availabilities()->saveMany(Availability::factory()->count(2)->make());

        // Act
        $this->availabilityRepository->updateForUser($user, new AvailabilitiesData());

        // Assert
        $this->assertCount(0, $user->refresh()->availabilities);
    }

    public function testItReplacesOnlyUserAvailabilities(): void
    {
        // Arrange
        $user = User::factory()->create();
        $oldAvailability = Availability::factory()->create(['user_id' => $user->id]);
        $otherUserAvailability = Availability::factory()->create();
        $availabilitiesData = new AvailabilitiesData(
            new AvailabilityData(1, '09:00', '09:50'),
            new AvailabilityData(2, '09:00', '09:50'),
        );

        // Act
        $this->availabilityRepository->updateForUser($user, $availabilitiesData);

        // Assert
        $this->assertModelMissing($oldAvailability);
        $this->assertModelExists($otherUserAvailability);
        $this->assertSame(
            [
                ['day_of_week' => 1, 'start_time' => '09:00:00', 'end_time' => '09:50:00'],
                ['day_of_week' => 2, 'start_time' => '09:00:00', 'end_time' => '09:50:00'],
            ],
            $user->availabilities()
                ->orderBy('day_of_week')
                ->get(['day_of_week', 'start_time', 'end_time'])
                ->toArray(),
        );
    }

    public function testItKeepsTheOldAvailabilitiesWhenCreatingOneFails(): void
    {
        // Arrange
        $user = User::factory()->create();
        $oldAvailability = Availability::factory()->create(['user_id' => $user->id]);
        $availabilitiesData = new AvailabilitiesData(
            new AvailabilityData(1, '09:00', '09:50'),
            new AvailabilityData(2, '09:00', '09:50'),
        );
        $creationAttempts = 0;
        Availability::creating(function () use (&$creationAttempts) {
            if (++$creationAttempts === 2) {
                throw new RuntimeException('Second availability could not be created');
            }
        });

        // Act
        $this->assertThrows(
            fn() => $this->availabilityRepository->updateForUser($user, $availabilitiesData),
            RuntimeException::class,
        );

        // Assert
        $this->assertModelExists($oldAvailability);
        $this->assertCount(1, $user->refresh()->availabilities);
    }
}
