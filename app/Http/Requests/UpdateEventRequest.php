<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\Event\UpdateEventData;
use App\Enums\EventType;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventRequest extends FormRequest
{
    public function rules(): array
    {
        return match ($this->getEvent()->type) {
            EventType::TimeOff => [
                'starts_at' => ['required', 'date'],
                'ends_at' => ['required', 'date', 'after:starts_at'],
            ],
            EventType::Lesson => [
                'title' => ['required', 'string', 'max:50'],
            ],
        };
    }

    public function toEventData(): UpdateEventData
    {
        $attributes = $this->validated();

        return match ($this->getEvent()->type) {
            EventType::TimeOff => UpdateEventData::forTimeOff(
                CarbonImmutable::parse($attributes['starts_at']),
                CarbonImmutable::parse($attributes['ends_at']),
            ),
            EventType::Lesson => UpdateEventData::forLesson($attributes['title']),
        };
    }

    private function getEvent(): Event
    {
        $event = $this->route('event');

        assert($event instanceof Event);

        return $event;
    }
}
