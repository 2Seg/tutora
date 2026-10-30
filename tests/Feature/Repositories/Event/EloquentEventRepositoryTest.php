<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Event;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Event\EloquentEventRepository;
use Tests\Feature\FeatureTestCase;

final class EloquentEventRepositoryTest extends FeatureTestCase
{
    private readonly EloquentEventRepository $eventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = new EloquentEventRepository();
    }

    function testItRetrievesEventsFromUser()
    {
        // Arrange
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        // Act
        $actual = $this->eventRepository->getAllFromUser($user);

        // Assert
        $this->assertTrue($actual->contains($event));
    }

    function testItRetrievesEventsFromAnotherUser()
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user2->id]);

        // Act
        $actual = $this->eventRepository->getAllFromUser($user);

        // Assert
        $this->assertFalse($actual->contains($event));
    }
}
