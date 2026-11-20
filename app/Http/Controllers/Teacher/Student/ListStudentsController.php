<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Repositories\Student\StudentRepository;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class ListStudentsController
{
    public function __construct(
        private ResponseFactory $inertia,
        private StudentRepository $studentRepository,
    ) {}

    public function __invoke(Request $request): Response
    {
        return $this->inertia->render('teacher/students/Index', [
            'students' => $this->studentRepository
                ->getAllFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
        ]);
    }
}
