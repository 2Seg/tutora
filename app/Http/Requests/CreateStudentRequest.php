<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\Student\CreateStudentData;
use Illuminate\Foundation\Http\FormRequest;

final class CreateStudentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
        ];
    }

    public function toStudentData(): CreateStudentData
    {
        $attributes = $this->validated();

        return new CreateStudentData(
            $attributes['email'],
            $attributes['first_name'],
            $attributes['last_name'],
            $attributes['phone'],
            $attributes['timezone'],
        );
    }
}
