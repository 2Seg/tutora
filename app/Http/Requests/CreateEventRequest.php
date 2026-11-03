<?php

namespace App\Http\Requests;

use App\Enums\EventType;
use App\Enums\LessonStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateEventRequest extends FormRequest
{
    public function rules(): array
    {
        $onlyWhenEventIsLesson = 'exclude_unless:type,' . EventType::Lesson->value;

        return [
            'type' => ['required', 'string', Rule::enum(EventType::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],

            'status' => [$onlyWhenEventIsLesson, 'required', 'string', Rule::enum(LessonStatus::class)],
            'student_id' => [$onlyWhenEventIsLesson, 'required', 'integer'],
            'title' => [$onlyWhenEventIsLesson, 'required', 'string', 'max:50'],
        ];
    }
}
