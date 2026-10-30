<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\EventType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'startTime' => $this->start_time?->toIso8601String(),
            'endTime' => $this->end_time?->toIso8601String(),
            'type' => $this->type,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),

            $this->mergeWhen($this->type === EventType::Lesson && $this->lesson, fn() => [
                'status' => $this->lesson->status,
                'title' => $this->lesson->title,
                'meetingUrl' => $this->lesson->meeting_url,
                'student' => (new StudentResource($this->lesson->student))->resolve($request),
            ]),
        ];
    }
}
