<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Http\Requests\CreateStudentRequest;
use App\Repositories\Student\StudentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

final readonly class CreateStudentController
{
    public function __construct(
        private Redirector $redirector,
        private StudentRepository $studentRepository,
    ) {}

    public function __invoke(CreateStudentRequest $createStudentRequest): RedirectResponse
    {
        $this->studentRepository->storeForUser($createStudentRequest->user(), $createStudentRequest->toStudentData());

        return $this->redirector->route('teacher.students.index');
    }
}
