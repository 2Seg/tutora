<?php

declare(strict_types=1);

namespace App\Data\Student;

final readonly class CreateStudentData
{
    public function __construct(
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $timezone,
    ) {}
}
