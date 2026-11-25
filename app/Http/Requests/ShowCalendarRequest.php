<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ShowCalendarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'week' => ['nullable', 'date_format:Y-m-d'],
            'event' => ['nullable', 'integer'],
        ];
    }
}
