<?php

declare(strict_types=1);

namespace App\Repositories\Student;

use App\Data\Student\CreateStudentData;
use App\Models\User;
use Illuminate\Support\Collection;

interface StudentRepository
{
    public function getAllFromUser(User $user): Collection;

    public function belongsToUser(int $studentId, User $user): bool;

    public function storeForUser(User $user, CreateStudentData $createStudentData): void;
}
