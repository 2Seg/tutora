<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasFullName;
use App\Models\Concerns\HasInitials;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Student extends Model
{
    use HasFactory, HasFullName, HasInitials, HasTimestamps;

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
