<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\Event\CreateEventData;
use App\Data\Event\CreateLessonData;
use App\Enums\EventType;
use App\Enums\LessonStatus;
use Carbon\CarbonImmutable;
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

    public function toEventData(): CreateEventData
    {
        $attributes = $this->validated();

        return new CreateEventData(
            EventType::from($attributes['type']),
            CarbonImmutable::parse($attributes['starts_at']),
            CarbonImmutable::parse($attributes['ends_at']),
            $attributes['type'] === EventType::Lesson->value
                ? new CreateLessonData((int) $attributes['student_id'], LessonStatus::from($attributes['status']), $attributes['title'])
                : null,
        );
    }
}
