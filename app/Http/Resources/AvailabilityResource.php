<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dayOfWeek' => $this->day_of_week,
            'startTime' => substr((string) $this->start_time, 0, 5),
            'endTime' => substr((string) $this->end_time, 0, 5),
        ];
    }
}
