<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Availability extends Model
{
    use HasTimestamps, HasFactory;

    protected $fillable = ['day_of_week', 'start_time', 'end_time'];
}
