<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\LoginRequest;
use Tests\Concerns\HasRequestValidator;
use Tests\Unit\UnitTestCase;

final class LoginRequestTest extends UnitTestCase
{
    use HasRequestValidator;

    private LoginRequest $loginRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginRequest = new LoginRequest();
    }

    public function testItFailsIfEmailIsMissing(): void
    {
        // Arrange
        $data = [
            'password' => 'secret',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['email' => ['Required' => []]], $validator->failed());
    }

    public function testItFailsIfEmailIsNotValid(): void
    {
        // Arrange
        $data = [
            'email' => 'not-valid-email',
            'password' => 'secret',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['email' => ['Email' => []]], $validator->failed());
    }

    public function testItFailsIfEmailIsNotAString(): void
    {
        // Arrange
        $data = [
            'email' => 123,
            'password' => 'secret',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['email' => ['String' => [], 'Email' => []]], $validator->failed());
    }

    public function testItFailsIfPasswordIsMissing(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['password' => ['Required' => []]], $validator->failed());
    }

    public function testItFailsIfPasswordIsNotAString(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 123,
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['password' => ['String' => []]], $validator->failed());
    }

    public function testItFailsIfRememberIsNotABoolean(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
            'remember' => 123,
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertSame(['remember' => ['Boolean' => []]], $validator->failed());
    }

    public function testItPassesIfFieldsAreValid(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }

    public function testItPassesIfAllFieldsPresentAreValid(): void
    {
        // Arrange
        $data = [
            'email' => 'test@test.com',
            'password' => 'secret',
            'remember' => '1',
        ];

        // Act
        $validator = $this->makeRequestValidator($this->loginRequest, $data);

        // Assert
        $this->assertTrue($validator->passes());
    }
}
