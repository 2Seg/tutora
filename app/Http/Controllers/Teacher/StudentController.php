<?php

namespace App\Http\Controllers\Teacher;

use App\Models\User;
use App\Repositories\Student\StudentRepository;
use Inertia\Response;
use Inertia\ResponseFactory;

final class StudentController
{
    public function __construct(
        private ResponseFactory $inertia,
        private StudentRepository $studentRepository
    ) {}

    public function index(): Response
    {
        $user = User::firstOrFail(); // TODO: Remove when auth is setup

        return $this->inertia->render('teacher/students/Index', [
            'students' => $this->studentRepository
                ->getStudentsFromUser($user)
                ->toResourceCollection()
                ->resolve(),
        ]);
    }
}
