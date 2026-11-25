<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\CreateStudentRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\PresenceVerifierInterface;
use Mockery;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class CreateStudentRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private readonly CreateStudentRequest $createStudentRequest;

    protected function setUp(): void
    {
        $this->createStudentRequest = new CreateStudentRequest();
    }

    public function testItFailsIfEmailIsMissing(): void
    {
        // Act
        $validator = $this->makeValidator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['email']);
    }

    public function testItFailsIfEmailIsNotAString(): void
    {
        // Arrange
        $data = ['email' => 123];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['email']);
    }

    public function testItFailsIfEmailIsMoreThan255Characters(): void
    {
        // Arrange
        $data = ['email' => str_repeat('a', 247) . '@test.com'];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['email']);
    }

    public function testItFailsIfEmailIsInvalid(): void
    {
        // Arrange
        $data = ['email' => 'not-an-email'];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Email', $validator->failed()['email']);
    }

    public function testItFailsIfEmailIsAlreadyTaken(): void
    {
        // Arrange
        $data = ['email' => 'john@doe.com'];

        // Act
        $validator = $this->makeValidator($data, 1);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Unique', $validator->failed()['email']);
    }

    public function testItFailsIfFirstNameIsMissing(): void
    {
        // Act
        $validator = $this->makeValidator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['first_name']);
    }

    public function testItFailsIfFirstNameIsNotAString(): void
    {
        // Arrange
        $data = ['first_name' => 123];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['first_name']);
    }

    public function testItFailsIfFirstNameIsMoreThan255Characters(): void
    {
        // Arrange
        $data = ['first_name' => str_repeat('a', 256)];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['first_name']);
    }

    public function testItFailsIfLastNameIsMissing(): void
    {
        // Act
        $validator = $this->makeValidator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['last_name']);
    }

    public function testItFailsIfLastNameIsNotAString(): void
    {
        // Arrange
        $data = ['last_name' => 123];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['last_name']);
    }

    public function testItFailsIfLastNameIsMoreThan255Characters(): void
    {
        // Arrange
        $data = ['last_name' => str_repeat('a', 256)];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['last_name']);
    }

    public function testItFailsIfPhoneIsMissing(): void
    {
        // Act
        $validator = $this->makeValidator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['phone']);
    }

    public function testItFailsIfPhoneIsNotAString(): void
    {
        // Arrange
        $data = ['phone' => 123];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['phone']);
    }

    public function testItFailsIfPhoneIsMoreThan255Characters(): void
    {
        // Arrange
        $data = ['phone' => str_repeat('a', 256)];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Max', $validator->failed()['phone']);
    }

    public function testItFailsIfTimezoneIsMissing(): void
    {
        // Act
        $validator = $this->makeValidator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Required', $validator->failed()['timezone']);
    }

    public function testItFailsIfTimezoneIsNotAString(): void
    {
        // Arrange
        $data = ['timezone' => 123];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('String', $validator->failed()['timezone']);
    }

    public function testItFailsIfTimezoneIsInvalid(): void
    {
        // Arrange
        $data = ['timezone' => 'Not/A_Timezone'];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('Timezone', $validator->failed()['timezone']);
    }

    public function testItPassesIfDataIsValid(): void
    {
        // Arrange
        $data = [
            'email' => 'john@doe.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+33612345678',
            'timezone' => 'Europe/Paris',
        ];

        // Act
        $validator = $this->makeValidator($data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItBuildsStudentData(): void
    {
        // Arrange
        $this->createStudentRequest->setValidator($this->makeValidator([
            'email' => 'john@doe.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+33612345678',
            'timezone' => 'Europe/Paris',
        ]));

        // Act
        $actual = $this->createStudentRequest->toStudentData();

        // Assert
        $this->assertSame('john@doe.com', $actual->email);
        $this->assertSame('John', $actual->firstName);
        $this->assertSame('Doe', $actual->lastName);
        $this->assertSame('+33612345678', $actual->phone);
        $this->assertSame('Europe/Paris', $actual->timezone);
    }

    private function makeValidator(array $data, int $existingEmailCount = 0): Validator
    {
        $presenceVerifier = Mockery::mock(PresenceVerifierInterface::class);
        $presenceVerifier->shouldReceive('getCount')->andReturn($existingEmailCount);

        $validator = $this->makeRequestValidator($this->createStudentRequest, $data);
        $validator->setPresenceVerifier($presenceVerifier);

        return $validator;
    }
}
