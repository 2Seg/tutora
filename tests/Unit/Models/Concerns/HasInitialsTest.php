<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Models\Concerns\HasInitials;
use Illuminate\Database\Eloquent\Model;
use Tests\Unit\UnitTestCase;

final class HasInitialsTest extends UnitTestCase
{
    private readonly Model $model;

    protected function setUp(): void
    {
        $this->model = new class extends Model {
            use HasInitials;

            protected $guarded = [];
        };
    }

    public function testItCombinesFirstNameAndLastNameLetters(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Act
        $actual = $this->model->initials;

        // Assert
        $this->assertSame('JD', $actual);
    }

    public function testItHandlesMissingFirstName(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => '',
            'last_name' => 'Doe',
        ]);

        // Act
        $actual = $this->model->initials;

        // Assert
        $this->assertSame('D', $actual);
    }

    public function testItHandlesMissingLastName(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => 'John',
            'last_name' => '',
        ]);

        // Act
        $actual = $this->model->initials;

        // Assert
        $this->assertSame('J', $actual);
    }
}
