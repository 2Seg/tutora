<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasInitials
{
    protected function initials(): Attribute
    {
        return Attribute::get(
            fn() => mb_strtoupper(mb_substr($this->first_name, 0, 1) . mb_substr($this->last_name, 0, 1))
        );
    }
}
