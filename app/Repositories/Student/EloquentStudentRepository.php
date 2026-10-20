<?php

declare(strict_types=1);

namespace App\Repositories\Student;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class EloquentStudentRepository implements StudentRepository
{
    public function getStudentsFromUser(User $user): Collection
    {
        return $user->students()->get();
    }
}
