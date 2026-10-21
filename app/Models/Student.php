<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasFullName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Student extends Model
{
    use HasFactory, HasFullName;
}
