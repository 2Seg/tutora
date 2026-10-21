<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasFullName
{
    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn() => trim("{$this->first_name} {$this->last_name}")
        );
    }
}
