<?php

declare(strict_types=1);

namespace App\Repositories\Student;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class EloquentStudentRepository implements StudentRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->students()->get();
    }

    public function belongsToUser(int $studentId, User $user): bool
    {
        return $user->students()->whereKey($studentId)->exists();
    }
}
