<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Models\Concerns\HasFullName;
use Illuminate\Database\Eloquent\Model;
use Tests\Unit\UnitTestCase;

final class HasFullNameTest extends UnitTestCase
{
    private readonly Model $model;

    protected function setUp(): void
    {
        $this->model = new class extends Model {
            use HasFullName;

            protected $guarded = [];
        };
    }

    public function testItCombinesFirstNameAndLastName(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Act
        $actual = $this->model->full_name;

        // Assert
        $this->assertSame('John Doe', $actual);
    }

    public function testItHandlesMissingFirstName(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => '',
            'last_name' => 'Doe',
        ]);

        // Act
        $actual = $this->model->full_name;

        // Assert
        $this->assertSame('Doe', $actual);
    }

    public function testItHandlesMissingLastName(): void
    {
        // Arrange
        $this->model->fill([
            'first_name' => 'John',
            'last_name' => '',
        ]);

        // Act
        $actual = $this->model->full_name;

        // Assert
        $this->assertSame('John', $actual);
    }
}
