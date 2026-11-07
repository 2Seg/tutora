<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAvailabilitiesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'availabilities' => ['present', 'array'],
            'availabilities.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i', 'after:availabilities.*.start_time'],
        ];
    }
}
