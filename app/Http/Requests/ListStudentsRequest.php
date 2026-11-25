<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListStudentsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student' => ['nullable', 'integer'],
        ];
    }
}
