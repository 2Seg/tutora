<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Requests\ListStudentsRequest;
use App\Repositories\Student\StudentRepository;
use DateTimeZone;
use Inertia\Response;
use Inertia\ResponseFactory;

final readonly class ListStudentsController
{
    public function __construct(
        private ResponseFactory $inertia,
        private StudentRepository $studentRepository,
    ) {}

    public function __invoke(ListStudentsRequest $request): Response
    {
        return $this->inertia->render('teacher/students/Index', [
            'students' => $this->studentRepository
                ->getAllFromUser($request->user())
                ->toResourceCollection()
                ->resolve(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'selectedStudentId' => $request->integer('student') ?: null,
        ]);
    }
}
