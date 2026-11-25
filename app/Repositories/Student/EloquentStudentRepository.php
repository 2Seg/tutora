<?php

declare(strict_types=1);

namespace App\Repositories\Student;

use App\Data\Student\CreateStudentData;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class EloquentStudentRepository implements StudentRepository
{
    public function getAllFromUser(User $user): Collection
    {
        return $user->students()->with('nextLesson')->latest('updated_at')->get();
    }

    public function belongsToUser(int $studentId, User $user): bool
    {
        return $user->students()->whereKey($studentId)->exists();
    }

    public function storeForUser(User $user, CreateStudentData $createStudentData): void
    {
        $user->students()->create([
            'email' => $createStudentData->email,
            'first_name' => $createStudentData->firstName,
            'last_name' => $createStudentData->lastName,
            'phone' => $createStudentData->phone,
            'timezone' => $createStudentData->timezone,
        ]);
    }
}
