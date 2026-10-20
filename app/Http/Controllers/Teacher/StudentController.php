<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Repositories\Student\StudentRepository;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class StudentController
{
    public function __construct(
        private ResponseFactory $inertia,
        private StudentRepository $studentRepository,
    ) {}

    public function index(Request $request): Response
    {
        return $this->inertia->render('teacher/students/Index', [
            'students' => $this->studentRepository
                ->getStudentsFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
        ]);
    }
}
