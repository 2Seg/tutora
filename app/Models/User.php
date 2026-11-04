<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasFullName;
use App\Models\Concerns\HasInitials;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasFullName, HasInitials, HasTimestamps;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
