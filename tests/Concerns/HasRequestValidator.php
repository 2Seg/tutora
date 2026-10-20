<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Contracts\Validation\Validator;

trait HasRequestValidator
{
    protected function makeRequestValidator(FormRequest $formRequest, array $data): Validator
    {
        return $this->app->make(ValidatorFactory::class)->make(
            $data,
            $formRequest->rules(),
            $formRequest->messages(),
            $formRequest->attributes()
        );
    }
}
